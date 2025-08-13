# main.py
from flask import Flask, request, jsonify
from pyrfc import Connection, ABAPApplicationError, ABAPRuntimeError, LogonError, CommunicationError
from decimal import Decimal
from concurrent.futures import ThreadPoolExecutor
import threading
import os
import traceback
from flask_cors import CORS
from datetime import datetime, time

app = Flask(__name__)
CORS(app, supports_credentials=True, resources={r"/api/*": {"origins": "*"}})

def connect_sap(username=None, password=None):
    username = username or os.environ.get('SAP_USERNAME')
    password = password or os.environ.get('SAP_PASSWORD')
    if not username or not password:
        raise Exception("SAP credentials not provided.")
    
    return Connection(
        user=username,
        passwd=password,
        ashost='192.168.254.154',
        sysnr='01',
        client='300',
        lang='EN',
    )

def get_credentials():
    """
    Mengambil kredensial SAP dari header request.
    """
    username = request.headers.get('X-SAP-Username')
    password = request.headers.get('X-SAP-Password')
    
    if not username or not password:
        # Kembalikan 401 Unauthorized jika tidak ada header
        raise ValueError("SAP credentials not found in headers.")
    
    return username, password

def get_data(plant_code=None, workcenters_csv=None, username=None, password=None):
    conn = connect_sap(username, password)
    all_data = []

    if not plant_code or not workcenters_csv:
        # Ambil semua data jika tidak ada plant atau workcenter
        result = conn.call('Z_FM_YPPR074', P_WERKS='', P_ARBPL='')
        all_data = result.get('T_DATA2', [])
    else:
        workcenters = workcenters_csv.split(',')
        for wc in workcenters:
            result = conn.call('Z_FM_YPPR074', P_WERKS=plant_code, P_ARBPL=wc)
            data = result.get('T_DATA2', [])
            all_data.extend(data)
    return all_data

def get_detail(plant_code=None, workcenter=None, username=None, password=None):
    conn = connect_sap(username, password)
    result = conn.call('Z_FM_YPPR074',
        P_WERKS=plant_code if plant_code else '',
        P_ARBPL=workcenter if workcenter else '',
    )
    return result.get('T_DATA1', [])

@app.route('/api/sap-login', methods=['POST'])
def sap_login():
    data = request.json

    try:
        conn = connect_sap(data['username'], data['password'])
        conn.ping()
        print("[DEBUG] Login sukses!")
        return jsonify({'status': 'connected'})
    except Exception as e:
        print("[ERROR] SAP Login failed:", str(e))
        return jsonify({'error': str(e)}), 401


@app.route('/api/sap_data', methods=['GET'])
def sap_data():
    """
    Endpoint untuk mengambil data SAP berdasarkan plant dan workcenter.
    """
    plant = request.args.get('plant')
    workcenter = request.args.get('workcenter')

    if not plant or not workcenter:
        return jsonify({'error': 'Missing plant or workcenter'}), 400

    try:
        username, password = get_credentials()
        data = get_data(plant, workcenter, username, password)
        return jsonify(data), 200
    except ValueError as ve:
        # Khusus untuk error validasi header
        return jsonify({'error': str(ve)}), 401
    except Exception as e:
        # Untuk error internal lainnya
        return jsonify({'error': f'Internal error: {str(e)}'}), 500

@app.route('/api/sap_detail', methods=['GET'])
def sap_detail():
    plant = request.args.get('plant')
    workcenter = request.args.get('workcenter')
    if not plant or not workcenter:
        return jsonify({'error': 'Missing plant or workcenter'}), 400

    try:
        username, password = get_credentials()
        detail = get_detail(plant, workcenter, username, password)
        return jsonify(detail)
    except ValueError as ve:
        return jsonify({'error': str(ve)}), 401
    except Exception as e:
        return jsonify({'error': str(e)}), 500

