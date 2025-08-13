<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\PurchaseOrderData1;
use App\Models\PurchaseOrderData2;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class PurchaseOrderController extends Controller
{

public function release(Request $request)
{
    // Handle both single and multiple PO releases
    $ebeln = $request->input('ebeln'); // For single PO
    $ebelnList = $request->input('selected_po', []); // For multiple POs
    $frgco = $request->input('frgco'); // For single PO
    $frgcoList = $request->input('frgco', []); // For multiple POs

    // Convert single PO case to array format for uniform processing
    if ($ebeln && !empty($ebeln)) {
        $ebelnList = [$ebeln];
        $frgcoList = [$frgco];
    }

    if (empty($ebelnList)) {
        return back()->with('error', 'Pilih setidaknya satu PO untuk di-release.');
    }

    if (!session()->has('sap_user') || !session()->has('sap_pass')) {
        return redirect()->route('sap.login')->withErrors('Silakan login ke SAP terlebih dahulu.');
    }

    $successCount = 0;
    $errorMessages = [];

    foreach ($ebelnList as $index => $ebeln) {
        try {
            $ebelnPadded = str_pad($ebeln, 10, '0', STR_PAD_LEFT);
            $currentFrgco = $frgcoList[$index] ?? '01'; // Default to '01' if not specified

            $response = Http::withHeaders([
                'X-SAP-Username' => session('sap_user'),
                'X-SAP-Password' => session('sap_pass'),
            ])->post('http://127.0.0.1:8006/api/z_po_release2', [
                'EBELN' => $ebelnPadded,
                'REL_CODE' => $currentFrgco, // Send as string
            ]);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? '') === 'success') {
                DB::transaction(function () use ($ebelnPadded) {
                    DB::table('purchase_orders_data2')->where('EBELN', $ebelnPadded)->delete();
                    DB::table('purchase_orders_data1')->where('EBELN', $ebelnPadded)->delete();
                });
                $successCount++;
            } else {
                $msg = $data['message'] ?? $response->body();
                $errorMessages[] = 'PO ' . ltrim($ebeln, '0') . ': ' . $msg;
            }
        } catch (\Exception $e) {
            $errorMessages[] = 'PO ' . ltrim($ebeln, '0') . ': ' . $e->getMessage();
        }
    }

    if (!empty($errorMessages)) {
        $message = ($successCount > 0 ? 'Beberapa PO berhasil di-release. ' : '') . 
                  'Error: ' . implode('; ', $errorMessages);
        return back()->with($successCount > 0 ? 'warning' : 'error', $message);
    }

    return back()->with('success', 
        count($ebelnList) > 1 
            ? "Semua {$successCount} PO berhasil di-release." 
            : "PO berhasil di-release."
    );
}

public function reject(Request $request)
{
    $ebeln = $request->input('ebeln');
    $rejectReason = $request->input('reject_reason');

    if (!$ebeln || !$rejectReason) {
        return back()->with('error', 'Nomor PO dan alasan reject wajib diisi.');
    }

    if (!session()->has('sap_user') || !session()->has('sap_pass')) {
        return redirect()->route('sap.login')->withErrors('Silakan login ke SAP terlebih dahulu.');
    }

    try {
        $ebelnPadded = str_pad($ebeln, 10, '0', STR_PAD_LEFT);

        // Update komentar dulu ke SAP
        $commentResponse = Http::withHeaders([
            'X-SAP-Username' => session('sap_user'),
            'X-SAP-Password' => session('sap_pass'),
        ])->post('http://127.0.0.1:8006/api/z_po_comment_update', [
            'PURCHASEORDER' => $ebelnPadded,
            'COMMENT_TEXT' => $rejectReason,
        ]);

        if (!$commentResponse->successful()) {
            return back()->with('error', 'Gagal mengupdate komentar reject.');
        }

        // Lanjut reject PO
        $rejectResponse = Http::withHeaders([
            'X-SAP-Username' => session('sap_user'),
            'X-SAP-Password' => session('sap_pass'),
        ])->post('http://127.0.0.1:8006/api/reject_po', [
            'EBELN' => $ebelnPadded,
        ]);

        if (!$rejectResponse->successful()) {
            return back()->with('error', 'Gagal melakukan reject PO.');
        }

        // Hapus data dari tabel terkait setelah berhasil reject
        DB::transaction(function () use ($ebelnPadded) {
            DB::table('purchase_orders_data1')->where('EBELN', $ebelnPadded)->delete();
            DB::table('purchase_orders_data2')->where('EBELN', $ebelnPadded)->delete();
        });

        // Kembalikan response sukses
        return back()->with('success', 'PO ' . ltrim($ebeln, '0') . ' berhasil direject dengan alasan: ' . $rejectReason);
    } catch (\Exception $e) {
        return back()->with('error', 'Error reject PO: ' . $e->getMessage());
    }
}


