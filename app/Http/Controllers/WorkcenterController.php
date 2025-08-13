<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Workcenters;
use Illuminate\Http\Request;
use App\Models\ProductionTData3;
use App\Models\WorkcenterTData1;
use App\Models\WorkcenterTData2;
use App\Models\WorkcenterTData3;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;


class WorkcenterController extends Controller
{
    public function index()
    {
        if (!session('sap_user') || !session('sap_pass')) {
            return redirect()->route('sap.login')->with('error', 'Silakan login SAP terlebih dahulu.');
        }

        $plants = Workcenters::select('plant')->distinct()->pluck('plant');
        $workcenters = Workcenters::all();

        return view('workcenter', compact('plants', 'workcenters'));
    }

    public function fetchFromSAP(Request $request)
    {
        $plant = $request->input('plant');
        $workcenter = $request->input('workcenter');
        $perPage = $request->input('per_page', 10);
        $currentPage = $request->input('page', 1);

        try {
            $response = Http::timeout(0)->withHeaders([
                'X-SAP-Username' => session('sap_user'),
                'X-SAP-Password' => session('sap_pass'),
            ])->get('http://127.0.0.1:8006/api/sap_data', [
                'plant' => $plant,
                'workcenter' => $workcenter,
            ]);

            if ($response->successful()) {
                $allData = collect($response->json());

                if ($perPage !== 'all') {
                    $perPage = (int) $perPage;
                    $data = $allData->forPage($currentPage, $perPage);
                    $paginator = new LengthAwarePaginator(
                        $data,
                        $allData->count(),
                        $perPage,
                        $currentPage,
                        ['path' => url()->current(), 'query' => $request->query()]
                    );
                } else {
                    $paginator = $allData;
                }

                return view('workcenter.result', compact('paginator', 'plant', 'workcenter'))->with('per_page', $perPage);
            }

            return back()->withErrors(['msg' => 'Gagal mengambil data dari SAP']);
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function resultDetail(Request $request)
    {
        $plant = $request->query('plant');
        $workcenter = $request->query('workcenter');
        $perPage = $request->query('per_page', 10);
        $currentPage = $request->query('page', 1);

        try {
            $response = Http::timeout(0)->withHeaders([
                'X-SAP-Username' => session('sap_user'),
                'X-SAP-Password' => session('sap_pass'),
            ])->get('http://127.0.0.1:8006/api/sap_detail', [
                'plant' => $plant,
                'workcenter' => $workcenter,
            ]);

            if (!$response->successful()) {
                return back()->with('error', 'Gagal mengambil data dari SAP.');
            }

            $items = collect($response->json());

            // Filtering berdasarkan pencarian
            if ($search = $request->query('search')) {
                $items = $items->filter(function ($item) use ($search) {
                    return str_contains(strtolower($item['AUFNR'] ?? ''), strtolower($search)) ||
                           str_contains(strtolower($item['ARBPL'] ?? ''), strtolower($search)) ||
                           str_contains(strtolower($item['MATNR'] ?? ''), strtolower($search)) ||
                           str_contains(strtolower($item['VORNR'] ?? ''), strtolower($search)) ||
                           str_contains(strtolower($item['PLNUM'] ?? ''), strtolower($search)) ||
                           str_contains(strtolower($item['STATS'] ?? ''), strtolower($search)) ||
                           str_contains(strtolower($item['VERID'] ?? ''), strtolower($search));
                })->values();
            }

            // Ambil yang status CRTD
            $crtdOptions = $items->where('STATS', 'CRTD')->map(function ($item) {
                return [
                    'AUFNR' => $item['AUFNR'],
                    'VERID' => $item['VERID'] ?? '-',
                    'ARBPL' => $item['ARBPL'] ?? '-',
                    'VORNR' => $item['VORNR'] ?? '-',
                ];
            })->unique(fn($item) => $item['AUFNR'] . '|' . $item['VERID'] . '|' . $item['ARBPL'])->values();

            // Sorting
            $sortBy = $request->query('sort_by');
            $sortDir = strtolower($request->query('sort_direction', 'asc'));
            if ($sortBy && $items->isNotEmpty() && isset($items->first()[$sortBy])) {
                $items = $items->sortBy(fn($item) => $item[$sortBy], SORT_REGULAR, $sortDir === 'desc')->values();
            }

            // Pagination
            $paginated = ($perPage === 'all') ? $items : new LengthAwarePaginator(
                $items->slice(($currentPage - 1) * $perPage, $perPage)->values(),
                $items->count(),
                (int) $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return view('workcenter.result_detail', compact('paginated', 'plant', 'workcenter', 'crtdOptions'))
                ->with('per_page', $perPage);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengambil data: ' . $e->getMessage());
        }
    }

public function saveEdit(Request $request)
{
    $request->validate([
        'aufnr' => 'required|string',
        'operation' => 'required|string',
        'work_center' => 'required|string',
    ]);

    $aufnr = $request->aufnr;
    $operation = $request->operation;
    $workCenter = $request->work_center;

    $payload = [
        'IV_AUFNR' => $aufnr,
        'IV_COMMIT' => 'X',
        'IT_OPERATION' => [[
            'SEQUEN' => '0',
            'OPER' => $operation,
            'WORK_CEN' => $workCenter,
            'W' => 'X'
        ]]
    ];

    $sapUser = session('sap_user');
    $sapPass = session('sap_pass');

    $response = Http::timeout(0)->withHeaders([
        'X-SAP-Username' => $sapUser,
        'X-SAP-Password' => $sapPass,
    ])->post('http://127.0.0.1:8006/api/save_edit', $payload);

    if ($response->successful()) {
        $message = data_get($response->json(), 'RETURN.0.MESSAGE', 'Work center berhasil diubah.');

        // ✅ Ambil plant dari DB berdasarkan AUFNR
        $plant = \App\Models\ProductionTData3::where('AUFNR', $aufnr)->value('WERKSX');

        if ($plant) {
            $syncResponse = Http::timeout(0)->withHeaders([
                'X-SAP-Username' => $sapUser,
                'X-SAP-Password' => $sapPass,
            ])->post('http://127.0.0.1:8006/api/sap_combined_multi', [
                'plant' => $plant,
                'aufnrs' => [$aufnr],
            ]);

            if ($syncResponse->successful()) {
                $syncData = $syncResponse->json();

                function formatTanggal($tgl)
                {
                    if (empty($tgl)) return null;
                    try {
                        return \Carbon\Carbon::createFromFormat('Ymd', $tgl)->format('d-m-Y');
                    } catch (\Exception $e) {
                        return null;
                    }
                }

                foreach ($syncData['T_DATA1'] ?? [] as $row1) {
                    $orderx = $row1['ORDERX'] ?? null;
                    $vornr = $row1['VORNR'] ?? null;

                    $sssl1 = formatTanggal($row1['SSSLDPV1'] ?? '');
                    $sssl2 = formatTanggal($row1['SSSLDPV2'] ?? '');
                    $sssl3 = formatTanggal($row1['SSSLDPV3'] ?? '');

                    $pv1 = (!empty($row1['ARBPL1']) && !empty($sssl1)) ? strtoupper($row1['ARBPL1'] . ' - ' . $sssl1) : null;
                    $pv2 = (!empty($row1['ARBPL2']) && !empty($sssl2)) ? strtoupper($row1['ARBPL2'] . ' - ' . $sssl2) : null;
                    $pv3 = (!empty($row1['ARBPL3']) && !empty($sssl3)) ? strtoupper($row1['ARBPL3'] . ' - ' . $sssl3) : null;
Log::info('Updating/Creating T_DATA1', [
    'key' => ['ORDERX' => $orderx, 'VORNR' => $vornr],
    'data' => array_merge($row1, [
        'PV1' => $pv1,
        'PV2' => $pv2,
        'PV3' => $pv3,
    ]),
]);

                    $existing = \App\Models\ProductionTData1::where('ORDERX', $orderx)->where('VORNR', $vornr)->first();

\App\Models\ProductionTData1::updateOrCreate([
    'ORDERX' => $orderx,
    'VORNR' => $vornr,
], array_merge($row1, [
    'PV1' => $pv1 ?? optional($existing)->PV1,
    'PV2' => $pv2 ?? optional($existing)->PV2,
    'PV3' => $pv3 ?? optional($existing)->PV3,
]));

                }

                foreach ($syncData['T_DATA4'] ?? [] as $row4) {
                    \App\Models\ProductionTData4::updateOrCreate([
                        'RSNUM' => $row4['RSNUM'],
                        'RSPOS' => $row4['RSPOS'],
                    ], $row4);
                }
            } else {
                Log::warning("Gagal sync /sap_combined_multi untuk AUFNR: $aufnr - Plant: $plant");
            }
        } else {
            Log::warning("Plant tidak ditemukan untuk AUFNR: $aufnr");
        }

        return back()->with('success', "Work center berhasil diubah. Pesan SAP: {$message}");
    }

    return back()->with('error', 'Gagal menyimpan: ' . ($response->json('error') ?? 'Tidak diketahui'));
}

    public function saveEditPv(Request $request)
{
    $request->validate([
        'aufnr' => 'required|string',
        'prod_version' => 'required|string',
    ]);

    $aufnr = $request->input('aufnr');
    $sapUser = session('sap_user');
    $sapPass = session('sap_pass');

    $payload = [
        'AUFNR' => $aufnr,
        'PROD_VERSION' => $request->input('prod_version'),
        'CURRENT_VERSION' => $request->input('current_pv'),
    ];

    $response = Http::timeout(0)->withHeaders([
        'X-SAP-Username' => $sapUser,
        'X-SAP-Password' => $sapPass,
    ])->post('http://127.0.0.1:8006/api/change_prod_version', $payload);

    if ($response->successful()) {
        $data = $response->json();
        $before = $data['before_version'] ?? 'unknown';
        $after = $data['after_version'] ?? 'unknown';
        $messages = collect($data['sap_return'] ?? [])->pluck('MESSAGE')->filter()->implode(', ');

        // ✅ Ambil plant dari DB berdasarkan AUFNR
        $plant = \App\Models\ProductionTData3::where('AUFNR', $aufnr)->value('WERKSX');

        if ($plant) {
            $syncResponse = Http::timeout(0)->withHeaders([
                'X-SAP-Username' => $sapUser,
                'X-SAP-Password' => $sapPass,
            ])->post('http://127.0.0.1:8006/api/sap_combined_multi', [
                'plant' => $plant,
                'aufnrs' => [$aufnr],
            ]);

            if ($syncResponse->successful()) {
                $syncData = $syncResponse->json();

                function formatTanggal($tgl)
                {
                    if (empty($tgl)) return null;
                    try {
                        return \Carbon\Carbon::createFromFormat('Ymd', $tgl)->format('d-m-Y');
                    } catch (\Exception $e) {
                        return null;
                    }
                }

                foreach ($syncData['T_DATA1'] ?? [] as $row1) {
                    $orderx = $row1['ORDERX'] ?? null;
                    $vornr = $row1['VORNR'] ?? null;

                    $sssl1 = formatTanggal($row1['SSSLDPV1'] ?? '');
                    $sssl2 = formatTanggal($row1['SSSLDPV2'] ?? '');
                    $sssl3 = formatTanggal($row1['SSSLDPV3'] ?? '');

                    $pv1 = (!empty($row1['ARBPL1']) && !empty($sssl1)) ? strtoupper($row1['ARBPL1'] . ' - ' . $sssl1) : null;
                    $pv2 = (!empty($row1['ARBPL2']) && !empty($sssl2)) ? strtoupper($row1['ARBPL2'] . ' - ' . $sssl2) : null;
                    $pv3 = (!empty($row1['ARBPL3']) && !empty($sssl3)) ? strtoupper($row1['ARBPL3'] . ' - ' . $sssl3) : null;

                    $existing = \App\Models\ProductionTData1::where('ORDERX', $orderx)->where('VORNR', $vornr)->first();

                    \Illuminate\Support\Facades\Log::info('Updating/Creating T_DATA1', [
                        'key' => ['ORDERX' => $orderx, 'VORNR' => $vornr],
                        'data' => array_merge($row1, [
                            'PV1' => $pv1,
                            'PV2' => $pv2,
                            'PV3' => $pv3,
                        ]),
                    ]);

                    \App\Models\ProductionTData1::updateOrCreate([
                        'ORDERX' => $orderx,
                        'VORNR' => $vornr,
                    ], array_merge($row1, [
                        'PV1' => $pv1 ?? optional($existing)->PV1,
                        'PV2' => $pv2 ?? optional($existing)->PV2,
                        'PV3' => $pv3 ?? optional($existing)->PV3,
                    ]));
                }

                foreach ($syncData['T_DATA4'] ?? [] as $row4) {
                    \App\Models\ProductionTData4::updateOrCreate([
                        'RSNUM' => $row4['RSNUM'],
                        'RSPOS' => $row4['RSPOS'],
                    ], $row4);
                }
            } else {
                Log::warning("Gagal sync /sap_combined_multi untuk AUFNR: $aufnr - Plant: $plant");
            }
        } else {
            Log::warning("Plant tidak ditemukan untuk AUFNR: $aufnr");
        }

        return back()->with('success', "PV berhasil diubah. SAP: $messages");
    }

    return back()->with('error', 'Gagal menyimpan: ' . ($response->json('error') ?? 'Tidak diketahui'));
}



    public function saveEditMassal(Request $request)
    {
        $action = $request->input('action');
        $selectedRows = $request->input('selected_rows', []);
        $newValue = $request->input('work_center');
        $sapUsername = session('sap_user');
        $sapPassword = session('sap_pass');

        if (!$sapUsername || !$sapPassword) {
            return back()->with('error', 'SAP credentials not found in session.');
        }

        if (empty($selectedRows)) {
            return back()->with('error', 'No rows selected.');
        }

        $errors = [];

        foreach ($selectedRows as $row) {
            [$aufnr, $vornr] = explode('|', $row);
            $payload = ($action === 'change_wc') ? [
                'IV_AUFNR' => $aufnr,
                'IV_COMMIT' => 'X',
                'IT_OPERATION' => [[
                    'SEQUEN' => '00',
                    'OPER' => $vornr,
                    'WORK_CEN' => $newValue,
                    'W' => 'X',
                ]]
            ] : [
                'AUFNR' => $aufnr,
                'PROD_VERSION' => $newValue,
            ];

            $url = $action === 'change_wc'
                ? 'http://127.0.0.1:8006/api/save_edit'
                : 'http://127.0.0.1:8006/api/change_prod_version';

            $response = Http::timeout(0)->withHeaders([
                'X-SAP-Username' => $sapUsername,
                'X-SAP-Password' => $sapPassword,
            ])->post($url, $payload);

            if ($response->failed()) {
                $errors[] = "AUFNR {$aufnr}: " . ($response->json('error') ?? 'Gagal menghubungi SAP');
            } else {
                $message = data_get($response->json(), 'RETURN.0.MESSAGE') ??
                           data_get($response->json(), 'BAPI_PRODORD_CHANGE.RETURN.0.MESSAGE');
                Log::info("AUFNR {$aufnr} berhasil diubah: {$message}");
            }
        }

        return count($errors)
            ? back()->with('error', 'Beberapa perubahan gagal: ' . implode('; ', $errors))
            : back()->with('success', 'Perubahan berhasil dikirim ke SAP.');
    }

public function releaseOrderDirect(Request $request, $aufnr)
{
    $payload = ['AUFNR' => $aufnr];

    $response = Http::timeout(0)->withHeaders([
        'X-SAP-Username' => session('sap_user'),
        'X-SAP-Password' => session('sap_pass'),
    ])->post('http://127.0.0.1:8006/api/release_order', $payload);

    if ($response->successful()) {
        $data = $response->json();
        $return = $data['RETURN'] ?? $data['BAPI_PRODORD_RELEASE']['RETURN'] ?? [];
        $message = is_array($return) ? ($return[0]['MESSAGE'] ?? 'Order berhasil direlease') : 'Order berhasil direlease';

        ProductionTData3::where('AUFNR', $aufnr)->update(['STATS' => 'REL']);

        // Jika request dari fetch() (AJAX), kirim response JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Order $aufnr berhasil direlease. Pesan SAP: {$message}"
            ]);
        }

        // Kalau bukan dari fetch, kembalikan redirect biasa
        return back()->with('success', "Order $aufnr berhasil direlease. Pesan SAP: {$message}");
    }

    $errorMessage = $response->json('error') ?? 'Tidak diketahui';

    if ($request->expectsJson()) {
        return response()->json([
            'success' => false,
            'message' => "Order $aufnr gagal direlease: $errorMessage"
        ], 500);
    }

    return back()->with('error', "Order $aufnr gagal direlease: $errorMessage");
}

public function convertPlannedOrder(Request $request)
{
    $request->validate([
        'plnum' => 'required|string',
        'auart' => 'required|string',
    ]);

    $plnum = $request->input('plnum');
    $auart = $request->input('auart');

    $payload = [
        'PLANNED_ORDER' => $plnum,
        'AUART' => $auart,
    ];

    $response = Http::timeout(0)->withHeaders([
        'X-SAP-Username' => session('sap_user'),
        'X-SAP-Password' => session('sap_pass'),
    ])->post('http://127.0.0.1:8006/api/create_prod_order', $payload);

    if ($response->successful()) {
        $data = $response->json();
        $orderNumber = $data['order_number'] ?? null;
        $sapMessage = $data['return']['MESSAGE'] ?? 'Tidak ada pesan dari SAP';

        if (empty($orderNumber)) {
            return back()->with('error', "SAP berhasil diakses, tetapi tidak mengembalikan nomor production order. Pesan SAP: {$sapMessage}");
        }

        // Ambil plant dari data T_DATA3
        $row = \App\Models\ProductionTData3::where('PLNUM', $plnum)->first();
        $plant = $row?->WERKSX ?? null;

        if (!$plant) {
            return back()->with('error', "Plant (WERKSX) tidak ditemukan untuk PLNUM: {$plnum}");
        }

        // Setelah $orderNumber didapat dan sebelum request ke Flask
            $row = \App\Models\ProductionTData3::where('PLNUM', $plnum)->first();
            $plant = $row?->WERKSX ?? null;
            $kdauf = $row?->KDAUF ?? null;
            $kdpos = $row?->KDPOS ?? null;

            if (!$plant || !$kdauf || !$kdpos) {
                return back()->with('error', "Data plant/KDAUF/KDPOS tidak ditemukan untuk PLNUM: {$plnum}");
            }

            // Update T_DATA3
            \App\Models\ProductionTData3::where('PLNUM', $plnum)->update([
                'PLNUM' => null,
                'AUFNR' => $orderNumber,
                'ORDERX' => $orderNumber,
                'STATS' => 'CRTD',
            ]);

            // Ambil semua AUFNR yang memiliki KDAUF+KDPOS yang sama
            $aufnrList = \App\Models\ProductionTData3::where('KDAUF', $kdauf)
                ->where('KDPOS', $kdpos)
                ->whereNotNull('AUFNR')
                ->pluck('AUFNR')
                ->unique()
                ->values()
                ->toArray();

            // Panggil Flask untuk semua AUFNR tersebut
            $syncResponse = Http::timeout(0)->withHeaders([
                'X-SAP-Username' => session('sap_user'),
                'X-SAP-Password' => session('sap_pass'),
            ])->post('http://127.0.0.1:8006/api/sap_combined_multi', [
                'plant' => $plant,
                'aufnrs' => $aufnrList,
            ]);

        if ($syncResponse->successful()) {
            $syncData = $syncResponse->json();

            // Fungsi bantu format tanggal
            function formatTanggal($tgl)
            {
                if (empty($tgl)) return null;
                try {
                    return \Carbon\Carbon::createFromFormat('Ymd', $tgl)->format('d-m-Y');
                } catch (\Exception $e) {
                    return null;
                }
            }

            // T_DATA1
            foreach ($syncData['T_DATA1'] ?? [] as $row1) {
                $orderx = $row1['ORDERX'] ?? null;
                $vornr = $row1['VORNR'] ?? null;

                $sssl1 = formatTanggal($row1['SSSLDPV1'] ?? '');
                $sssl2 = formatTanggal($row1['SSSLDPV2'] ?? '');
                $sssl3 = formatTanggal($row1['SSSLDPV3'] ?? '');

                $pv1 = (!empty($row1['ARBPL1']) && !empty($sssl1)) ? strtoupper($row1['ARBPL1'] . ' - ' . $sssl1) : null;
                $pv2 = (!empty($row1['ARBPL2']) && !empty($sssl2)) ? strtoupper($row1['ARBPL2'] . ' - ' . $sssl2) : null;
                $pv3 = (!empty($row1['ARBPL3']) && !empty($sssl3)) ? strtoupper($row1['ARBPL3'] . ' - ' . $sssl3) : null;

                \App\Models\ProductionTData1::updateOrCreate([
                    'ORDERX' => $orderx,
                    'VORNR' => $vornr,
                ], array_merge($row1, [
                    'PV1' => $pv1,
                    'PV2' => $pv2,
                    'PV3' => $pv3,
                ]));
            }

            // T_DATA4
            foreach ($syncData['T_DATA4'] ?? [] as $row4) {
                \App\Models\ProductionTData4::updateOrCreate([
                    'RSNUM' => $row4['RSNUM'],
                    'RSPOS' => $row4['RSPOS'],
                ], $row4);
            }
        } else {
            Log::warning("Gagal sinkronisasi T_DATA1 dan T_DATA4 untuk AUFNR: $orderNumber, PLANT: $plant");
        }

        return back()->with('success', "Planned order dikonversi ke production order: {$orderNumber}. Pesan SAP: {$sapMessage}");
    }

    return back()->with('error', 'Gagal mengonversi ke SAP: ' . ($response->json('error') ?? 'Tidak diketahui'));
}