# CANGE WC
@app.route('/api/save_edit', methods=['POST'])
def changewc():
    try:
        username, password = get_credentials()
        conn = connect_sap(username, password)

        data = request.get_json()
        print("Data diterima:", data)

        if not data:
            return jsonify({'error': 'No JSON payload received'}), 400

        aufnr = data.get('IV_AUFNR')
        commit = data.get('IV_COMMIT', 'X')
        it_operation = data.get('IT_OPERATION', [])

        if not aufnr or not it_operation:
            return jsonify({'error': 'Missing required fields'}), 400

        if isinstance(it_operation, dict):
            it_operation = [it_operation]

        it_operation_filtered = []
        for op in it_operation:
            filtered = {
                'SEQUENCE': op.get('SEQUEN', ' '),
                'OPERATION': op.get('OPER', ''),
                'WORK_CENTER': op.get('WORK_CEN', ''),
                'WORK_CENTER_X': op.get('W', 'X'),
            }
            it_operation_filtered.append(filtered)

        print("Calling RFC CO_SE_PRODORD_CHANGE...")
        result = conn.call(
            'CO_SE_PRODORD_CHANGE',
            IV_ORDER_NUMBER=aufnr,
            IV_COMMIT=commit,
            IT_OPERATION=it_operation_filtered
        )
        import time
        time.sleep(2) 
        return jsonify(result)

    except ValueError as ve:
        return jsonify({'error': str(ve)}), 401
    except Exception as e:
        print("Exception:", str(e))
        return jsonify({'error': str(e)}), 500

@app.route('/api/sap_combined', methods=['GET'])
def sap_combined():
    plant = request.args.get('plant')

    if not plant:
        return jsonify({'error': 'Missing plant parameter'}), 400

    try:
        username, password = get_credentials()
        conn = connect_sap(username, password)

        # Panggil RFC hanya dengan parameter plant
        result = conn.call('Z_FM_YPPR074Z', P_WERKS=plant)

        return jsonify({
            "T_DATA1": result.get('T_DATA1', []),
            "T_DATA2": result.get('T_DATA2', []),
            "T_DATA3": result.get('T_DATA3', []),
            "T_DATA4": result.get('T_DATA4', []),
        })

    except ValueError as ve:
        return jsonify({'error': str(ve)}), 401
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/sap_combined_multi', methods=['POST'])
def sap_combined_multi():
    data = request.get_json()
    plant = data.get('plant')
    aufnrs = data.get('aufnrs', [])

    if not plant or not isinstance(aufnrs, list):
        return jsonify({'error': 'Missing plant or aufnrs[]'}), 400

    try:
        username, password = get_credentials()
        conn = connect_sap(username, password)
        import time
        time.sleep(2)
        all_data1 = []
        all_data4 = []

        for aufnr in aufnrs:
            result = conn.call('Z_FM_YPPR074Z', P_WERKS=plant, P_AUFNR=aufnr)
            all_data1.extend(result.get('T_DATA1', []))
            all_data4.extend(result.get('T_DATA4', []))

        return jsonify({
            'T_DATA1': all_data1,
            'T_DATA4': all_data4,
        })

    except ValueError as ve:
        return jsonify({'error': str(ve)}), 401
    except Exception as e:
        return jsonify({'error': str(e)}), 500


# RELEASE PRO
@app.route('/api/release_order', methods=['POST'])
def release_order():
    try:
        username, password = get_credentials()
        conn = connect_sap(username, password)

        data = request.get_json()
        print("Data diterima untuk release:", data)

        aufnr = data.get('AUFNR')
        if not aufnr:
            return jsonify({'error': 'AUFNR is required'}), 400

        print("Calling RFC BAPI_PRODORD_RELEASE...")

        result = conn.call(
            'BAPI_PRODORD_RELEASE',
            RELEASE_CONTROL='1',
            WORK_PROCESS_GROUP='COWORK_BAPI',
            WORK_PROCESS_MAX=99,
            ORDERS=[{'ORDER_NUMBER': aufnr}]
        )

        return jsonify(result)

    except ValueError as ve:
        return jsonify({'error': str(ve)}), 401
    except Exception as e:
        print("Exception saat release order:", str(e))
        return jsonify({'error': str(e)}), 500

# TECO
@app.route('/api/teco_order', methods=['POST'])
def teco_order():
    try:
        username, password = get_credentials()
        conn = connect_sap(username, password)

        data = request.get_json()
        print("Data diterima untuk teco:", data)

        aufnr = data.get('AUFNR')
        if not aufnr:
            return jsonify({'error': 'AUFNR is required'}), 400

        # Panggil BAPI_PRODORD_COMPLETE_TECH
        print("Calling RFC BAPI_PRODORD_COMPLETE_TECH...")
        result_teco = conn.call(
            'BAPI_PRODORD_COMPLETE_TECH',
            SCOPE_COMPL_TECH='1',
            WORK_PROCESS_GROUP='COWORK_BAPI',
            WORK_PROCESS_MAX=99,
            ORDERS=[{'ORDER_NUMBER': aufnr}]
        )

        # Lakukan COMMIT agar perubahan tersimpan
        print("Calling RFC BAPI_TRANSACTION_COMMIT...")
        result_commit = conn.call(
            'BAPI_TRANSACTION_COMMIT',
            WAIT='X'
        )

        return jsonify({
            'BAPI_PRODORD_COMPLETE_TECH': result_teco,
            'BAPI_TRANSACTION_COMMIT': result_commit
        })

    except ValueError as ve:
        return jsonify({'error': str(ve)}), 401
    except Exception as e:
        print("Exception saat teco:", str(e))
        return jsonify({'error': str(e)}), 500

