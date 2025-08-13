from flask import Flask, request, jsonify
from pyrfc import Connection, ABAPApplicationError, ABAPRuntimeError, LogonError, CommunicationError
from decimal import Decimal
from concurrent.futures import ThreadPoolExecutor
import threading
import os
import traceback
from flask_cors import CORS
from datetime import datetime, time, timezone
import uuid

app = Flask(__name__)
CORS(app, supports_credentials=True, resources={r"/api/*": {"origins": "*"}})

def _now_iso():
    return datetime.now(timezone.utc).isoformat()

def api_response(ok, code, message, http_status=200, data=None, errors=None):
    payload = {
        "ok": ok,
        "code": code,
        "message": message,
        "data": data if ok else None,
        "errors": errors if (not ok and errors) else ([] if not ok else None),
        "meta": {
            "correlation_id": str(uuid.uuid4()),
            "ts": _now_iso()
        }
    }
    return jsonify(payload), http_status

def get_user_profile_from_sap(conn, username: str):
    """
    Tidak ada nama terpisah: gunakan ID SAP sebagai 'nama' untuk display.
    Jika memang ID SAP = username, kembalikan langsung.
    """
    return {
        "id_sap": str(username),  # ganti jika kamu sudah ambil ID SAP asli dari SAP
    }

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


@app.route('/api/sap-login', methods=['POST'])
def sap_login():
    data = request.get_json(silent=True) or {}
    username = data.get('username')
    password = data.get('password')

    missing = [k for k in ['username','password'] if not data.get(k)]
    if missing:
        return api_response(False, "BAD_REQUEST", "username dan password wajib diisi",
                            http_status=400, errors=missing)

    try:
        conn = connect_sap(username, password)
        conn.ping()
        profile = get_user_profile_from_sap(conn, username)
        return api_response(True, "SAP_LOGIN_OK", "Login sukses",
                            http_status=200, data=profile)
    except Exception as e:
        return api_response(False, "SAP_AUTH_FAILED",
                            str(e) or "Username/Password tidak valid",
                            http_status=401)
    
if __name__ == '__main__':
    os.environ['PYTHONHASHSEED'] = '0'
    app.run(host='127.0.0.1', port=8006, debug=True)