private function sendFonnteMessage($phone, $message)
{
    $data = [
        [
            "target" => $phone,
            "message" => $message,
            "delay" => "0"
        ]
    ];

    $response = Http::withHeaders([
        'Authorization' => env('FONNTE_TOKEN'), // Token dari .env
    ])->asForm()->post('http://api.fonnte.com/send', [
        'data' => json_encode($data)
    ]);

    Log::info('Fonnte WA Response', [
        'message' => $message,
        'response' => $response->json(),
    ]);
}

public function index()
{
    $totalSemarang = $this->getTotalPoByPlant('1300');
    $totalSurabaya = $this->getTotalPoByPlant('1200');

    $kategoriList = DB::table('purchase_order_mapping')
        ->select('CATEGORY')
        ->distinct()
        ->get();

    return view('purchase-order', [
        'totalSemarang' => $totalSemarang,
        'totalSurabaya' => $totalSurabaya,
        'kategoriList' => $kategoriList,
        'title' => 'Menu Purchase Order',
    ]);
}

private function getTotalPoByPlant($plant)
{
    ini_set('max_execution_time', 900);
    if (!session()->has('sap_user') || !session()->has('sap_pass')) return 0;

    $response = Http::timeout(0)->withHeaders([
        'X-SAP-Username' => session('sap_user'),
        'X-SAP-Password' => session('sap_pass'),
    ])->post('http://127.0.0.1:8006/api/sap-po', [
        'plants' => [$plant]
    ]);

    if ($response->failed()) return 0;

    return count($response->json('T_DATA1') ?? []);
}

public function fetchBothPlants()
{
    if (!session()->has('sap_user') || !session()->has('sap_pass')) {
        return redirect()->route('sap.login')->withErrors('Silakan login ke SAP terlebih dahulu.');
   }

   // Truncate tabel sebelum mulai fetch data baru
   PurchaseOrderData1::truncate();
   PurchaseOrderData2::truncate();

   $plantMap = [
       'semarang' => '1300',
       'surabaya' => '1200',
   ];

   $totals = [];

   foreach ($plantMap as $lokasi => $plantCode) {
       try {
           $response = Http::timeout(0)->withHeaders([
               'X-SAP-Username' => session('sap_user'),
               'X-SAP-Password' => session('sap_pass'),
           ])->post('http://127.0.0.1:8006/api/sap-po', [
               'plants' => [$plantCode]
           ]);

           if ($response->successful()) {
               $tData1 = $response->json('T_DATA1') ?? [];
               $tData2 = $response->json('T_DATA2') ?? [];

               $this->savePoData($tData1, $tData2, $lokasi);

               $totals[$lokasi] = count($tData1);
           } else {
               $totals[$lokasi] = 0;
           }
       } catch (\Exception $e) {
           Log::error("Fetch data SAP gagal untuk lokasi $lokasi: " . $e->getMessage());
           $totals[$lokasi] = 0;
       }
   }

   return view('purchase-order', [
       'totalSemarang' => $totals['semarang'] ?? 0,
       'totalSurabaya' => $totals['surabaya'] ?? 0,
       'title' => 'Dashboard PO (Fetch SAP)',
   ]);
}