# CHANGE PV
@app.route('/api/change_prod_version', methods=['POST'])
def change_prod_version():
    import time
    try:
        username, password = get_credentials()
        conn = connect_sap(username, password)

        data = request.get_json()
        aufnr = data.get('AUFNR')
        verid = data.get('PROD_VERSION')

        if not aufnr or not verid:
            return jsonify({'error': 'AUFNR and PROD_VERSION are required'}), 400

        print("AUFNR:", aufnr, "→ target PROD_VERSION:", verid)

        # Ambil PROD_VERSION sebelum diubah
        before_detail = conn.call('BAPI_PRODORD_GET_DETAIL', NUMBER=aufnr)
        before_version = before_detail.get('ORDER_GENERAL_DETAIL', {}).get('PROD_VERSION', 'unknown')

        print("Sebelum ubah: PROD_VERSION =", before_version)

        # Lakukan perubahan
        result_change = conn.call(
            'BAPI_PRODORD_CHANGE',
            NUMBER=aufnr,
            ORDERDATA={'PROD_VERSION': verid},
            ORDERDATAX={'PROD_VERSION': 'X'}
        )

        # Commit perubahan
        print("BAPI_TRANSACTION_COMMIT...")
        conn.call('BAPI_TRANSACTION_COMMIT', WAIT='X')

        # Delay agar commit selesai
        time.sleep(4)

        # Ambil ulang versi setelah perubahan
        after_detail = conn.call('BAPI_PRODORD_GET_DETAIL', NUMBER=aufnr)
        after_version = after_detail.get('ORDER_GENERAL_DETAIL', {}).get('PROD_VERSION', 'unknown')

        # Ambil pesan dari RETURN
        sap_return = result_change.get('RETURN', [])

        return jsonify({
            'before_version': before_version,
            'after_version': after_version,
            'sap_return': sap_return
        })

    except ValueError as ve:
        return jsonify({'error': str(ve)}), 401
    except Exception as e:
        print("❌ Exception:", str(e))
        return jsonify({'error': str(e)}), 500

# CONVERT 
@app.route('/api/create_prod_order', methods=['POST'])
def create_prod_order_from_plord():
    try:
        username, password = get_credentials()
        conn = connect_sap(username, password)

        data = request.get_json()
        plnum = data.get('PLANNED_ORDER')
        order_type = data.get('AUART')

        if not plnum or not order_type:
            return jsonify({'error': 'PLANNED_ORDER and AUART are required'}), 400

        print(f"Calling BAPI_PRODORD_CREATE_FROM_PLORD with PLANNED_ORDER: {plnum} and ORDER_TYPE: {order_type}")

        result = conn.call(
            'BAPI_PRODORD_CREATE_FROM_PLORD',
            PLANNED_ORDER=plnum,
            ORDER_TYPE=order_type
        )

        return_data = result.get('RETURN', {})
        order_number = result.get('PRODUCTION_ORDER', '')

        print("Result from BAPI_PRODORD_CREATE_FROM_PLORD:", result)

        # Commit jika tidak error
        if return_data.get('TYPE') != 'E':
            conn.call('BAPI_TRANSACTION_COMMIT', WAIT='X')

        return jsonify({
            'success': return_data.get('TYPE') != 'E',
            'order_number': order_number,
            'return': return_data
        })

    except ValueError as ve:
        return jsonify({'error': str(ve)}), 401
    except Exception as e:
        print("Exception:", str(e))
        return jsonify({'error': str(e)}), 500


@app.route('/api/sap-po', methods=['POST'])
def fetch_purchase_orders():
    try:
        username, password = get_credentials()
        plants = request.json.get('plants', [])

        all_data1 = []
        all_data2 = []
        lock = threading.Lock()

        def fetch_from_sap(plant):
            try:
                local_conn = connect_sap(username, password)
                print(f"Fetching from plant: {plant}")
                result = local_conn.call('Z_FM_YMMR068', P_WERKS=plant)

                with lock:
                    if 'T_DATA1' in result:
                        all_data1.extend(result['T_DATA1'])
                    if 'T_DATA2' in result:
                        # PASTIKAN TIDAK ADA MANIPULASI TEXT DI SINI - BIARKAN ORIGINAL
                        for row in result['T_DATA2']:
                            # JANGAN TAMBAHKAN APAPUN KE TEXT FIELD
                            # Biarkan kosong jika memang kosong dari SAP
                            pass
                        all_data2.extend(result['T_DATA2'])
            except Exception as e:
                print(f"[ERROR] Plant {plant}: {str(e)}")

        with ThreadPoolExecutor(max_workers=min(5, len(plants))) as executor:
            executor.map(fetch_from_sap, plants)

        return jsonify({
            'T_DATA1': all_data1,
            'T_DATA2': all_data2,
        })

    except Exception as e:
        print("Exception:", str(e))
        return jsonify({'error': str(e)}), 500