    public function convertMassalPlannedOrders(Request $request)
    {
        $request->validate([
            'selected_plnums' => 'required|array',
        ]);

        $results = [];

        foreach ($request->input('selected_plnums') as $item) {
            [$plnum, $auart] = explode('|', $item);

            $payload = [
                'PLANNED_ORDER' => $plnum,
                'AUART' => $auart,
            ];

            try {
                $response = Http::timeout(30)->withHeaders([
                    'X-SAP-Username' => session('sap_user'),
                    'X-SAP-Password' => session('sap_pass'),
                ])->post('http://127.0.0.1:8006/api/create_prod_order', $payload);

                if ($response->successful()) {
                    $data = $response->json();
                    $results[] = [
                        'plnum' => $plnum,
                        'order_number' => $data['order_number'] ?? '-',
                        'status' => $data['success'] ? 'success' : 'failed',
                        'message' => $data['return']['MESSAGE'] ?? 'No message',
                    ];
                } else {
                    $results[] = [
                        'plnum' => $plnum,
                        'status' => 'error',
                        'message' => 'HTTP Error: ' . $response->status(),
                    ];
                }
            } catch (\Exception $e) {
                $results[] = [
                    'plnum' => $plnum,
                    'status' => 'exception',
                    'message' => $e->getMessage(),
                ];
            }
        }

        $successCount = collect($results)->where('status', 'success')->count();
        $failedCount = count($results) - $successCount;
        $messages = collect($results)->map(fn($r) => "{$r['plnum']} → {$r['status']} ({$r['message']})")->implode('<br>');

        return back()->with([
            'success' => "Convert selesai: {$successCount} berhasil, {$failedCount} gagal.",
            'detail_message' => $messages,
        ]);
    }