private function savePoData(array $tData1, array $tData2, string $lokasi)
{
   // === Simpan T_DATA1 ===
   foreach ($tData1 as $item) {
       if (!isset($item['EBELN']) || !is_scalar($item['EBELN'])) {
           Log::warning("SKIP: EBELN tidak valid di T_DATA1", ['item' => $item]);
           continue;
       }

       $ebeln = strval($item['EBELN']);

       try {
           PurchaseOrderData1::updateOrCreate(
               ['EBELN' => $ebeln],
               [
                   'BADAT' => $this->formatDateSAP($item['BADAT'] ?? null),
                   'BEDAT' => $this->formatDateSAP($item['BEDAT'] ?? null),
                   'FRGCO' => $item['FRGCO'] ?? null,
                   'KRYW'  => $item['KRYW'] ?? null,
                   'NAME1' => $item['NAME1'] ?? null,
                   'STATS' => $item['STATS'] ?? null,
                   'TOTPR' => $item['TOTPR'] ?? null,
                   'WAERK' => $item['WAERK'] ?? null,
                   'WEEK'  => $item['WEEK'] ?? null,
                   'BSART' => $item['BSART'] ?? null,
                   'LOC'   => strtolower($lokasi),
               ]
           );
       } catch (\Exception $e) {
           Log::error("GAGAL simpan T_DATA1 $ebeln: " . $e->getMessage(), ['item' => $item]);
       }
   }

   // === Logging Contoh T_DATA2 ===
   Log::info("[$lokasi] Jumlah data T_DATA2: " . count($tData2));
   foreach (array_slice($tData2, 0, 5) as $index => $row) {
       Log::info("[$lokasi] Contoh T_DATA2 [$index]:", $row);
   }

   // === Simpan T_DATA2 ===
   foreach ($tData2 as $item) {
       if (!isset($item['EBELN']) || !is_scalar($item['EBELN']) || !isset($item['EBELP']) || !is_scalar($item['EBELP'])) {
           Log::warning("SKIP: EBELN/EBELP tidak valid di T_DATA2", ['item' => $item]);
           continue;
       }

       $ebeln = strval($item['EBELN']);
       $ebelp = strval($item['EBELP']);

       try {
           // VALIDASI TEXT YANG KONSISTEN UNTUK SEMUA JENIS PO (GOODS & SERVICE)
           $textValue = null;
           if (isset($item['TEXT']) && !empty(trim($item['TEXT']))) {
               $cleanText = trim($item['TEXT']);
               
               // HAPUS TEXT YANG MENGANDUNG DEFAULT MESSAGE (case-insensitive)
               $defaultMessages = [
                   'tidak ada keterangan',
                   'no description',
                   'no information',
                   'tidak ada keterangan untuk po',
                   'no keterangan',
                   'kosong'
               ];
               
               $isDefaultMessage = false;
               foreach ($defaultMessages as $defaultMsg) {
                   if (stripos($cleanText, $defaultMsg) !== false) {
                       $isDefaultMessage = true;
                       break;
                   }
               }
               
               // Jika bukan default message, simpan. Jika default message, set null
               if (!$isDefaultMessage) {
                   $textValue = $cleanText;
               }
           }

           PurchaseOrderData2::updateOrCreate(
               ['EBELN' => $ebeln, 'EBELP' => $ebelp],
               [
                   'MATNR' => $item['MATNR'] ?? null,
                   'MAKTX' => $item['MAKTX'] ?? null,
                   'MENGE' => $item['MENGE'] ?? null,
                   'MEINS' => $item['MEINS'] ?? null,
                   'NETWR' => $item['NETWR'] ?? null,
                   'NETTT' => $item['NETTT'] ?? null,
                   'TAX'   => $item['TAX'] ?? null,
                   'TOTPR' => $item['TOTPR'] ?? null,
                   'WAERK' => $item['WAERK'] ?? null,
                   'TEXT'  => $textValue, // Konsisten: null jika kosong atau default message
                   'KRYW'  => $item['KRYW'] ?? null,
                   'NAME1' => $item['NAME1'] ?? null,
                   'FRGCO' => $item['FRGCO'] ?? null,
                   'STATS' => $item['STATS'] ?? null,
                   'BADAT' => $this->formatDateSAP($item['BADAT'] ?? null),
                   'BEDAT' => $this->formatDateSAP($item['BEDAT'] ?? null),
                   'WEEK'  => $item['WEEK'] ?? null,
                   'MANDT' => $item['MANDT'] ?? '',
               ]
           );
           Log::info("BERHASIL: Simpan T_DATA2 $ebeln-$ebelp dengan TEXT: " . ($textValue ?? 'NULL'));
       } catch (\Exception $e) {
           Log::error("GAGAL simpan T_DATA2 $ebeln-$ebelp: " . $e->getMessage(), ['item' => $item]);
       }
   }
}