@app.route('/api/reject_po', methods=['POST'])
def reject_po():
    try:
        username, password = get_credentials()
        data = request.json or {}
        ebeln = data.get('EBELN')

        if not ebeln:
            return jsonify({'error': 'Parameter EBELN wajib diisi'}), 400

        print("EBELN diterima:", ebeln)  # Debug

        conn = connect_sap(username, password)
        result = conn.call('Z_PO_REJECT', I_EBELN=ebeln)

        return jsonify({'status': 'success', 'result': result}), 200

    except Exception as e:
        print("[ERROR] Reject PO:", str(e))
        return jsonify({'status': 'error', 'message': str(e)}), 500

@app.route('/api/z_po_comment_update', methods=['POST'])
def comment_update():
    try:
        username, password = get_credentials()
        data = request.json or {}

        ebeln = data.get('PURCHASEORDER')
        comment = data.get('COMMENT_TEXT')

        print(f"[DEBUG] Received Comment Update: EBELN={ebeln}, TEXT={comment}")

        if not ebeln or not comment:
            return jsonify({'status': 'error', 'message': 'PURCHASEORDER dan COMMENT_TEXT wajib diisi'}), 400

        conn = connect_sap(username, password)

        result = conn.call('Z_PO_COMMENT_UPDATE',
            PURCHASEORDER=ebeln,
            COMMENT_TEXT=comment,
            TEXT_ID='F01',
            TEXT_LANGU='EN',
            HEADER_LEVEL='X',
            ITEM_NUMBER='00000'
        )

        print("[DEBUG] SAP Response:", result)
        return jsonify({'status': 'success', 'result': result}), 200

    except Exception as e:
        print("[ERROR] Z_PO_COMMENT_UPDATE:", str(e))
        return jsonify({'status': 'error', 'message': str(e)}), 500



@app.route('/api/z_po_release2', methods=['POST'])
def z_po_release2():
    try:
        username, password = get_credentials()
        conn = connect_sap(username, password)

        data = request.get_json()
        ebeln = data.get('EBELN')
        rel_code = data.get('REL_CODE')

        if not ebeln or not rel_code:
            return jsonify({'status': 'error', 'message': 'EBELN and REL_CODE are required'}), 400

        result = conn.call('Z_PO_RELEASE2', PURCHASEORDER=ebeln, PO_REL_CODE=rel_code)
        return_table = result.get('RETURN', [])
        first_return = return_table[0] if return_table else {}

        if first_return.get('TYPE') != 'E':
            conn.call('BAPI_TRANSACTION_COMMIT', WAIT='X')
            return jsonify({
                'status': 'success',
                'message': first_return.get('MESSAGE', ''),
                'details': return_table
            }), 200
        else:
            return jsonify({
                'status': 'error',
                'message': first_return.get('MESSAGE', ''),
                'details': return_table
            }), 200

    except (ABAPApplicationError, ABAPRuntimeError, LogonError, CommunicationError) as sap_err:
        return jsonify({
            'status': 'sap_error',
            'message': str(sap_err)
        }), 200

    except Exception as e:
        print("Exception saat Z_PO_RELEASE2:", str(e))
        return jsonify({'status': 'exception', 'error': str(e)}), 500

