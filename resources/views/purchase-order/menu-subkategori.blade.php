<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $title }}
        </h2>
        <a href="{{ url('/menu-po/' . strtolower($lokasi)) }}">
            <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded">
                ⬅️BACK
            </button>
        </a>
        </div>

    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(count($subkategoriList) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    @foreach ($subkategoriList as $sub)
                        @php
                            // Get BSART values for this sub-category
                            $bsarts = DB::table('purchase_order_mapping')
                                ->where('LOC', $lokasi)
                                ->where('CATEGORY', $kategori)
                                ->where('SUB_CATEGORY', $sub)
                                ->pluck('BSART')
                                ->unique()
                                ->toArray();
                            
                            // Count POs for this sub-category
                            $poCount = \App\Models\PurchaseOrderData1::where('LOC', $lokasi)
                                ->whereIn('BSART', $bsarts)
                                ->count();
                        @endphp
                        
                        <a href="{{ route('po.result', ['lokasi' => $lokasi, 'kategori' => $kategori, 'subkategori' => $sub]) }}"
                        class="block bg-white hover:bg-blue-50 border border-gray-200 rounded-lg shadow px-6 py-6 transition duration-200">
                            <div>
                                <div class="text-xl font-semibold text-gray-800">{{ strtoupper($sub) }}</div>
                                <div class="text-sm text-gray-500 mt-1">
                                    Total PO:
                                    <span class="text-xl font-bold text-blue-600">{{ $poCount }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                @php
                    // Get BSART values for the main category when no sub-categories exist
                    $bsarts = DB::table('purchase_order_mapping')
                        ->where('LOC', $lokasi)
                        ->where('CATEGORY', $kategori)
                        ->pluck('BSART')
                        ->unique()
                        ->toArray();
                    
                    $poCount = \App\Models\PurchaseOrderData1::where('LOC', $lokasi)
                        ->whereIn('BSART', $bsarts)
                        ->count();
                @endphp
                
                <div class="bg-white p-6 rounded-lg shadow text-center">
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Tidak ada Sub Kategori</h3>
                    <p class="text-gray-500 mb-4">
                        Menampilkan langsung PO berdasarkan kategori <strong>{{ strtoupper($kategori) }}</strong>.
                    </p>
                        <div class="mb-4 text-md font-semibold">
                            Total: <span class="bg-blue-200 text-blue-900 px-4 py-1 rounded-full">{{ $poCount }} PO</span>
                        </div>
                    <a href="{{ route('po.result', ['lokasi' => $lokasi, 'kategori' => $kategori]) }}"
                       class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Lihat PO
                    </a>
                </div>
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