// Method untuk membersihkan data lama yang mengandung default message
public function cleanOldTextData()
{
   try {
       // Update semua TEXT yang mengandung default message menjadi NULL
       $defaultMessages = [
           '%Tidak Ada Keterangan%',
           '%tidak ada keterangan%',
           '%No Description%',
           '%no description%',
           '%No Information%',
           '%no information%',
           '%Tidak Ada Keterangan Untuk PO%',
           '%tidak ada keterangan untuk po%'
       ];

       $totalUpdated = 0;
       foreach ($defaultMessages as $pattern) {
           $updated = DB::table('purchase_orders_data2')
               ->where('TEXT', 'LIKE', $pattern)
               ->update(['TEXT' => null]);
           $totalUpdated += $updated;
       }

       Log::info("Cleaned $totalUpdated rows with default text messages");
       
       return response()->json([
           'success' => true,
           'updated_rows' => $totalUpdated,
           'message' => "Successfully cleaned $totalUpdated rows containing default messages"
       ]);
   } catch (\Exception $e) {
       Log::error("Error cleaning text data: " . $e->getMessage());
       return response()->json([
           'success' => false,
           'error' => $e->getMessage()
       ], 500);
   }
}

private function formatDateSAP($date)
{
   try {
       if (preg_match('/^\d{8}$/', $date)) {
           return Carbon::createFromFormat('Ymd', $date)->format('Y-m-d');
       }

       if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date)) {
           return Carbon::createFromFormat('d.m.Y', $date)->format('Y-m-d');
       }

       return null;
   } catch (\Exception $e) {
       return null;
   }
}

public function show(Request $request)
{
   $lokasi = $request->input('lokasi');
   $kategori = $request->input('kategori');
   $subkategori = $request->input('subkategori'); // Tambahan

   if (!$lokasi || !$kategori) {
       return back()->with('error', 'Parameter lokasi dan kategori wajib diisi.');
   }

   // Cek apakah ada subkategori
   if ($subkategori) {
       $bsartList = DB::table('purchase_order_mapping')
           ->where('LOC', $lokasi)
           ->where('CATEGORY', $kategori)
           ->where('SUB_CATEGORY', $subkategori)
           ->pluck('BSART')
           ->unique()
           ->toArray();

       $title = 'Subkategori: ' . strtoupper($subkategori);
   } else {
       $bsartList = DB::table('purchase_order_mapping')
           ->where('LOC', $lokasi)
           ->where('CATEGORY', $kategori)
           ->pluck('BSART')
           ->unique()
           ->toArray();

       $title = 'Kategori: ' . strtoupper($kategori);
   }

   $purchaseOrders = PurchaseOrderData1::with('items')
       ->where('LOC', $lokasi)
       ->whereIn('BSART', $bsartList)
       ->orderByDesc('BEDAT')
       ->paginate(1000);

   return view('purchase-order.result-po', [
       'purchaseOrders' => $purchaseOrders,
       'lokasi' => $lokasi,
       'kategori' => $kategori,
       'subkategori' => $subkategori,
       'title' => $title,
   ]);
}


public function menuPo($lokasi)
{
$kategoriList = DB::table('purchase_order_mapping')
   ->where('LOC', $lokasi)
   ->select('CATEGORY', DB::raw('MAX(CASE WHEN SUB_CATEGORY IS NOT NULL THEN 1 ELSE 0 END) as has_sub'))
   ->groupBy('CATEGORY')
   ->get();


   return view('purchase-order.menu-po', [
       'lokasi' => $lokasi,
       'kategoriList' => $kategoriList,
       'title' => ' ' . strtoupper($lokasi),
   ]);
}

public function menuSubKategori($lokasi, $kategori)
{
   $subkategoriList = DB::table('purchase_order_mapping')
   ->where('LOC', $lokasi)
   ->where('CATEGORY', $kategori)
   ->whereNotNull('SUB_CATEGORY')
   ->where('SUB_CATEGORY', '!=', '')
   ->distinct()
   ->pluck('SUB_CATEGORY');

   if ($subkategoriList->isEmpty()) {
       return redirect()->route('po.result', [
           'lokasi' => $lokasi,
           'kategori' => $kategori,
       ]);
   }

   return view('purchase-order.menu-subkategori', [
       'lokasi' => $lokasi,
       'kategori' => $kategori,
       'subkategoriList' => $subkategoriList,
       'title' => ' ' . strtoupper($kategori),
   ]);
}

}