@app.route('/api/schedule_order', methods=['POST'])
def schedule_order():
    try:
        username, password = get_credentials()
        conn = connect_sap(username, password)

        data = request.get_json()
        aufnr = data.get('AUFNR')
        date = data.get('DATE')    # format: YYYYMMDD
        time_str = data.get('TIME')  # format: HH:MM:SS

        if not aufnr or not date or not time_str:
            return jsonify({'error': 'AUFNR, DATE, and TIME are required'}), 400

        # Konversi time string ke datetime.time
        try:
            time_parts = [int(x) for x in time_str.split(':')]
            time_obj = time(*time_parts)
        except Exception as te:
            return jsonify({'error': f'Format jam tidak valid: {time_str}'}), 400

        print(f"Calling BAPI_PRODORD_SCHEDULE with AUFNR={aufnr}, DATE={date}, TIME={time_obj}")

        result = conn.call(
            'BAPI_PRODORD_SCHEDULE',
            SCHED_TYPE='1',
            FWD_BEG_ORIGIN='1',
            FWD_BEG_DATE=date,
            FWD_BEG_TIME=time_obj,
            WORK_PROCESS_GROUP='COWORK_BAPI',
            WORK_PROCESS_MAX=99,
            ORDERS=[{'ORDER_NUMBER': aufnr}]
        )

        conn.call('BAPI_TRANSACTION_COMMIT', WAIT='X')

        return jsonify({
            'sap_return': result.get('RETURN', []),
            'detail_return': result.get('DETAIL_RETURN', []),
            'application_log': result.get('APPLICATION_LOG', []),
        })

    except Exception as e:
        import traceback
        traceback.print_exc()
        return jsonify({'error': str(e)}), 500

# ADD COMPONENT
@app.route('/api/add_component', methods=['POST'])
def add_component():
    try:
        username, password = get_credentials()
        conn = connect_sap(username, password)

        data = request.get_json()
        print("Data diterima untuk add component:", data)

        # Validasi input wajib
        required_fields = ['IV_AUFNR', 'IV_MATNR', 'IV_BDMNG', 'IV_MEINS', 'IV_WERKS', 'IV_LGORT', 'IV_VORNR']
        for field in required_fields:
            if not data.get(field):
                return jsonify({'error': f'{field} is required'}), 400

        # Parameter untuk RFC call
        params = {
            'IV_AUFNR': data.get('IV_AUFNR'),        # Production order number (otomatis dari sebelumnya)
            'IV_MATNR': data.get('IV_MATNR'),        # Material number (input form)
            'IV_BDMNG': str(data.get('IV_BDMNG')),   # Quantity (input form, convert to string)
            'IV_MEINS': data.get('IV_MEINS'),        # Unit of measure (input form)
            'IV_POSTP': 'L',                         # Item category (default 'L')
            'IV_WERKS': data.get('IV_WERKS'),        # Plant (input form)
            'IV_LGORT': data.get('IV_LGORT'),        # Storage location (input form)
            'IV_VORNR': data.get('IV_VORNR')         # Operation number (otomatis dari sebelumnya)
        }

        print("Calling RFC Z_RFC_PRODORD_COMPONENT_ADD...")
        result = conn.call('Z_RFC_PRODORD_COMPONENT_ADD', **params)

        # Commit jika berhasil
        if result.get('EV_SUBRC') == 0:
            conn.call('BAPI_TRANSACTION_COMMIT', WAIT='X')

        return jsonify({
            'success': result.get('EV_SUBRC') == 0,
            'return_message': result.get('EV_RETURN_MSG', ''),
            'status_code': result.get('EV_SUBRC', ''),
            'return_details': result.get('IT_RETURN', [])
        })

    except ValueError as ve:
        return jsonify({'error': str(ve)}), 401
    except Exception as e:
        print("Exception saat add component:", str(e))
        return jsonify({'error': str(e)}), 500

# DELETE COMPONENT
@app.route('/api/delete_component', methods=['POST'])
def delete_component():
    try:
        username, password = get_credentials()
        conn = connect_sap(username, password)

        data = request.get_json()
        print("Data diterima untuk delete component:", data)

        aufnr = data.get('IV_AUFNR')
        rspos = data.get('IV_RSPOS')

        if not aufnr or not rspos:
            return jsonify({'error': 'IV_AUFNR and IV_RSPOS are required'}), 400

        print(f"Calling RFC Z_RFC_PRODORD_COMPONENT_DEL with AUFNR={aufnr}, RSPOS={rspos}")

        result = conn.call(
            'Z_RFC_PRODORD_COMPONENT_DEL',
            IV_AUFNR=aufnr,
            IV_RSPOS=rspos
        )

        # Commit jika berhasil
        if result.get('EV_SUBRC') == 0:
            conn.call('BAPI_TRANSACTION_COMMIT', WAIT='X')

        return jsonify({
            'success': result.get('EV_SUBRC') == 0,
            'return_message': result.get('EV_RETURN_MSG', ''),
            'status_code': result.get('EV_SUBRC', ''),
            'return_details': result.get('IT_RETURN', [])
        })

    except ValueError as ve:
        return jsonify({'error': str(ve)}), 401
    except Exception as e:
        print("Exception saat delete component:", str(e))
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    os.environ['PYTHONHASHSEED'] = '0'
    app.run(host='127.0.0.1', port=8006, debug=True)