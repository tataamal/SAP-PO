<!-- result-po.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $subkategori 
                ? ' ' . strtoupper($subkategori) 
                : ' ' . ucfirst($lokasi) . ' ' . $kategori }}
        </h2>

    @php
        $lokasiLower = strtolower($lokasi);
        $kategoriEncoded = rawurlencode($kategori);

        if (!empty($subkategori)) {
            $backUrl = url("/po/menu/{$lokasiLower}/{$kategoriEncoded}");
        } else {
            $backUrl = url("/menu-po/{$lokasiLower}");
        }
    @endphp

    <a href="{{ $backUrl }}">
        <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded">
            ⬅️ BACK
        </button>
    </a>
    </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg" style="max-width: 1400px; margin: 0 auto; width: 95%;">
                
                <!-- Header Controls -->
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <!-- Left: Search and Filters -->
                        <div class="flex items-center search-row-spacing">
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="searchPO" 
                                    placeholder="Search here..." 
                                    class="w-80 pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    @if($purchaseOrders->count() == 0) disabled @endif
                                >
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <label class="text-sm text-gray-600 font-medium">Show:</label>
                                <select id="rowsPerPage" class="border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none bg-white" style="padding-right: 2rem;" @if($purchaseOrders->count() == 0) disabled @endif>
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="all">All</option>
                                </select>
                                <span class="text-sm text-gray-600">entries</span>
                            </div>
                        </div>

                        <!-- Right: Selection Info -->
                        <div id="selectionInfo" class="hidden">
                            <span id="selectedCount" class="text-sm text-gray-600 bg-blue-50 px-3 py-1 rounded-full font-medium">0 selected</span>
                        </div>
                    </div>
                </div>

                <!-- Table Container with proper scrolling -->
                <div class="table-container-wrapper">
                    <div class="table-scroll-container">
                        <table class="compact-table min-w-full divide-y divide-gray-200">
                            <thead class="table-header-navy">
                                <tr>
                                    <th scope="col" class="w-checkbox text-center">
                                        <input type="checkbox" id="masterCheckbox" class="custom-checkbox" @if($purchaseOrders->count() == 0) disabled @endif>
                                    </th>
                                    <th scope="col" class="w-expand text-center">
                                        <button onclick="toggleAllDetails()" 
                                                class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                                            <svg id="master-expand-icon" class="w-5 h-5 transform transition-transform duration-200" 
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </button>
                                    </th>
                                    <th scope="col" class="w-no text-center text-xs font-medium uppercase tracking-wider">No</th>
                                    <th scope="col" class="w-po-number text-left text-xs font-medium uppercase tracking-wider">PO Number</th>
                                    <th scope="col" class="w-doc-date text-left text-xs font-medium uppercase tracking-wider">Doc Date</th>
                                    <th scope="col" class="w-week text-center text-xs font-medium uppercase tracking-wider">Week</th>
                                    <th scope="col" class="w-vendor text-left text-xs font-medium uppercase tracking-wider">Vendor</th>
                                    <th scope="col" class="w-created-by text-left text-xs font-medium uppercase tracking-wider">Created By</th>
                                    <th scope="col" class="w-total text-right text-xs font-medium uppercase tracking-wider">Total</th>
                                    <th scope="col" class="w-currency text-center text-xs font-medium uppercase tracking-wider">Currency</th>
                                </tr>
                            </thead>
                            <tbody id="poTableBody" class="bg-white divide-y divide-gray-200">
                                @if($purchaseOrders->count())
                                    @foreach($purchaseOrders as $index => $po)
                                        <!-- Main Row -->
                                        <tr id="po-row-{{ $index }}" class="po-row po-row-custom transition-colors duration-200" 
                                            data-po="{{ ltrim($po->EBELN, '0') }}" 
                                            data-vendor="{{ strtolower($po->NAME1) }}" 
                                            data-created="{{ strtolower($po->KRYW) }}" 
                                            data-total="{{ $po->TOTPR }}" 
                                            data-currency="{{ $po->WAERK }}"
                                            data-row-index="{{ $index }}">
                                            
                                            <td class="text-center">
                                                <input type="checkbox" 
                                                       name="selected[]" 
                                                       value="{{ $po->EBELN }}" 
                                                       data-frgco="{{ $po->FRGCO ?? '01' }}" 
                                                       data-total="{{ $po->TOTPR }}" 
                                                       data-currency="{{ $po->WAERK }}" 
                                                       class="custom-checkbox po-checkbox">
                                            </td>
                                            
                                            <td class="text-center">
                                                <button onclick="toggleDetail({{ $index }})" 
                                                        class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                                                    <svg id="expand-icon-{{ $index }}" class="w-5 h-5 transform transition-transform duration-200" 
                                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                            
                                            <td class="text-center text-sm text-gray-900 font-medium">
                                                {{ $index + 1 }}
                                            </td>
                                            
                                            <td>
                                                <div class="text-sm font-semibold text-blue-900">{{ ltrim($po->EBELN, '0') }}</div>
                                                @if($po->BSART)
                                                    <div class="text-xs text-gray-500">{{ $po->BSART }}</div>
                                                @endif
                                            </td>
                                            
                                            <td class="text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($po->BEDAT)->format('d/m/Y') }}
                                            </td>
                                            
                                            <td class="text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ (int) $po->WEEK }}
                                                </span>
                                            </td>
                                            
                                            <td class="text-sm text-gray-900">
                                                <div class="font-medium truncate" title="{{ $po->NAME1 }}">{{ $po->NAME1 }}</div>
                                            </td>
                                            
                                            <td class="text-sm text-gray-900">
                                                {{ $po->KRYW }}
                                            </td>
                                            
                                            <td class="text-right text-sm text-gray-900 font-semibold">
                                                {{ $po->WAERK === 'IDR'
                                                    ? 'Rp ' . number_format($po->TOTPR * 100, 0, ',', '.')
                                                    : number_format($po->TOTPR, 2, ',', '.') }}
                                            </td>
                                            
                                            <td class="text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                                                    {{ $po->WAERK === 'IDR' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ $po->WAERK }}
                                                </span>
                                            </td>
                                        </tr>

                                        <!-- Detail Row -->
                                        <tr id="detail-row-{{ $index }}" class="detail-row detail-row-custom hidden" data-row-index="{{ $index }}">
                                            <td colspan="10" class="px-0 py-0">
                                                <div class="ml-16 mr-4 border-l-4 border-blue-400 bg-white mb-4 rounded-r-lg shadow-sm">
                                                    <div class="p-4">
                                                        <div class="overflow-x-auto">
                                                            <table class="min-w-full divide-y divide-gray-200 compact-detail-table">
                                                                <thead class="bg-gray-50">
                                                                    <tr>
                                                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Item</th>
                                                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Qty</th>
                                                                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Unit</th>
                                                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Price</th>
                                                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Amount</th>
                                                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Tax</th>
                                                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Total</th>
                                                                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Currency</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="bg-white divide-y divide-gray-200">
                                                                    @forelse($po->items as $item)
                                                                        <tr class="hover:bg-gray-50">
                                                                            <td class="px-2 py-2 text-sm font-medium text-gray-900 w-16">
                                                                                {{ ltrim($item->EBELP, '0') }}
                                                                            </td>
                                                                            <td class="px-2 py-2 text-sm text-gray-900">
                                                                                {{ $item->MAKTX }}
                                                                            </td>
                                                                            <td class="px-2 py-2 text-sm text-gray-900 text-right w-20">
                                                                                {{ number_format($item->MENGE, 0, ',', '.') }}
                                                                            </td>
                                                                            <td class="px-2 py-2 text-center w-16">
                                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                                                    @if($item->MEINS === 'LE')
                                                                                        AU
                                                                                    @elseif($item->MEINS === 'ST')
                                                                                        PCS
                                                                                    @else
                                                                                        {{ $item->MEINS }}
                                                                                    @endif
                                                                                </span>
                                                                            </td>
                                                                            <td class="px-2 py-2 text-sm text-gray-900 text-right font-medium w-24">
                                                                                {{ $item->WAERK === 'IDR' 
                                                                                    ? number_format($item->NETTT * 100, 0, ',', '.') 
                                                                                    : number_format($item->NETTT, 2, ',', '.') }}
                                                                            </td>
                                                                            <td class="px-2 py-2 text-sm text-gray-900 text-right font-medium w-24">
                                                                                {{ $item->WAERK === 'IDR' 
                                                                                    ? number_format($item->NETWR * 100, 0, ',', '.') 
                                                                                    : number_format($item->NETWR, 2, ',', '.') }}
                                                                            </td>
                                                                            <td class="px-2 py-2 text-sm text-gray-900 text-right w-20">
                                                                                {{ $item->WAERK === 'IDR'
                                                                                    ? number_format($item->TAX * 100, 0, ',', '.')
                                                                                    : number_format($item->TAX, 2, ',', '.') }}
                                                                            </td>
                                                                            <td class="px-2 py-2 text-sm text-gray-900 text-right font-semibold w-24">
                                                                                {{ $item->WAERK === 'IDR' 
                                                                                    ? number_format($item->TOTPR * 100, 0, ',', '.') 
                                                                                    : number_format($item->TOTPR, 2, ',', '.') }}
                                                                            </td>
                                                                            <td class="px-2 py-2 text-center w-16">
                                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                                                    {{ $item->WAERK === 'IDR' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                                                    {{ $item->WAERK }}
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr>
                                                                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                                                                <div class="flex flex-col items-center">
                                                                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                                                    </svg>
                                                                                    <span class="text-sm">No items found for this PO</span>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                        @php
                                                            // Perbaikan: Filter dan gabungkan keterangan yang tidak null/kosong
                                                            $keteranganItems = $po->items->pluck('TEXT')
                                                                ->filter(function($text) {
                                                                    return !is_null($text) && trim($text) !== '';
                                                                })
                                                                ->unique()
                                                                ->values();
                                                            
                                                            $keterangan = $keteranganItems->count() > 0 
                                                                ? $keteranganItems->implode(' | ') 
                                                                : '-'; // Gunakan tanda strip jika kosong
                                                        @endphp

                                                        @if($keterangan)
                                                            <div class="mt-4 p-3 {{ $keterangan === '-' ? 'bg-gray-50 border-gray-200' : 'bg-blue-50 border-blue-200' }} rounded-lg border">
                                                                <h5 class="text-sm font-semibold {{ $keterangan === '-' ? 'text-gray-600' : 'text-blue-900' }} mb-2 flex items-center">
                                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                    </svg>
                                                                    Information
                                                                </h5>
                                                                <p class="text-sm {{ $keterangan === '-' ? 'text-gray-500 italic' : 'text-blue-800' }}">{{ $keterangan }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <!-- Empty State -->
                                    <tr>
                                        <td colspan="10" class="px-6 py-16 text-center">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-24 h-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <h3 class="text-lg font-medium text-gray-900 mb-1">No Purchase Orders Found</h3>
                                                <p class="text-gray-500">
                                                    No data available for 
                                                    <strong>{{ $subkategori ? strtoupper($subkategori) : $kategori }}</strong> 
                                                    in <strong>{{ ucfirst($lokasi) }}</strong>
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination Info -->
                @if($purchaseOrders->count())
                    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <span id="paginationInfo" class="text-sm text-gray-700">
                                Showing <span id="currentStart">0</span> to <span id="currentEnd">0</span> of <span id="totalRows">0</span> entries
                            </span>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <button id="prevPage" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 text-sm" disabled>
                                Previous
                            </button>
                            <div id="pageNumbers" class="flex space-x-1">
                                <!-- Page numbers will be inserted here -->
                            </div>
                            <button id="nextPage" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 text-sm" disabled>
                                Next
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Sticky Action Buttons at Bottom -->
                <div id="actionButtons" class="action-buttons-container hidden">
                    <div class="flex items-center justify-end space-x-3">
                        <!-- Single Action Buttons (untuk 1 item terpilih) -->
                        <div id="singleActions" class="hidden flex space-x-2">
                            <button id="singleReleaseBtn" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg text-sm font-semibold transition duration-200 shadow-lg">
                                ✅ Release
                            </button>
                            <button id="singleRejectBtn" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg text-sm font-semibold transition duration-200 shadow-lg">
                                ❌ Reject
                            </button>
                        </div>

                        <!-- Multiple Action Buttons (untuk >1 item terpilih) -->
                        <div id="multipleActions" class="hidden flex space-x-2">
                            <button id="multipleReleaseBtn" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg text-sm font-semibold transition duration-200 shadow-lg">
                                ✅ Release Selected
                            </button>
                            <button id="multipleRejectBtn" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg text-sm font-semibold transition duration-200 shadow-lg">
                                ❌ Reject Selected
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi -->
    <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4" id="modalTitle">Confirm Action</h3>
            <p class="mb-4 text-gray-600" id="modalMessage">Are you sure?</p>
            <textarea id="rejectReason" name="reject_reason" placeholder="Enter rejection reason..." 
                class="w-full border rounded-lg p-3 mb-4 hidden resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="3"></textarea>
            <div class="flex justify-end space-x-3">
                <button id="btnCancel" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition duration-200">Cancel</button>
                <button id="btnConfirm" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition duration-200">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Release Selected Modal -->
    <div id="releaseSelectedModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6">
            <h3 class="text-lg font-semibold mb-4">Release Selected Purchase Orders</h3>
            <div id="selectedPoList" class="mb-4 max-h-60 overflow-y-auto border rounded-lg p-3">
                <div class="text-gray-500 italic">No POs selected</div>
            </div>
            <div class="text-sm text-gray-600 mb-4 p-3 bg-blue-50 rounded-lg">
                <div class="font-semibold">Summary:</div>
                <div>Total POs: <span id="selectedPoCount">0</span></div>
                <div>Total Value: <span id="totalPoValue">Rp0</span></div>
            </div>
            <div class="flex justify-end space-x-3">
                <button id="btnCancelReleaseSelected" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition duration-200">Cancel</button>
                <button id="btnConfirmReleaseSelected" class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition duration-200">Release All</button>
            </div>
        </div>
    </div>

    <!-- Reject Selected Modal -->
    <div id="rejectSelectedModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6">
            <h3 class="text-lg font-semibold mb-4">Reject Selected Purchase Orders</h3>
            <div id="selectedPoListReject" class="mb-4 max-h-60 overflow-y-auto border rounded-lg p-3">
                <div class="text-gray-500 italic">No POs selected</div>
            </div>
            <div class="text-sm text-gray-600 mb-4 p-3 bg-red-50 rounded-lg">
                <div class="font-semibold">Summary:</div>
                <div>Total POs: <span id="selectedPoCountReject">0</span></div>
                <div>Total Value: <span id="totalPoValueReject">Rp0</span></div>
            </div>
            <textarea id="rejectReasonSelected" name="reject_reason_selected" placeholder="Enter rejection reason for all selected POs..." 
                class="w-full border rounded-lg p-3 mb-4 resize-none focus:ring-2 focus:ring-red-500 focus:border-red-500" rows="3"></textarea>
            <div class="flex justify-end space-x-3">
                <button id="btnCancelRejectSelected" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition duration-200">Cancel</button>
                <button id="btnConfirmRejectSelected" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition duration-200">Reject All</button>
            </div>
        </div>
    </div>

    <style>
        /* New styles for sticky table header */
        .table-container-wrapper {
            position: relative;
            background: white;
        }

        .table-scroll-container {
            max-height: calc(100vh - 380px);
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
        }

        /* Sticky header styles */
        .table-header-navy {
            position: sticky !important;
            top: 0 !important;
            z-index: 20 !important;
            background-color: #1e3a8a !important;
            background-image: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
        }

        /* Remove body scroll when table has scroll */
        body.has-table-scroll {
            overflow: hidden;
        }

        /* Action buttons container improvement */
        .action-buttons-container {
            position: sticky;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-top: 1px solid rgba(229, 231, 235, 0.8);
            z-index: 15;
            padding: 1rem;
            margin-top: 0;
        }

        /* Prevent double scrollbars */
        .main-content {
            overflow: visible !important;
        }

        /* Smooth scrolling */
        .table-scroll-container {
            scroll-behavior: smooth;
        }

        /* Table styles remain the same */
        .compact-table {
            table-layout: fixed;
            width: 100%;
        }
    </style>

    <script>
        // Global variables
        let allRows = [];
        let currentPage = 1;
        let rowsPerPage = 10;
        let filteredRows = [];
        let scrollPosition = 0;
        let allDetailsExpanded = false;
        let expandedDetails = new Set(); // Track expanded details

        // Utility functions for scroll management
        function saveScrollPosition() {
            const scrollContainer = document.querySelector('.table-scroll-container');
            if (scrollContainer) {
                scrollPosition = scrollContainer.scrollTop;
            }
        }

        function restoreScrollPosition(smooth = false) {
            const scrollContainer = document.querySelector('.table-scroll-container');
            if (scrollContainer) {
                if (smooth) {
                    scrollContainer.scrollTo({
                        top: scrollPosition,
                        behavior: 'smooth'
                    });
                } else {
                    scrollContainer.scrollTop = scrollPosition;
                }
            }
        }

        function scrollToElement(element, offset = -100) {
            if (element) {
                const scrollContainer = document.querySelector('.table-scroll-container');
                if (scrollContainer) {
                    const elementPosition = element.offsetTop + offset;
                    scrollContainer.scrollTo({
                        top: elementPosition,
                        behavior: 'smooth'
                    });
                }
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeTable();
            setupEventListeners();
            
            // Fix for select all checkbox functionality
            const masterCheckbox = document.getElementById('masterCheckbox');
            if (masterCheckbox) {
                masterCheckbox.addEventListener('change', function() {
                    toggleAll(this);
                });
            }

            // Prevent body scroll when table has content
            const scrollContainer = document.querySelector('.table-scroll-container');
            if (scrollContainer) {
                document.body.classList.add('has-table-scroll');
            }
        });

        function initializeTable() {
            const tableBody = document.getElementById('poTableBody');
            const rows = tableBody.querySelectorAll('.po-row');
            
            rows.forEach((row, index) => {
                allRows.push({
                    element: row,
                    detailElement: document.getElementById('detail-row-' + index),
                    po: row.dataset.po,
                    vendor: row.dataset.vendor,
                    created: row.dataset.created,
                    total: row.dataset.total,
                    currency: row.dataset.currency,
                    index: index
                });
            });
            
            filteredRows = [...allRows];
            updatePagination();
            updateTableInfo();
        }

        function setupEventListeners() {
            // Search functionality
            document.getElementById('searchPO').addEventListener('input', handleSearch);
            
            // Rows per page functionality
            document.getElementById('rowsPerPage').addEventListener('change', handleRowsPerPageChange);
            
            // Pagination button listeners
            document.getElementById('prevPage').addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    updatePagination();
                }
            });
            
            document.getElementById('nextPage').addEventListener('click', () => {
                const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    updatePagination();
                }
            });
            
            // Checkbox event listeners
            const checkboxes = document.querySelectorAll('.po-checkbox');
            console.log("Found " + checkboxes.length + " row checkboxes");
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateActionButtons);
            });

            // Action button event listeners
            const singleReleaseBtn = document.getElementById('singleReleaseBtn');
            const singleRejectBtn = document.getElementById('singleRejectBtn');
            const multipleReleaseBtn = document.getElementById('multipleReleaseBtn');
            const multipleRejectBtn = document.getElementById('multipleRejectBtn');

            if (singleReleaseBtn) {
                singleReleaseBtn.addEventListener('click', handleSingleRelease);
            }
            if (singleRejectBtn) {
                singleRejectBtn.addEventListener('click', handleSingleReject);
            }
            if (multipleReleaseBtn) {
                multipleReleaseBtn.addEventListener('click', handleMultipleRelease);
            }
            if (multipleRejectBtn) {
                multipleRejectBtn.addEventListener('click', handleMultipleReject);
            }

            // Modal event listeners
            setupModalEventListeners();
        }

        function handleSearch() {
            const searchTerm = this.value.toLowerCase().trim();
            
            if (searchTerm === '') {
                filteredRows = [...allRows];
            } else {
                filteredRows = allRows.filter(row => {
                    return row.po.includes(searchTerm) || 
                           row.vendor.includes(searchTerm) || 
                           row.created.includes(searchTerm);
                });
            }
            
            currentPage = 1;
            updatePagination();
            updateTableInfo();
        }

        function handleRowsPerPageChange() {
            saveScrollPosition();
            const newRowsPerPage = this.value;
            
            if (newRowsPerPage === 'all') {
                rowsPerPage = 'all';
            } else {
                rowsPerPage = parseInt(newRowsPerPage);
            }
            
            currentPage = 1;
            updatePagination();
            updateTableInfo();
            
            setTimeout(() => {
                restoreScrollPosition(true);
            }, 100);
        }

        function updateTableInfo() {
            // Update pagination info di bagian bawah jika elemen ada
            const paginationInfo = document.getElementById('paginationInfo');
            if (paginationInfo) {
                const totalEntries = filteredRows.length;
                
                if (totalEntries === 0) {
                    paginationInfo.textContent = 'Showing 0 to 0 of 0 entries';
                    return;
                }
                
                let startEntry, endEntry;
                
                if (rowsPerPage === 'all') {
                    startEntry = 1;
                    endEntry = totalEntries;
                } else {
                    startEntry = (currentPage - 1) * rowsPerPage + 1;
                    endEntry = Math.min(currentPage * rowsPerPage, totalEntries);
                }
                
                const currentStart = document.getElementById('currentStart');
                const currentEnd = document.getElementById('currentEnd');
                const totalRows = document.getElementById('totalRows');
                
                if (currentStart) currentStart.textContent = startEntry;
                if (currentEnd) currentEnd.textContent = endEntry;
                if (totalRows) totalRows.textContent = totalEntries;
            }
        }

        function updatePagination() {
            // Hide all rows first
            allRows.forEach(row => {
                row.element.style.display = 'none';
                if (row.detailElement) {
                    row.detailElement.style.display = 'none';
                    
                    // Keep detail expanded if it was expanded before
                    if (expandedDetails.has(row.index)) {
                        row.detailElement.classList.remove('hidden');
                        const icon = document.getElementById('expand-icon-' + row.index);
                        if (icon) {
                            icon.classList.add('rotate-90');
                        }
                    } else {
                        row.detailElement.classList.add('hidden');
                        const icon = document.getElementById('expand-icon-' + row.index);
                        if (icon) {
                            icon.classList.remove('rotate-90');
                        }
                    }
                }
            });

            // Update master expand icon based on expanded details
            const masterIcon = document.getElementById('master-expand-icon');
            if (masterIcon) {
                if (expandedDetails.size > 0 && allDetailsExpanded) {
                    masterIcon.classList.add('rotate-90');
                } else {
                    masterIcon.classList.remove('rotate-90');
                    allDetailsExpanded = false;
                }
            }

            // Show filtered rows based on pagination
            let rowsToShow;
            let startIndex = 0;
            
            if (rowsPerPage === 'all') {
                rowsToShow = filteredRows;
            } else {
                startIndex = (currentPage - 1) * rowsPerPage;
                const endIndex = startIndex + rowsPerPage;
                rowsToShow = filteredRows.slice(startIndex, endIndex);
            }

            // Display the rows
            rowsToShow.forEach((row, index) => {
                row.element.style.display = 'table-row';
                
                // Show detail if it was expanded
                if (expandedDetails.has(row.index)) {
                    row.detailElement.style.display = 'table-row';
                }
                
                const numberCell = row.element.querySelector('td:nth-child(3)');
                if (numberCell) {
                    numberCell.textContent = startIndex + index + 1;
                }
            });

            // Update pagination controls
            updatePaginationControls();

            // Reset master checkbox when pagination changes
            const masterCheckbox = document.getElementById('masterCheckbox');
            if (masterCheckbox) {
                masterCheckbox.checked = false;
                masterCheckbox.indeterminate = false;
            }

            updateActionButtons();
            updateTableInfo();
        }

        function updatePaginationControls() {
            if (rowsPerPage === 'all') {
                // Hide pagination controls when showing all
                const prevBtn = document.getElementById('prevPage');
                const nextBtn = document.getElementById('nextPage');
                const pageNumbers = document.getElementById('pageNumbers');
                
                if (prevBtn) prevBtn.style.display = 'none';
                if (nextBtn) nextBtn.style.display = 'none';
                if (pageNumbers) pageNumbers.style.display = 'none';
                return;
            }

            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
            const prevBtn = document.getElementById('prevPage');
            const nextBtn = document.getElementById('nextPage');
            const pageNumbers = document.getElementById('pageNumbers');

            // Show pagination controls
            if (prevBtn) prevBtn.style.display = 'inline-block';
            if (nextBtn) nextBtn.style.display = 'inline-block';
            if (pageNumbers) pageNumbers.style.display = 'flex';

            // Update button states
            if (prevBtn) {
                prevBtn.disabled = currentPage <= 1;
                prevBtn.classList.toggle('opacity-50', currentPage <= 1);
                prevBtn.classList.toggle('cursor-not-allowed', currentPage <= 1);
            }
            
            if (nextBtn) {
                nextBtn.disabled = currentPage >= totalPages;
                nextBtn.classList.toggle('opacity-50', currentPage >= totalPages);
                nextBtn.classList.toggle('cursor-not-allowed', currentPage >= totalPages);
            }

            // Generate page numbers
            if (pageNumbers) {
                pageNumbers.innerHTML = '';
                
                // Show max 5 page numbers
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, startPage + 4);
                
                if (endPage - startPage < 4) {
                    startPage = Math.max(1, endPage - 4);
                }

                for (let i = startPage; i <= endPage; i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.textContent = i;
                    pageBtn.className = `px-3 py-1 border rounded text-sm ${
                        i === currentPage 
                            ? 'bg-blue-500 text-white border-blue-500' 
                            : 'border-gray-300 hover:bg-gray-50'
                    }`;
                    
                    pageBtn.addEventListener('click', () => {
                        currentPage = i;
                        updatePagination();
                    });
                    
                    pageNumbers.appendChild(pageBtn);
                }
            }
        }

        // Toggle all checkboxes - UPDATED FUNCTION
        function toggleAll(source) {
            saveScrollPosition();
            
            // Ambil semua checkbox yang terlihat
            const allVisibleCheckboxes = Array.from(document.querySelectorAll('.po-checkbox')).filter(cb => {
                const row = cb.closest('.po-row');
                return row && row.style.display !== 'none';
            });
            
            // Set semua checkbox sesuai dengan master checkbox
            allVisibleCheckboxes.forEach(cb => {
                cb.checked = source.checked;
            });
            
            updateActionButtons();
            
            setTimeout(() => {
                restoreScrollPosition(true);
            }, 50);
        }

        // Update action buttons based on selection - UPDATED FUNCTION
        function updateActionButtons() {
            // Ambil semua checkbox yang terlihat (tidak hidden)
            const allVisibleCheckboxes = Array.from(document.querySelectorAll('.po-checkbox')).filter(cb => {
                const row = cb.closest('.po-row');
                return row && row.style.display !== 'none';
            });
            
            // Ambil checkbox yang tercentang dan terlihat
            const checkedVisibleCheckboxes = allVisibleCheckboxes.filter(cb => cb.checked);

            // Update master checkbox state
            const masterCheckbox = document.getElementById('masterCheckbox');
            if (masterCheckbox && allVisibleCheckboxes.length > 0) {
                if (checkedVisibleCheckboxes.length === allVisibleCheckboxes.length) {
                    // Semua tercentang
                    masterCheckbox.checked = true;
                    masterCheckbox.indeterminate = false;
                } else if (checkedVisibleCheckboxes.length > 0) {
                    // Sebagian tercentang
                    masterCheckbox.checked = false;
                    masterCheckbox.indeterminate = true;
                } else {
                    // Tidak ada yang tercentang
                    masterCheckbox.checked = false;
                    masterCheckbox.indeterminate = false;
                }
            }

            // Update action buttons
            const actionButtons = document.getElementById('actionButtons');
            const singleActions = document.getElementById('singleActions');
            const multipleActions = document.getElementById('multipleActions');
            const selectionInfo = document.getElementById('selectionInfo');
            const selectedCount = document.getElementById('selectedCount');

            if (checkedVisibleCheckboxes.length === 0) {
                actionButtons.classList.add('hidden');
                selectionInfo.classList.add('hidden');
            } else {
                actionButtons.classList.remove('hidden');
                selectionInfo.classList.remove('hidden');
                selectedCount.textContent = `${checkedVisibleCheckboxes.length} selected`;
                
                if (checkedVisibleCheckboxes.length === 1) {
                    singleActions.classList.remove('hidden');
                    multipleActions.classList.add('hidden');
                } else {
                    singleActions.classList.add('hidden');
                    multipleActions.classList.remove('hidden');
                }
            }
        }

        // Toggle detail rows - Updated to track expanded state
        function toggleDetail(index) {
            saveScrollPosition();
            
            const clickedRow = allRows.find(row => row.index === index);
            const visibleRows = filteredRows.filter(row => row.element.style.display !== 'none');
            
            if (!allDetailsExpanded) {
                visibleRows.forEach(row => {
                    const detailRow = row.detailElement;
                    const icon = document.getElementById('expand-icon-' + row.index);
                    
                    if (detailRow) {
                        detailRow.classList.remove('hidden');
                        detailRow.style.display = 'table-row';
                        expandedDetails.add(row.index);
                    }
                    if (icon) {
                        icon.classList.add('rotate-90');
                    }
                });
                
                const masterIcon = document.getElementById('master-expand-icon');
                if (masterIcon) {
                    masterIcon.classList.add('rotate-90');
                }
                allDetailsExpanded = true;
            } else {
                visibleRows.forEach(row => {
                    const detailRow = row.detailElement;
                    const icon = document.getElementById('expand-icon-' + row.index);
                    
                    if (detailRow) {
                        detailRow.classList.add('hidden');
                        detailRow.style.display = 'none';
                        expandedDetails.delete(row.index);
                    }
                    if (icon) {
                        icon.classList.remove('rotate-90');
                    }
                });
                
                const masterIcon = document.getElementById('master-expand-icon');
                if (masterIcon) {
                    masterIcon.classList.remove('rotate-90');
                }
                allDetailsExpanded = false;
            }
            
            setTimeout(() => {
                if (clickedRow && clickedRow.element) {
                    scrollToElement(clickedRow.element, -20);
                } else {
                    restoreScrollPosition(true);
                }
            }, 50);
        }

        function toggleAllDetails() {
            saveScrollPosition();
            toggleDetail(0);
        }

        // Action handlers
        function handleSingleRelease() {
            saveScrollPosition();
            
            const selectedCheckboxes = Array.from(document.querySelectorAll('.po-checkbox:checked')).filter(cb => {
                const row = cb.closest('.po-row');
                return row && row.style.display !== 'none';
            });

            if (selectedCheckboxes.length === 1) {
                const checkbox = selectedCheckboxes[0];
                const poNumber = ltrim(checkbox.value, '0');
                const frgco = checkbox.dataset.frgco || '01';
                openConfirmModal('release', poNumber, frgco);
            }
        }

        function handleSingleReject() {
            saveScrollPosition();
            
            const selectedCheckboxes = Array.from(document.querySelectorAll('.po-checkbox:checked')).filter(cb => {
                const row = cb.closest('.po-row');
                return row && row.style.display !== 'none';
            });

            if (selectedCheckboxes.length === 1) {
                const checkbox = selectedCheckboxes[0];
                const poNumber = ltrim(checkbox.value, '0');
                openConfirmModal('reject', poNumber);
            }
        }

        function handleMultipleRelease() {
            saveScrollPosition();
            
            const checkboxes = document.querySelectorAll('.po-checkbox:checked');
            const visibleCheckboxes = Array.from(checkboxes).filter(cb => {
                const row = cb.closest('.po-row');
                return row && row.style.display !== 'none';
            });

            if (visibleCheckboxes.length === 0) {
                alert('Please select at least one PO to release');
                return;
            }

            showReleaseModal(visibleCheckboxes);
        }

        function handleMultipleReject() {
            saveScrollPosition();
            
            const checkboxes = document.querySelectorAll('.po-checkbox:checked');
            const visibleCheckboxes = Array.from(checkboxes).filter(cb => {
                const row = cb.closest('.po-row');
                return row && row.style.display !== 'none';
            });

            if (visibleCheckboxes.length === 0) {
                alert('Please select at least one PO to reject');
                return;
            }

            showRejectModal(visibleCheckboxes);
        }

        // Modal functions
        function openConfirmModal(action, ebeln, frgco = null) {
            const modal = document.getElementById('confirmModal');
            const title = document.getElementById('modalTitle');
            const message = document.getElementById('modalMessage');
            const confirmBtn = document.getElementById('btnConfirm');
            const rejectReason = document.getElementById('rejectReason');

            confirmBtn.classList.remove('bg-red-600', 'hover:bg-red-700', 'bg-green-600', 'hover:bg-green-700');

            if (action === 'release') {
                title.textContent = 'Confirm Release PO';
                message.textContent = `Are you sure you want to release PO number ${ebeln}?`;
                rejectReason.classList.add('hidden');
                confirmBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                confirmBtn.textContent = 'Yes, Release';
                confirmBtn.onclick = () => submitAction('release', ebeln, frgco);
            } else if (action === 'reject') {
                title.textContent = 'Confirm Reject PO';
                message.textContent = `Enter rejection reason for PO number ${ebeln}:`;
                rejectReason.classList.remove('hidden');
                rejectReason.value = '';
                confirmBtn.classList.add('bg-red-600', 'hover:bg-red-700');
                confirmBtn.textContent = 'Yes, Reject';
                confirmBtn.onclick = () => submitAction('reject', ebeln, null, rejectReason.value);
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function showReleaseModal(checkboxes) {
            const selectedPoList = document.getElementById('selectedPoList');
            const countSpan = document.getElementById('selectedPoCount');
            const totalValueSpan = document.getElementById('totalPoValue');
            const modal = document.getElementById('releaseSelectedModal');

            selectedPoList.innerHTML = '';
            let totalPerCurrency = {};

            checkboxes.forEach(checkbox => {
                const poNumber = checkbox.value;
                const frgco = checkbox.dataset.frgco || '01';
                const total = parseFloat(checkbox.dataset.total);
                const currency = checkbox.dataset.currency;
                const poDisplay = ltrim(poNumber, '0');
                
                let displayTotal = currency === 'IDR' ? total * 100 : total;
                
                if (!totalPerCurrency[currency]) {
                    totalPerCurrency[currency] = 0;
                }
                totalPerCurrency[currency] += displayTotal;

                const formattedTotal = currency === 'IDR' 
                    ? new Intl.NumberFormat('id-ID').format(displayTotal)
                    : new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(displayTotal);

                const poItem = document.createElement('div');
                poItem.className = 'flex justify-between items-center p-3 border-b border-gray-200 last:border-b-0';
                poItem.innerHTML = `
                    <span class="font-medium">PO: ${poDisplay}</span>
                    <span class="text-gray-600">${currency} ${formattedTotal}</span>
                    <input type="hidden" name="selected_po[]" value="${poNumber}">
                    <input type="hidden" name="frgco[]" value="${frgco}">
                `;
                selectedPoList.appendChild(poItem);
            });

            let totalDisplay = '';
            for (const [currency, amount] of Object.entries(totalPerCurrency)) {
                const formattedAmount = currency === 'IDR' 
                    ? new Intl.NumberFormat('id-ID').format(amount)
                    : new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(amount);
                
                if (totalDisplay) totalDisplay += '<br>';
                totalDisplay += `${currency} ${formattedAmount}`;
            }

            countSpan.textContent = checkboxes.length;
            totalValueSpan.innerHTML = totalDisplay;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function showRejectModal(checkboxes) {
            const selectedPoList = document.getElementById('selectedPoListReject');
            const countSpan = document.getElementById('selectedPoCountReject');
            const totalValueSpan = document.getElementById('totalPoValueReject');
            const modal = document.getElementById('rejectSelectedModal');

            selectedPoList.innerHTML = '';
            let totalPerCurrency = {};

            checkboxes.forEach(checkbox => {
                const poNumber = checkbox.value;
                const total = parseFloat(checkbox.dataset.total);
                const currency = checkbox.dataset.currency;
                const poDisplay = ltrim(poNumber, '0');
                
                let displayTotal = currency === 'IDR' ? total * 100 : total;
                
                if (!totalPerCurrency[currency]) {
                    totalPerCurrency[currency] = 0;
                }
                totalPerCurrency[currency] += displayTotal;

                const formattedTotal = currency === 'IDR' 
                    ? new Intl.NumberFormat('id-ID').format(displayTotal)
                    : new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(displayTotal);

                const poItem = document.createElement('div');
                poItem.className = 'flex justify-between items-center p-3 border-b border-gray-200 last:border-b-0';
                poItem.innerHTML = `
                    <span class="font-medium">PO: ${poDisplay}</span>
                    <span class="text-gray-600">${currency} ${formattedTotal}</span>
                    <input type="hidden" name="selected_po_reject[]" value="${poNumber}">
                `;
                selectedPoList.appendChild(poItem);
            });

            let totalDisplay = '';
            for (const [currency, amount] of Object.entries(totalPerCurrency)) {
                const formattedAmount = currency === 'IDR' 
                    ? new Intl.NumberFormat('id-ID').format(amount)
                    : new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(amount);
                
                if (totalDisplay) totalDisplay += '<br>';
                totalDisplay += `${currency} ${formattedAmount}`;
            }

            countSpan.textContent = checkboxes.length;
            totalValueSpan.innerHTML = totalDisplay;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function setupModalEventListeners() {
            // Cancel buttons
            document.getElementById('btnCancel').addEventListener('click', () => {
                document.getElementById('confirmModal').classList.add('hidden');
                document.getElementById('confirmModal').classList.remove('flex');
                setTimeout(() => restoreScrollPosition(true), 100);
            });

            document.getElementById('btnCancelReleaseSelected').addEventListener('click', () => {
                document.getElementById('releaseSelectedModal').classList.add('hidden');
                document.getElementById('releaseSelectedModal').classList.remove('flex');
                setTimeout(() => restoreScrollPosition(true), 100);
            });

            document.getElementById('btnCancelRejectSelected').addEventListener('click', () => {
                document.getElementById('rejectSelectedModal').classList.add('hidden');
                document.getElementById('rejectSelectedModal').classList.remove('flex');
                setTimeout(() => restoreScrollPosition(true), 100);
            });

            // Confirm buttons
            document.getElementById('btnConfirmReleaseSelected').addEventListener('click', () => {
                const selectedPos = document.querySelectorAll('#selectedPoList input[name="selected_po[]"]');
                
                if (selectedPos.length === 0) {
                    alert('No POs selected');
                    return;
                }

                sessionStorage.setItem('scrollPosition', scrollPosition.toString());
                sessionStorage.setItem('maintainScroll', 'true');
                
                // Save expanded details state
                sessionStorage.setItem('expandedDetails', JSON.stringify(Array.from(expandedDetails)));

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/purchase-order/release';

                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                selectedPos.forEach((poInput, index) => {
                    form.appendChild(poInput.cloneNode());
                    const frgcoInput = document.querySelectorAll('#selectedPoList input[name="frgco[]"]')[index].cloneNode();
                    form.appendChild(frgcoInput);
                });

                document.body.appendChild(form);
                form.submit();
            });

            document.getElementById('btnConfirmRejectSelected').addEventListener('click', () => {
                const selectedPos = document.querySelectorAll('#selectedPoListReject input[name="selected_po_reject[]"]');
                const rejectReason = document.getElementById('rejectReasonSelected').value.trim();
                
                if (selectedPos.length === 0) {
                    alert('No POs selected');
                    return;
                }

                if (!rejectReason) {
                    alert('Rejection reason is required!');
                    return;
                }

                sessionStorage.setItem('scrollPosition', scrollPosition.toString());
                sessionStorage.setItem('maintainScroll', 'true');
                
                // Save expanded details state
                sessionStorage.setItem('expandedDetails', JSON.stringify(Array.from(expandedDetails)));

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/purchase-order/reject-multiple';

                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'reject_reason';
                reasonInput.value = rejectReason;
                form.appendChild(reasonInput);

                selectedPos.forEach((poInput) => {
                    form.appendChild(poInput.cloneNode());
                });

                document.body.appendChild(form);
                form.submit();
            });
        }

        function submitAction(action, ebeln, frgco = null, rejectReason = null) {
            if (action === 'reject' && !rejectReason.trim()) {
                alert('Rejection reason is required!');
                return;
            }

            sessionStorage.setItem('scrollPosition', scrollPosition.toString());
            sessionStorage.setItem('maintainScroll', 'true');
            
            // Save expanded details state
            sessionStorage.setItem('expandedDetails', JSON.stringify(Array.from(expandedDetails)));

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = action === 'release' ? '/purchase-order/release' : '/purchase-order/reject';

            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            const ebelnInput = document.createElement('input');
            ebelnInput.type = 'hidden';
            ebelnInput.name = 'ebeln';
            ebelnInput.value = ebeln;
            form.appendChild(ebelnInput);

            if (action === 'release' && frgco) {
                const frgcoInput = document.createElement('input');
                frgcoInput.type = 'hidden';
                frgcoInput.name = 'frgco';
                frgcoInput.value = frgco;
                form.appendChild(frgcoInput);
            } else if (action === 'reject' && rejectReason) {
                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'reject_reason';
                reasonInput.value = rejectReason;
                form.appendChild(reasonInput);
            }

            document.body.appendChild(form);
            form.submit();
        }

        function ltrim(str, chars) {
            return str.replace(new RegExp(`^[${chars}]+`), '');
        }

        // Restore expanded details state on page load
        window.addEventListener('load', function() {
            const savedExpandedDetails = sessionStorage.getItem('expandedDetails');
            if (savedExpandedDetails) {
                try {
                    const expanded = JSON.parse(savedExpandedDetails);
                    expanded.forEach(index => {
                        expandedDetails.add(index);
                    });
                    sessionStorage.removeItem('expandedDetails');
                } catch (e) {
                    console.error('Error restoring expanded details:', e);
                }
            }

            // Restore scroll position if needed
            const savedScrollPosition = sessionStorage.getItem('scrollPosition');
            if (savedScrollPosition && sessionStorage.getItem('maintainScroll') === 'true') {
                scrollPosition = parseInt(savedScrollPosition);
                setTimeout(() => {
                    restoreScrollPosition(false);
                    sessionStorage.removeItem('scrollPosition');
                    sessionStorage.removeItem('maintainScroll');
                }, 100);
            }
        });

        // Make functions globally accessible
        window.toggleAll = toggleAll;
        window.toggleDetail = toggleDetail;
        window.toggleAllDetails = toggleAllDetails;
        window.updateActionButtons = updateActionButtons;
    </script>

    @if(session('success'))
    <script>
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: "{{ session('error') }}",
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
        });
    </script>
    @endif
</x-app-layout>