    public function submitData(Request $request)
    {
        $plant = $request->input('plant');
        $workcenter = $request->input('workcenter');
        $workcenterCodes = array_map('trim', explode(',', $workcenter));

        try {
            $response = Http::timeout(0)->withHeaders([
                'X-SAP-Username' => session('sap_user'),
                'X-SAP-Password' => session('sap_pass'),
            ])->get('http://127.0.0.1:8006/api/sap_combined', [
                'plant' => $plant,
                'workcenter' => $workcenter,
            ]);

            if (!$response->successful()) {
                return back()->with('error', 'Gagal mengambil data dari SAP.');
            }

            $data = $response->json();

            // === Sinkronisasi T_DATA1 ===
            $existingTData1 = \App\Models\WorkcenterTData1::whereIn('ARBPL', $workcenterCodes)->get();
            $incomingTData1 = collect($data['T_DATA1'] ?? []);

            $incomingAUFNR = $incomingTData1->pluck('AUFNR')->filter()->unique();

            foreach ($existingTData1 as $item) {
                if (!$incomingAUFNR->contains($item->AUFNR)) {
                    $item->delete();
                }
            }

            foreach ($incomingTData1 as $row) {
                foreach ($row as $k => $v) {
                    if ($v === '') $row[$k] = null;
                }

                // Jika KDAUF/KDPOS kosong, set ke null (tidak pakai '__BLANK__' lagi)
                if (empty($row['KDAUF'])) $row['KDAUF'] = null;
                if (empty($row['KDPOS'])) $row['KDPOS'] = null;

                \App\Models\WorkcenterTData1::updateOrCreate(
                    ['AUFNR' => $row['AUFNR']],
                    $row
                );
            }

            // === Sinkronisasi T_DATA2 ===
            $existingTData2 = \App\Models\WorkcenterTData2::whereIn('ARBPL', $workcenterCodes)->get();
            $incomingTData2 = collect($data['T_DATA2'] ?? []);
            $incomingARBPL = $incomingTData2->pluck('ARBPL')->filter()->unique();

            foreach ($existingTData2 as $item) {
                if (!$incomingARBPL->contains($item->ARBPL)) {
                    $item->delete();
                }
            }

            foreach ($incomingTData2 as $row) {
                foreach ($row as $k => $v) {
                    if ($v === '') $row[$k] = null;
                }

                \App\Models\WorkcenterTData2::updateOrCreate(
                    ['ARBPL' => $row['ARBPL']],
                    $row
                );
            }

            // === Sinkronisasi T_DATA3 ===
            $existingTData3 = \App\Models\WorkcenterTData3::all();
            $incomingTData3 = collect($data['T_DATA3'] ?? []);

            $incomingKeys = $incomingTData3
                ->filter(fn($row) => !empty($row['KDAUF']) && !empty($row['KDPOS']))
                ->map(fn($row) => $row['KDAUF'].'_'.$row['KDPOS'])
                ->unique();

            foreach ($existingTData3 as $item) {
                $key = ($item->KDAUF ?? '') . '_' . ($item->KDPOS ?? '');
                if (!$incomingKeys->contains($key)) {
                    $item->delete();
                }
            }

            foreach ($incomingTData3 as $row) {
                foreach ($row as $k => $v) {
                    if ($v === '') $row[$k] = null;
                }

                if (!empty($row['EDATU'])) {
                    try {
                        $row['EDATU'] = \Carbon\Carbon::createFromFormat('Ymd', $row['EDATU'])->format('d-m-Y');
                    } catch (\Exception $e) {
                        $row['EDATU'] = null;
                    }
                }

                \App\Models\WorkcenterTData3::updateOrCreate(
                    ['KDAUF' => $row['KDAUF'], 'KDPOS' => $row['KDPOS']],
                    $row
                );
            }

            // === Ambil data untuk ditampilkan ke hasil view ===
            $t_data1 = \App\Models\WorkcenterTData1::whereIn('ARBPL', $workcenterCodes)->get();
        
            $t_data2 = \App\Models\WorkcenterTData2::whereIn('ARBPL', $workcenterCodes)->get();
            $t_data2 = $t_data2->map(function ($item) use ($t_data1) {
                // Hitung total PRO (termasuk yang tanpa SO)
                $item->jumlah_aufnr = $t_data1->where('ARBPL', $item->ARBPL)
                                            ->pluck('AUFNR')
                                            ->unique()
                                            ->count();
                
                // Hitung PRO tanpa SO
                $item->jumlah_pro_tanpa_so = $t_data1->where('ARBPL', $item->ARBPL)
                                                ->where(function($query) {
                                                    $query->whereNull('KDAUF')
                                                            ->orWhere('KDAUF', '');
                                                })
                                                ->count();
                return $item;
            });

            // Ambil semua T_DATA3 (termasuk yang KDAUF kosong/null)
            $t_data3 = \App\Models\WorkcenterTData3::where(function($query) use ($t_data1) {
                // Untuk yang memiliki SO (KDAUF tidak kosong)
                $query->whereNotNull('KDAUF')
                    ->whereIn('KDAUF', $t_data1->whereNotNull('KDAUF')->pluck('KDAUF')->unique());
                
                // Atau yang tidak memiliki SO (KDAUF kosong/null)
                $query->orWhere(function($q) {
                    $q->whereNull('KDAUF')->orWhere('KDAUF', '');
                });
            })->get();

            return view('workcenter.wc-result', [
                'plant' => $plant,
                'workcenter' => implode(', ', $workcenterCodes),
                't_data1' => $t_data1,
                't_data2' => $t_data2,
                't_data3' => $t_data3,
            ])->with('success', 'Data berhasil disinkronkan dan ditampilkan berdasarkan workcenter yang dipilih.');
            
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
            }
     }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'description' => 'required|string',
            'categories' => 'required|string',
            'plant' => 'required|string',
        ]);

        Workcenters::updateOrCreate(
            ['code' => $request->code], // ← akan cek berdasarkan code
            [
                'description' => $request->description,
                'categories' => $request->categories,
                'plant' => $request->plant,
            ]
        );

        return redirect()->back()->with('success', 'Workcenter saved successfully.');
    }
}
