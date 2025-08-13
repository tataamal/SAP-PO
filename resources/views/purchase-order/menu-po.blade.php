<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $title }}
        </h2>
        <a href="{{ url('/purchase-order/fetch') }}">
            <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded">
                ⬅️BACK
            </button>
        </a>
    </div>
    </x-slot>

    <div class="py-12">
        <!-- UBAH max-w-6xl ke max-w-7xl dan tambahkan px-4 -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @php
                $hasSubKategori = $kategoriList->where('has_sub', 1)->count() > 0;
            @endphp

            @if($hasSubKategori)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    @foreach($kategoriList as $kategori)
                        @if($kategori->has_sub)
                            @php
                                $bsarts = DB::table('purchase_order_mapping')
                                    ->where('LOC', $lokasi)
                                    ->where('CATEGORY', $kategori->CATEGORY)
                                    ->pluck('BSART')
                                    ->unique()
                                    ->toArray();
                                
                                $poCount = \App\Models\PurchaseOrderData1::where('LOC', $lokasi)
                                    ->whereIn('BSART', $bsarts)
                                    ->count();
                            @endphp

                            <!-- UBAH class jadi sama seperti di purchase-order -->
                            <a href="{{ route('po.subkategori', ['lokasi' => $lokasi, 'kategori' => $kategori->CATEGORY]) }}"
                               class="flex items-center justify-between bg-white hover:bg-blue-50 border border-gray-200 rounded-lg shadow px-6 py-6 transition duration-200">
                                <div>
                                    <div class="text-xl font-semibold text-gray-800">
                                        {{ $kategori->CATEGORY }}
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1">
                                        Total PO:
                                        <span class="text-xl font-bold text-blue-600">{{ $poCount }}</span>
                                    </div>
                                </div>

                                {{-- Optional image placeholder jika ingin gambar --}}
                                {{-- 
                                <div class="w-[88px] h-[88px] ml-4 flex-shrink-0">
                                    <img src="{{ asset('images/default.png') }}" class="w-full h-full object-contain">
                                </div> 
                                --}}
                            </a>
                        @endif
                    @endforeach
                </div>
            @else
                {{-- Jika tidak ada subkategori --}}
                @foreach($kategoriList as $kategori)
                    @php
                        $bsarts = DB::table('purchase_order_mapping')
                            ->where('LOC', $lokasi)
                            ->where('CATEGORY', $kategori->CATEGORY)
                            ->pluck('BSART')
                            ->unique()
                            ->toArray();

                        $purchaseOrders = \App\Models\PurchaseOrderData1::where('LOC', $lokasi)
                            ->whereIn('BSART', $bsarts)
                            ->orderByDesc('BEDAT')
                            ->get();
                            
                        $poCount = $purchaseOrders->count();
                    @endphp

                    <div class="bg-white shadow rounded-lg p-4 mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-semibold text-gray-700">
                                Data PO: {{ $kategori->CATEGORY }}
                            </h3>
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                {{ $poCount }} PO
                            </span>
                        </div>
                        <table class="min-w-full table-auto text-sm text-left border">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="px-4 py-2 border">EBELN</th>
                                    <th class="px-4 py-2 border">Vendor</th>
                                    <th class="px-4 py-2 border">Tanggal</th>
                                    <th class="px-4 py-2 border">Total</th>
                                    <th class="px-4 py-2 border">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchaseOrders as $po)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 border font-mono text-blue-800">{{ ltrim($po->EBELN, '0') }}</td>
                                        <td class="px-4 py-2 border">{{ $po->NAME1 }}</td>
                                        <td class="px-4 py-2 border">{{ $po->BEDAT }}</td>
                                        <td class="px-4 py-2 border">{{ number_format($po->TOTPR, 0, ',', '.') }} {{ $po->WAERK }}</td>
                                        <td class="px-4 py-2 border">{{ $po->STATS }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-2 border text-center text-gray-500">Tidak ada data PO ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @endif

        </div>
    </div>

    <script>
        // Function untuk tombol Back
        function goBack() {
            window.history.back();
        }
    </script>
</x-app-layout>