<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionTData1;
use App\Models\ProductionTData2;
use App\Models\ProductionTData3;
use App\Models\ProductionTData4;
use App\Models\ProductionMapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ManufactController extends Controller
{
        public function show($plant)
    {
        $categories = ProductionMapping::select('categories', DB::raw('count(*) as total'))
            ->where('plant', $plant)
            ->groupBy('categories')
            ->get();

        return view('manufact.manufact', compact('categories', 'plant'));
    }

    public function detail($plant, $category)
    {
        $data = ProductionMapping::where('plant', $plant)
            ->where('categories', $category)
            ->get();

        return view('manufact.manufact-detail', compact('data', 'plant', 'category'));
    }


    public function syncFromSAP($code)
    {
        set_time_limit(0);

        try {
            $response = Http::timeout(0)->withHeaders([
                'X-SAP-Username' => session('sap_user'),
                'X-SAP-Password' => session('sap_pass'),
            ])->get('http://127.0.0.1:8006/api/sap_combined', [
                'plant' => $code
            ]);

            if (!$response->successful()) {
                return back()->with('error', 'Gagal mengambil data dari SAP.');
            }

            $data = $response->json();

        function formatTanggal($tgl)
        {
            if (empty($tgl)) return null;

            try {
                return Carbon::createFromFormat('Ymd', $tgl)->format('d-m-Y');
            } catch (\Exception $e) {
                return $tgl; // fallback, kalau format tidak sesuai
            }
        }
        
            // == T_DATA1
            $orderx1 = [];

            foreach ($data['T_DATA1'] ?? [] as $row) {
                $orderx = $row['ORDERX'] ?? null;
                $vornr = $row['VORNR'] ?? null;
                if ($orderx) $orderx1[] = [$orderx, $vornr];

                // Buat field PV1, PV2, PV3 jika datanya ada
                $sssl1 = formatTanggal($row['SSSLDPV1'] ?? '');
                $sssl2 = formatTanggal($row['SSSLDPV2'] ?? '');
                $sssl3 = formatTanggal($row['SSSLDPV3'] ?? '');

                $pv1 = (!empty($row['ARBPL1']) && !empty($sssl1)) ? strtoupper($row['ARBPL1'] . ' - ' . $sssl1) : null;
                $pv2 = (!empty($row['ARBPL2']) && !empty($sssl2)) ? strtoupper($row['ARBPL2'] . ' - ' . $sssl2) : null;
                $pv3 = (!empty($row['ARBPL3']) && !empty($sssl3)) ? strtoupper($row['ARBPL3'] . ' - ' . $sssl3) : null;

                ProductionTData1::updateOrCreate([
                    'ORDERX' => $orderx,
                    'VORNR' => $vornr,
                ], array_merge($row, [
                    'PV1' => $pv1,
                    'PV2' => $pv2,
                    'PV3' => $pv3,
                ]));
            }


            // == T_DATA2
            $kd2WithKey = [];
            $kd2WithoutKey = [];

            foreach ($data['T_DATA2'] ?? [] as $row) {
                $row['WERKSX'] = $code;

                if (empty($row['EDATU'])) $row['EDATU'] = null;

                try {
                    if (!empty($row['KDAUF']) && !empty($row['KDPOS'])) {
                        $kd2WithKey[] = [$row['KDAUF'], $row['KDPOS']];
                        ProductionTData2::updateOrCreate([
                            'KDAUF' => $row['KDAUF'],
                            'KDPOS' => $row['KDPOS'],
                        ], $row);
                    } else {
                        // tetap simpan dan lacak dengan ID setelah create
                        $created = ProductionTData2::create($row);
                        $kd2WithoutKey[] = $created->id;
                    }
                } catch (\Exception $e) {
                    Log::warning('Gagal simpan T_DATA2', ['row' => $row, 'error' => $e->getMessage()]);
                }
            }


            // == T_DATA3
            $orderx3 = [];

            foreach ($data['T_DATA3'] ?? [] as $row) {
                if (!isset($row['ORDERX'])) continue;

                $orderx3[] = [$row['ORDERX'], $row['VORNR'] ?? null];

                ProductionTData3::updateOrCreate([
                    'ORDERX' => $row['ORDERX'],
                    'VORNR' => $row['VORNR'] ?? null,
                ], $row);
            }

            // == T_DATA4
            $rsnum4 = [];

            foreach ($data['T_DATA4'] ?? [] as $row) {
                if (!isset($row['RSNUM']) || !isset($row['RSPOS'])) continue;

                $rsnum4[] = [$row['RSNUM'], $row['RSPOS']];

                ProductionTData4::updateOrCreate([
                    'RSNUM' => $row['RSNUM'],
                    'RSPOS' => $row['RSPOS'],
                ], $row);
            }

            // === DELETE DATA YANG TIDAK ADA DI SAP ===
            // === DELETE T_DATA1 YANG TIDAK ADA ===
            $existing1 = ProductionTData1::all();
            $toKeep1 = collect($orderx1);
            foreach ($existing1 as $item) {
                if (!$toKeep1->contains(function ($val) use ($item) {
                    return $val[0] === $item->ORDERX && $val[1] === $item->VORNR;
                })) {
                    $item->delete();
                }
            }

            // === DELETE T_DATA2 YANG TIDAK ADA (khusus plant itu saja) ===
            $existing2 = ProductionTData2::where('WERKSX', $code)->get();
            $toKeep2WithKey = collect($kd2WithKey);
            $toKeep2WithoutKey = collect($kd2WithoutKey);

            foreach ($existing2 as $item) {
                if (!empty($item->KDAUF) && !empty($item->KDPOS)) {
                    if (!$toKeep2WithKey->contains(function ($val) use ($item) {
                        return $val[0] === $item->KDAUF && $val[1] === $item->KDPOS;
                    })) {
                        $item->delete();
                    }
                } else {
                    if (!$toKeep2WithoutKey->contains($item->id)) {
                        $item->delete();
                    }
                }
            }


            // === DELETE T_DATA3 YANG TIDAK ADA ===
            $existing3 = ProductionTData3::all();
            $toKeep3 = collect($orderx3);
            foreach ($existing3 as $item) {
                if (!$toKeep3->contains(function ($val) use ($item) {
                    return $val[0] === $item->ORDERX && $val[1] === $item->VORNR;
                })) {
                    $item->delete();
                }
            }

            // === DELETE T_DATA4 YANG TIDAK ADA ===
            $existing4 = ProductionTData4::all();
            $toKeep4 = collect($rsnum4);
            foreach ($existing4 as $item) {
                if (!$toKeep4->contains(function ($val) use ($item) {
                    return $val[0] === $item->RSNUM && $val[1] === $item->RSPOS;
                })) {
                    $item->delete();
                }
            }


            return redirect()->route('manufact.data2.detail', $code)
                ->with('success', 'Data berhasil disinkronkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }


    public function showDetail(Request $request, $code)
    {
        $search = $request->get('search');

        $query = ProductionTData2::where('WERKSX', $code)
            ->orderByRaw('KDAUF IS NULL DESC')
            ->orderByRaw('KDAUF = "" DESC')
            ->orderBy('KDAUF');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('KDAUF', 'like', "%$search%")
                    ->orWhere('KDPOS', 'like', "%$search%")
                    ->orWhere('MATFG', 'like', "%$search%")
                    ->orWhere('MAKFG', 'like', "%$search%")
                    ->orWhere('EDATU', 'like', "%$search%");
            });

            $query->orWhereHas('tData3', function($q) use ($search) {
                $q->where('AUFNR', 'like', "%$search%")
                    ->orWhere('PLNUM', 'like', "%$search%")
                    ->orWhere('MATNR', 'like', "%$search%");
            });

            $query->orWhereHas('tData3.tData1', function($q) use ($search) {
                $q->where('VORNR', 'like', "%$search%")
                    ->orWhere('ARBPL', 'like', "%$search%")
                    ->orWhere('AUFNR', 'like', "%$search%");
            });

            $query->orWhereHas('tData3.tData4', function($q) use ($search) {
                $q->where('RSNUM', 'like', "%$search%")
                    ->orWhere('RSPOS', 'like', "%$search%")
                    ->orWhere('MATNR', 'like', "%$search%")
                    ->orWhere('MAKTX', 'like', "%$search%");
            });
        }

        // Get all data without pagination
        $details = $query->get();

        // Get all related T_DATA3
        $allTData3 = ProductionTData3::whereIn('KDAUF', $details->pluck('KDAUF'))
                        ->whereIn('KDPOS', $details->pluck('KDPOS'))
                        ->get()
                        ->groupBy(fn($item) => $item->KDAUF . '-' . $item->KDPOS);

        $aufnrList = $allTData3->flatten()->pluck('AUFNR')->filter()->unique();

        // Get all related T_DATA1
        $allTData1 = ProductionTData1::whereIn('AUFNR', $aufnrList)
                        ->orderBy('VORNR', 'asc') // Tambahkan ini untuk sorting ascending
                        ->get()
                        ->groupBy('AUFNR');

        // Get all related T_DATA4 (BOM)
        $allTData4 = ProductionTData4::whereIn('AUFNR', $aufnrList)
                            ->get()
                            ->groupBy('AUFNR');

        $plnumList = $allTData3->flatten()->pluck('PLNUM')->filter()->unique();

        $allTData4ByPlnum = ProductionTData4::whereIn('PLNUM', $plnumList)
            ->get()
            ->groupBy('PLNUM');

        $bagian = ProductionMapping::where('code', $code)->value('bagian') ?? '-';
        $categories = ProductionMapping::where('code', $code)->value('categories') ?? '-';
        $plant = ProductionMapping::where('code', $code)->value('plant') ?? '-';
        
        return view('manufact.detail-data2', compact(
            'details', 'code', 'allTData3', 'allTData1', 'allTData4', 'allTData4ByPlnum', 'bagian', 'categories', 'plant'
        ));
    }

    public function getTData3($kdauf, $kdpos)
    {
        $data3 = ProductionTData3::where('KDAUF', $kdauf)
                    ->where('KDPOS', $kdpos)
                    ->get();

        return response()->json($data3);
    }


public function rescheduleOrder(Request $request)
{
    $request->validate([
        'aufnr' => 'required|string',
        'date' => 'required|date_format:Y-m-d',
        'time' => ['required', 'regex:/^\d{2}\.\d{2}\.\d{2}$/'], // format: 00.00.00
    ]);

    // Ambil kredensial SAP dari session
    $sapUser = session('sap_user');
    $sapPass = session('sap_pass');

    // Format SAP: tanggal Ymd dan jam H:i:s
    $tanggal = str_replace('-', '', $request->input('date')); // Contoh: 2025-07-11 → 20250711
    $jam = str_replace('.', ':', $request->input('time'));     // Contoh: 00.00.00 → 00:00:00

    try {
        $response = Http::timeout(0)->withHeaders([
            'X-SAP-Username' => $sapUser,
            'X-SAP-Password' => $sapPass,
        ])->post('http://127.0.0.1:8006/api/schedule_order', [
            'AUFNR' => $request->input('aufnr'),
            'DATE'  => $tanggal,
            'TIME'  => $jam,
        ]);

        if ($response->successful()) {
            $sapMessages = collect($response->json('sap_return') ?? [])->pluck('MESSAGE')->implode(', ');
            return back()->with('success', "Reschedule berhasil. SAP: {$sapMessages}");
        }

        return back()->with('error', 'Gagal reschedule: ' . ($response->json('error') ?? 'Tidak diketahui'));

    } catch (\Exception $e) {
        Log::error('Reschedule SAP error', ['message' => $e->getMessage()]);
        return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

    // ADD COMPONENT METHOD
    public function addComponent(Request $request)
    {
        $request->validate([
            'iv_aufnr' => 'required|string',
            'iv_matnr' => 'required|string',
            'iv_bdmng' => 'required|numeric|min:0.01',
            'iv_meins' => 'required|string',
            'iv_werks' => 'required|string',
            'iv_lgort' => 'required|string',
            'iv_vornr' => 'required|string',
        ]);

        try {
            $response = Http::timeout(0)->withHeaders([
                'X-SAP-Username' => session('sap_user'),
                'X-SAP-Password' => session('sap_pass'),
            ])->post('http://127.0.0.1:8006/api/add_component', [
                'IV_AUFNR' => $request->input('iv_aufnr'),
                'IV_MATNR' => $request->input('iv_matnr'),
                'IV_BDMNG' => $request->input('iv_bdmng'),
                'IV_MEINS' => $request->input('iv_meins'),
                'IV_WERKS' => $request->input('iv_werks'),
                'IV_LGORT' => $request->input('iv_lgort'),
                'IV_VORNR' => $request->input('iv_vornr'),
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['success']) {
                    return back()->with('success', 'Component berhasil ditambahkan: ' . $result['return_message']);
                } else {
                    return back()->with('error', 'Gagal menambah component: ' . $result['return_message']);
                }
            }

            return back()->with('error', 'Gagal menambah component: ' . ($response->json('error') ?? 'Tidak diketahui'));

        } catch (\Exception $e) {
            Log::error('Add Component SAP error', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // DELETE COMPONENT METHOD
    public function deleteComponent(Request $request)
    {
        $request->validate([
            'iv_aufnr' => 'required|string',
            'iv_rspos' => 'required|string',
        ]);

        try {
            $response = Http::timeout(0)->withHeaders([
                'X-SAP-Username' => session('sap_user'),
                'X-SAP-Password' => session('sap_pass'),
            ])->post('http://127.0.0.1:8006/api/delete_component', [
                'IV_AUFNR' => $request->input('iv_aufnr'),
                'IV_RSPOS' => $request->input('iv_rspos'),
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['success']) {
                    return back()->with('success', 'Component berhasil dihapus: ' . $result['return_message']);
                } else {
                    return back()->with('error', 'Gagal menghapus component: ' . $result['return_message']);
                }
            }

            return back()->with('error', 'Gagal menghapus component: ' . ($response->json('error') ?? 'Tidak diketahui'));

        } catch (\Exception $e) {
            Log::error('Delete Component SAP error', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

}