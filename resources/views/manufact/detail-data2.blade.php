<!-- detail-data2.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                DATA DETAIL: Plant {{$plant}} {{$categories}}{{ $bagian }}
            </h2>
            <a href="{{ route('manufact.detail', ['plant' => $plant, 'category' => $categories]) }}"
               class="inline-block px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                ← BACK
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Outstanding Order</h3>
                    <form method="GET" class="flex items-center">
                        <input type="hidden" name="lokasi" value="{{ request('lokasi') }}">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                            class="border rounded px-2 py-1 text-sm w-48" id="searchInput">
                    </form>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto text-sm text-left whitespace-nowrap border">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">No.</th>
                                <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">ORDER</th>
                                <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">ITEM</th>
                                <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">MATERIAL FG</th>
                                <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">DESCRIPTION MATERIAL</th>
                                <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">PO DATE</th>
                                <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">TOTAL PLO</th>
                                <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">PRO (CRTD)</th>
                                <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">PRO (Released)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($details as $index => $item)
                                @php
                                    $kdauf = $item->KDAUF;
                                    $kdpos = $item->KDPOS;
                                    $formattedKDPOS = preg_replace('/^0{1,2}/', '', $kdpos);
                                    $key = $kdauf . '-' . $kdpos;
                                    
                                    // Count PRO by status and PLO for this SO item
                                    $proCountCRTD = 0;
                                    $proCountReleased = 0;
                                    $ploCount = 0;
                                    
                                    if (isset($allTData3[$key])) {
                                        foreach ($allTData3[$key] as $tdata3) {
                                            if (!empty($tdata3['AUFNR'])) {
                                                if ($tdata3['STATS'] === 'CRTD') {
                                                    $proCountCRTD++;
                                                } elseif (in_array($tdata3['STATS'], ['PCNF', 'REL', 'CNF REL'])) {
                                                    $proCountReleased++;
                                                }
                                            }
                                            if (!empty($tdata3['PLNUM'])) $ploCount++;
                                        }
                                    }
                                @endphp
                                <tr class="hover:bg-blue-50 cursor-pointer" onclick="showTData3('{{ $key }}', this)">
                                    <td class="px-2 py-1 border text-center">{{ $index + 1 }}</td>
                                    <td class="px-2 py-1 border">{{ $kdauf }}</td>
                                    <td class="px-2 py-1 border">{{ $formattedKDPOS }}</td>
                                    <td class="px-2 py-1 border">{{ Str::contains($item->MATFG, '.') ? $item->MATFG : ltrim($item->MATFG, '0') }}</td>
                                    <td class="px-2 py-1 border">{{ $item->MAKFG }}</td>
                                    <td class="px-2 py-1 border">
                                        {{ \Carbon\Carbon::parse($item->EDATU)->format('d-m-Y') }}
                                    </td>
                                    <td class="px-2 py-1 border text-center">{{ $ploCount }}</td>
                                    <td class="px-2 py-1 border text-center">{{ $proCountCRTD }}</td>
                                    <td class="px-2 py-1 border text-center">{{ $proCountReleased }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-3 py-2 text-center text-gray-500 border">Tidak ada data ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Tabel T_DATA3 yang terpisah -->
                <div id="tdata3-container" class="mt-8 hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Order Overview</h3>
                        
                        <!-- Filter Buttons -->
                        <div class="flex items-center gap-2">
                            <div class="flex bg-gray-200 rounded-lg p-1" id="status-filter">
                                <button id="filter-all" 
                                        class="px-3 py-1 rounded text-sm font-medium bg-blue-600 text-white transition-colors"
                                        onclick="filterByStatus('all')">
                                    All
                                </button>
                                <button id="filter-plo" 
                                        class="px-3 py-1 rounded text-sm font-medium text-gray-700 hover:bg-gray-300 transition-colors"
                                        onclick="filterByStatus('plo')">
                                    PLO
                                </button>
                                <button id="filter-crtd" 
                                        class="px-3 py-1 rounded text-sm font-medium text-gray-700 hover:bg-gray-300 transition-colors"
                                        onclick="filterByStatus('crtd')">
                                    PRO (CRTD)
                                </button>
                                <button id="filter-released" 
                                        class="px-3 py-1 rounded text-sm font-medium text-gray-700 hover:bg-gray-300 transition-colors"
                                        onclick="filterByStatus('released')">
                                    PRO (Released)
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bulk Action Controls -->
                    <div id="bulk-controls" class="flex items-center gap-2 mb-4 hidden">
                        <button id="bulk-convert-btn" 
                                class="bg-orange-600 text-white px-4 py-2 rounded text-sm hover:bg-orange-700 hidden"
                                onclick="bulkConvertPlannedOrders()">
                            Convert Selected PLO
                        </button>
                        <button id="bulk-release-btn" 
                                class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700 hidden"
                                onclick="bulkReleaseOrders()">
                            Release Selected PRO
                        </button>
                        <button class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600"
                                onclick="clearAllSelections()">
                            Clear All
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table id="tdata3-table" class="min-w-full table-auto text-sm text-left whitespace-nowrap border">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">
                                        <input type="checkbox" id="select-all" onchange="toggleSelectAll()" class="mr-1">
                                        Select
                                    </th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">No.</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">PLO</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">PRO</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">STATUS</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">ACTION</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">MRP</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">MATERIAL</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">DESCRIPTION</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">QTY ORDER</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">QTY GR</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">OUTS GR</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">BASIC START DATE</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">BASIC FINISH DATE</th>
                                </tr>
                            </thead>
                            <tbody id="tdata3-body">
                                <!-- Data akan diisi oleh JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Container untuk T_DATA1 dan T_DATA4 -->
                <div id="additional-data-container" class="mt-4">
                    <!-- Data akan diisi oleh JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Existing Modals -->
    <div id="modal-edit-wc" class="hidden fixed inset-0 z-50 items-center justify-center p-4 bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Edit Work Center</h2>
                    <button onclick="closeModalEditWC()" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form id="edit-wc-form" method="POST" action="{{ route('sap.save_edit') }}">
                    @csrf
                    <input type="hidden" id="edit-wc-aufnr" name="aufnr">
                    <input type="hidden" id="edit-wc-operation" name="operation">
                    
                    <div class="mb-6">
                        <label for="edit-wc-work-center" class="block text-sm font-medium text-gray-700 mb-2">Work Center</label>
                        <input type="text" id="edit-wc-work-center" name="work_center"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter work center">
                    </div>

                    <div class="flex justify-between pt-4 border-t border-gray-200 flex-wrap">
                        <button type="button" onclick="closeModalEditWC()"
                                class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-change-pv" class="fixed inset-0 z-50 items-center justify-center p-4 bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Change PV</h2>
                    <button onclick="closeModalChangePV()" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form id="form-change-pv" method="POST" action="{{ route('sap.save_edit_pv') }}">
                    @csrf
                    <input type="hidden" id="change-pv-aufnr" name="aufnr">
                    <input type="hidden" id="change-pv-current" name="current_pv">
                    
                    <div class="mb-4">
                        <label for="new-pv" class="block text-sm font-medium text-gray-700">New PV</label>
                        <input type="text" id="new-pv" name="prod_version" class="w-full border rounded px-3 py-2"
                            placeholder="Enter new PV">
                    </div>

                    <div class="flex justify-between pt-4 border-t border-gray-200 flex-wrap">
                        <button type="button" onclick="closeModalChangePV()"
                                class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="reschedule-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-4 rounded shadow w-full max-w-md">
            <h2 class="text-lg font-semibold mb-4">Re-Schedule Order</h2>

            <form method="POST" action="{{ route('sap.reschedule') }}">
                @csrf
                <input type="hidden" id="reschedule-aufnr" name="aufnr">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                    <input type="date" id="reschedule-date" name="date" class="mt-1 block w-full border rounded px-2 py-1" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Jam</label>
                    <input type="text" id="reschedule-time" name="time" value="00.00.00" placeholder="HH.MM.SS"
                        class="w-full border rounded px-3 py-2" required>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-200 flex-wrap">
                    <button type="button" onclick="closeRescheduleModal()"
                            class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Component Modal -->
    <div id="modal-delete-component" class="fixed inset-0 z-50 items-center justify-center p-4 bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Delete Component</h2>
                    <button onclick="closeModalDeleteComponent()" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="mb-4">
                    <p class="text-gray-700">Are you sure you want to delete this component?</p>
                    <p class="text-sm text-gray-500 mt-2" id="delete-component-info"></p>
                </div>

                <form id="delete-component-form" method="POST" action="{{ route('manufact.delete.component') }}">
                    @csrf
                    <input type="hidden" id="delete-component-aufnr" name="iv_aufnr">
                    <input type="hidden" id="delete-component-rspos" name="iv_rspos">

                    <div class="flex justify-between pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeModalDeleteComponent()"
                                class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit" id="delete-component-submit-btn"
                                class="px-6 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            Delete Component
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include Add Component Modal -->
    @include('manufact.add-component-modal')

    @if(session('success'))
        <div id="modal-success" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white p-6 rounded shadow-xl w-full max-w-md text-center">
                <h2 class="text-lg font-semibold text-green-700 mb-4">Sukses</h2>
                <p class="text-gray-700 mb-6">{{ session('success') }}</p>
                <button onclick="document.getElementById('modal-success').remove()"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    OK
                </button>
            </div>
        </div>
    @endif

    <script>
        let currentActiveAufnr = null;
        const searchInput = document.getElementById('searchInput');
        let searchTimeout;
        let currentSelectedRow = null;
        let currentActivePlnum = null;
        let selectedPLO = new Set();
        let selectedPRO = new Set();
        let currentFilter = 'all';
        let allRowsData = [];
        
        // Component selection handling
        let selectedComponents = {};

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.form.submit();
            }, 500);
        });

        function formatDate(dateString) {
            if (!dateString || dateString === '-' || dateString === '') {
                return '-';
            }
            
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) {
                    return dateString;
                }
                
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                
                return `${day}-${month}-${year}`;
            } catch (e) {
                return dateString;
            }
        }

        function filterByStatus(status) {
            currentFilter = status;
            
            // Update button styles
            document.querySelectorAll('#status-filter button').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('text-gray-700', 'hover:bg-gray-300');
            });
            
            const activeBtn = document.getElementById(`filter-${status}`);
            activeBtn.classList.remove('text-gray-700', 'hover:bg-gray-300');
            activeBtn.classList.add('bg-blue-600', 'text-white');
            
            // Filter and display rows
            const tbody = document.getElementById('tdata3-body');
            tbody.innerHTML = '';
            
            let filteredData = allRowsData;
            
            if (status === 'plo') {
                filteredData = allRowsData.filter(d3 => d3.PLNUM && !d3.AUFNR);
            } else if (status === 'crtd') {
                filteredData = allRowsData.filter(d3 => d3.AUFNR && d3.STATS === 'CRTD');
            } else if (status === 'released') {
                filteredData = allRowsData.filter(d3 => d3.AUFNR && ['PCNF', 'REL', 'CNF REL'].includes(d3.STATS));
            }
            
            // Render filtered data
            filteredData.forEach((d3, index) => {
                const row = createTableRow(d3, index + 1);
                tbody.appendChild(row);
            });
            
            // Clear selections when filtering
            clearAllSelections();
        }

        function createTableRow(d3, index) {
            const row = document.createElement('tr');
            row.className = 'border';
            
            // Determine if this row can be selected for bulk actions
            const canSelectForPLO = d3.PLNUM && !d3.AUFNR;
            const canSelectForPRO = d3.AUFNR && d3.STATS === 'CRTD';
            const canSelect = canSelectForPLO || canSelectForPRO;
            
            // Enhanced status styling with visual indicators
            let statusDisplay = d3.STATS || '-';
            let statusClass = '';
            
            if (d3.STATS === 'CRTD') {
                statusClass = 'bg-orange-100 text-orange-800 px-2 py-1 rounded-full text-xs font-medium';
            } else if (['PCNF', 'REL', 'CNF REL'].includes(d3.STATS)) {
                statusClass = 'bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium';
            }
            
            row.innerHTML = `
                <td class="px-2 py-1 border text-center">
                    ${canSelect ? `
                        <input type="checkbox" 
                               class="bulk-select" 
                               data-type="${canSelectForPLO ? 'PLO' : 'PRO'}"
                               data-id="${canSelectForPLO ? d3.PLNUM : d3.AUFNR}"
                               data-auart="${d3.AUART || ''}"
                               onchange="handleBulkSelect(this)">
                    ` : '-'}
                </td>
                <td class="px-2 py-1 border text-center">${index}</td>
                <td class="px-2 py-1 border">
                    <div class="flex items-center gap-2">
                        <span>${d3.PLNUM || '-'}</span>
                        ${d3.PLNUM ? `
                            <button type="button"
                                class="bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700"
                                onclick="showTData4ByPlnum('${d3.PLNUM}')">COMPONENT</button>
                        ` : ''}
                    </div>
                </td>
                <td class="px-2 py-1 border">
                    <div class="flex items-center gap-2">
                        <span>${d3.AUFNR || '-'}</span>
                        ${d3.AUFNR ? `
                            <button type="button"
                                class="bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700"
                                onclick="showTData1('${d3.AUFNR}')">ROUTING</button>
                            <button type="button"
                                class="bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700"
                                onclick="showTData4('${d3.AUFNR}')">COMPONENT</button>
                        ` : ''}
                    </div>
                </td>
                <td class="px-2 py-1 border">
                    ${statusClass ? `<span class="${statusClass}">${statusDisplay}</span>` : statusDisplay}
                </td>
                <td class="px-2 py-1 border">
                    <div class="flex gap-2">
                        ${d3.AUFNR && d3.STATS === 'CRTD' ? `
                            <button type="button"
                                class="bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700"
                                onclick="releaseOrder('${d3.AUFNR}')">
                                Release
                            </button>
                        ` : ''}
                        
                        ${d3.PLNUM && !d3.AUFNR ? `
                            <button type="button"
                                class="bg-orange-600 text-white px-2 py-1 rounded text-xs hover:bg-orange-700"
                                onclick='convertPlannedOrderFromRow(${JSON.stringify(d3)})'>
                                Convert
                            </button>
                        ` : ''}
                        
                        ${d3.AUFNR ? `
                            <button type="button"
                                class="bg-red-600 text-white px-2 py-1 rounded text-xs hover:bg-red-700"
                                onclick="openRescheduleModal('${d3.AUFNR}')">
                                ⏰
                            </button>
                        ` : ''}
                    </div>
                </td>
                <td class="px-2 py-1 border">${d3.DISPO || '-'}</td>
                <td class="px-2 py-1 border">${d3.MATNR ? ltrimZeros(d3.MATNR) : '-'}</td>
                <td class="px-2 py-1 border">${d3.MAKTX || '-'}</td>
                <td class="px-2 py-1 border">${d3.PSMNG || '-'}</td>
                <td class="px-2 py-1 border">${d3.WEMNG || '-'}</td>
                <td class="px-2 py-1 border">${d3.MENG2 || '-'}</td>
                <td class="px-2 py-1 border">${formatDate(d3.SSAVD)}</td>
                <td class="px-2 py-1 border">${formatDate(d3.SSSLD)}</td>
            `;
            
            return row;
        }

        function showTData3(key, clickedRow) {
            const container = document.getElementById('tdata3-container');
            const tbody = document.getElementById('tdata3-body');

            if (currentSelectedRow === clickedRow) {
                hideAllDetails();
                return;
            }

            hideAllDetails();
            tbody.innerHTML = '';

            const data3 = @json($allTData3);

            if (data3[key] && data3[key].length > 0) {
                clickedRow.classList.add('bg-blue-100');
                currentSelectedRow = clickedRow;

                const allRows = document.querySelectorAll('tbody tr');
                allRows.forEach(row => {
                    if (row !== clickedRow) {
                        row.classList.add('hidden');
                    }
                });

                // Store all data for filtering
                allRowsData = data3[key];
                currentFilter = 'all';
                
                // Reset filter buttons
                document.querySelectorAll('#status-filter button').forEach(btn => {
                    btn.classList.remove('bg-blue-600', 'text-white');
                    btn.classList.add('text-gray-700', 'hover:bg-gray-300');
                });
                document.getElementById('filter-all').classList.remove('text-gray-700', 'hover:bg-gray-300');
                document.getElementById('filter-all').classList.add('bg-blue-600', 'text-white');

                // Display all data initially
                data3[key].forEach((d3, index) => {
                    const row = createTableRow(d3, index + 1);
                    tbody.appendChild(row);
                });

                container.classList.remove('hidden');
            } else {
                alert('Tidak ada detail ditemukan.');
            }
        }

        function handleBulkSelect(checkbox) {
            const type = checkbox.dataset.type;
            const id = checkbox.dataset.id;
            
            if (checkbox.checked) {
                if (type === 'PLO') {
                    selectedPLO.add({
                        plnum: id,
                        auart: checkbox.dataset.auart
                    });
                } else if (type === 'PRO') {
                    selectedPRO.add(id);
                }
            } else {
                if (type === 'PLO') {
                    selectedPLO.forEach(item => {
                        if (item.plnum === id) {
                            selectedPLO.delete(item);
                        }
                    });
                } else if (type === 'PRO') {
                    selectedPRO.delete(id);
                }
            }
            
            updateBulkControls();
        }

        function toggleSelectAll() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.bulk-select');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
                handleBulkSelect(checkbox);
            });
        }

        function updateBulkControls() {
            const bulkControls = document.getElementById('bulk-controls');
            const convertBtn = document.getElementById('bulk-convert-btn');
            const releaseBtn = document.getElementById('bulk-release-btn');
            
            const hasSelectedPLO = selectedPLO.size > 0;
            const hasSelectedPRO = selectedPRO.size > 0;
            
            if (hasSelectedPLO || hasSelectedPRO) {
                bulkControls.classList.remove('hidden');
                
                if (hasSelectedPLO) {
                    convertBtn.classList.remove('hidden');
                    convertBtn.textContent = `Convert Selected PLO (${selectedPLO.size})`;
                } else {
                    convertBtn.classList.add('hidden');
                }
                
                if (hasSelectedPRO) {
                    releaseBtn.classList.remove('hidden');
                    releaseBtn.textContent = `Release Selected PRO (${selectedPRO.size})`;
                } else {
                    releaseBtn.classList.add('hidden');
                }
            } else {
                bulkControls.classList.add('hidden');
            }
        }

        function clearAllSelections() {
            selectedPLO.clear();
            selectedPRO.clear();
            
            const checkboxes = document.querySelectorAll('.bulk-select');
            checkboxes.forEach(checkbox => checkbox.checked = false);
            
            document.getElementById('select-all').checked = false;
            updateBulkControls();
        }

        function bulkConvertPlannedOrders() {
            if (selectedPLO.size === 0) {
                alert('Tidak ada PLO yang dipilih.');
                return;
            }
            
            const ploArray = Array.from(selectedPLO);
            const message = `Apakah Anda yakin ingin mengkonversi ${ploArray.length} Planned Order?`;
            
            if (!confirm(message)) return;
            
            // Show loading
            const loader = document.getElementById('global-loading');
            if (loader) {
                loader.style.display = 'flex';
                loader.style.opacity = '1';
            }
            
            // Process conversions
            Promise.all(ploArray.map(item => {
                const url = `/sap/convert-direct?plnum=${encodeURIComponent(item.plnum)}&auart=${encodeURIComponent(item.auart)}`;
                return fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
            }))
            .then(responses => {
                const failed = responses.filter(r => !r.ok);
                if (failed.length > 0) {
                    throw new Error(`${failed.length} konversi gagal dari ${responses.length} total.`);
                }
                alert(`${ploArray.length} Planned Order berhasil dikonversi.`);
                location.reload();
            })
            .catch(error => {
                alert(error.message || 'Terjadi kesalahan saat konversi bulk.');
            })
            .finally(() => {
                if (loader) {
                    loader.style.opacity = '0';
                    setTimeout(() => loader.style.display = 'none', 300);
                }
            });
        }

        function bulkReleaseOrders() {
            if (selectedPRO.size === 0) {
                alert('Tidak ada PRO yang dipilih.');
                return;
            }
            
            const proArray = Array.from(selectedPRO);
            const message = `Apakah Anda yakin ingin me-release ${proArray.length} Production Order?`;
            
            if (!confirm(message)) return;
            
            // Show loading
            const loader = document.getElementById('global-loading');
            if (loader) {
                loader.style.display = 'flex';
                loader.style.opacity = '1';
            }
            
            // Process releases
            Promise.all(proArray.map(aufnr => {
                return fetch(`/release-order/${aufnr}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
            }))
            .then(responses => {
                const failed = responses.filter(r => !r.ok);
                if (failed.length > 0) {
                    throw new Error(`${failed.length} release gagal dari ${responses.length} total.`);
                }
                alert(`${proArray.length} Production Order berhasil di-release.`);
                location.reload();
            })
            .catch(error => {
                alert(error.message || 'Terjadi kesalahan saat release bulk.');
            })
            .finally(() => {
                if (loader) {
                    loader.style.opacity = '0';
                    setTimeout(() => loader.style.display = 'none', 300);
                }
            });
        }

        function hideAllDetails() {
            // Clear selections when hiding
            clearAllSelections();
            
            // Clear component selections
            selectedComponents = {};
            
            // Sembunyikan container T_DATA3
            document.getElementById('tdata3-container').classList.add('hidden');
            
            // Kosongkan isi tabel T_DATA3
            document.getElementById('tdata3-body').innerHTML = '';
            
            // Tampilkan kembali semua baris T_DATA2
            const allRows = document.querySelectorAll('tbody tr');
            allRows.forEach(row => {
                row.classList.remove('hidden');
                row.classList.remove('bg-blue-100');
            });
            
            currentSelectedRow = null;
            currentActiveAufnr = null;
            currentActivePlnum = null;
            document.getElementById('additional-data-container').innerHTML = '';
            
            // Reset filter
            allRowsData = [];
            currentFilter = 'all';
        }

        function hideRow(button) {
            const row = button.closest('tr');
            row.remove();
            
            // Sembunyikan container jika tidak ada data lagi
            const tbody = document.getElementById('tdata3-body');
            if (tbody.children.length === 0) {
                hideAllDetails();
            }
        }

        function showTData1(aufnr) {
            const existingElement = document.getElementById(`tdata1-${aufnr}`);

            if (existingElement) {
                existingElement.remove();

                // Kembalikan semua T_DATA3 row yang disembunyikan
                const tdata3Rows = document.querySelectorAll('#tdata3-body tr');
                tdata3Rows.forEach(row => row.classList.remove('hidden'));

                currentActiveAufnr = null;
                return;
            }

            // Jika sedang menampilkan data aufnr yang sama, toggle ulang
            if (currentActiveAufnr === aufnr) {
                return;
            }

            // Sembunyikan T_DATA3 yang bukan milik aufnr
            const tdata3Rows = document.querySelectorAll('#tdata3-body tr');
            tdata3Rows.forEach(row => {
                if (!row.textContent.includes(aufnr)) {
                    row.classList.add('hidden');
                } else {
                    row.classList.remove('hidden');
                }
            });

            currentActiveAufnr = aufnr;

            const container = document.getElementById('additional-data-container');
            const tData1 = @json($allTData1);

            const existing = document.getElementById(`tdata1-${aufnr}`);
            if (existing) existing.remove();

            if (tData1[aufnr] && tData1[aufnr].length > 0) {
                const div = document.createElement('div');
                div.id = `tdata1-${aufnr}`;
                div.className = 'bg-gray-50 p-4 rounded-lg mb-4';
                        
                        div.innerHTML = `
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-md font-semibold">Routing Overview</h4>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="table-auto w-full text-xs border">
                                    <thead>
                                        <tr>
                                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">No.</th>
                                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">ACTIVITY</th>
                                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">CONTROL KEY</th>
                                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">DESCRIPTION</th>
                                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">NEW WORKCENTER</th>
                                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">DEFAULT PV1</th>
                                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">PV2</th>
                                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">PV3</th>
                                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">ACTION</th>
                                            </tr>
                                    </thead>

                                    <tbody>
                                        ${tData1[aufnr].map((t1, index) => `
                                            <tr>
                                                <td class="border px-3 py-2 text-center">${index + 1}</td>
                                                <td class="border px-3 py-2">${t1.VORNR}</td>
                                                <td class="border px-3 py-2">${t1.STEUS}</td>
                                                <td class="border px-3 py-2">${t1.KTEXT}</td>
                                                <td class="border px-3 py-2">
                                                    ${
                                                        (() => {
                                                            let defaultPv = '-';
                                                            if (t1.VERID === '0001') {
                                                                defaultPv = t1.PV1;
                                                            } else if (t1.VERID === '0002') {
                                                                defaultPv = t1.PV2;
                                                            } else if (t1.VERID === '0003') {
                                                                defaultPv = t1.PV3;
                                                            }
                                                            // Ambil sebelum tanda '-' dari defaultPv
                                                            let defaultPrefix = defaultPv?.split('-')[0]?.trim();
                                                            // Hanya jika VERID adalah 0001, jika ARBPL sama dengan prefix PV1, maka tampilkan '-'
                                                            if (t1.VERID === '0001' && defaultPrefix && t1.ARBPL?.trim() === defaultPrefix) {
                                                                return '-';
                                                            }
                                                            return t1.ARBPL || '-';
                                                        })()
                                                    }
                                                </td>
                                                <td class="border px-3 py-2 ${t1.VERID === '0001' ? 'bg-blue-100 font-semibold text-green-900' : ''}">${t1.PV1 ?? '-'}</td>
                                                <td class="border px-3 py-2 ${t1.VERID === '0002' ? 'bg-blue-100 font-semibold text-green-900' : ''}">${t1.PV2 ?? '-'}</td>
                                                <td class="border px-3 py-2 ${t1.VERID === '0003' ? 'bg-blue-100 font-semibold text-green-900' : ''}">${t1.PV3 ?? '-'}</td>
                                                <td class="border px-3 py-2">
                                                    <button class="bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-indigo-600"onclick="openModalEditWC('${t1.AUFNR}', '${t1.VORNR}', '${t1.ARBPL || ''}')">
                                                        Edit WC
                                                    </button>
                                                    <button type="button"class="bg-orange-600 text-white px-2 py-1 rounded text-xs hover:bg-orange-500"onclick="openModalChangePV('${t1.AUFNR}', '${t1.VERID}')">
                                                        Change PV
                                                    </button>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        `;
                        
                        container.appendChild(div);
                    } else {
                        alert('Tidak ada data routing ditemukan.');
                    }
                }

        // UPDATED showTData4 function with AJAX support for real-time updates
        function showTData4(aufnr) {
            const existingElement = document.getElementById(`tdata4-${aufnr}`);

            if (existingElement) {
                existingElement.remove();

                // Kembalikan semua T_DATA3 row
                const tdata3Rows = document.querySelectorAll('#tdata3-body tr');
                tdata3Rows.forEach(row => row.classList.remove('hidden'));

                currentActiveAufnr = null;
                return;
            }

            if (currentActiveAufnr === aufnr) {
                return;
            }

            const tdata3Rows = document.querySelectorAll('#tdata3-body tr');
            tdata3Rows.forEach(row => {
                if (!row.textContent.includes(aufnr)) {
                    row.classList.add('hidden');
                } else {
                    row.classList.remove('hidden');
                }
            });

            currentActiveAufnr = aufnr;

            const container = document.getElementById('additional-data-container');
            const tData4 = @json($allTData4);

            const existing = document.getElementById(`tdata4-${aufnr}`);
            if (existing) existing.remove();

            if (tData4[aufnr] && tData4[aufnr].length > 0) {
                renderComponentTable(aufnr, tData4[aufnr], container);
            } else {
                alert('Tidak ada data BOM ditemukan.');
            }
        }

        // NEW: Function to render component table
        function renderComponentTable(aufnr, components, container) {
            const div = document.createElement('div');
            div.id = `tdata4-${aufnr}`;
            div.className = 'bg-gray-50 p-4 rounded-lg mb-4';
            
            div.innerHTML = `
                <div class="flex justify-between items-center mb-2">
                    <h4 class="text-md font-semibold">Component Overview</h4>
                    <div class="flex items-center gap-2">
                        <!-- Bulk Delete Controls -->
                        <div id="bulk-delete-controls-${aufnr}" class="flex items-center gap-2 hidden">
                            <button type="button"
                                    id="bulk-delete-btn-${aufnr}"
                                    class="bg-red-600 text-white px-3 py-2 rounded text-sm hover:bg-red-700"
                                    onclick="bulkDeleteComponents('${aufnr}')">
                                Delete Selected (0)
                            </button>
                            <button type="button"
                                    class="bg-gray-500 text-white px-3 py-2 rounded text-sm hover:bg-gray-600"
                                    onclick="clearComponentSelections('${aufnr}')">
                                Clear All
                            </button>
                        </div>
                        
                        <!-- Add Component Button - Green -->
                        <button type="button"
                                class="bg-green-600 text-white px-3 py-2 rounded text-sm hover:bg-green-700"
                                onclick="openModalAddComponent('${aufnr}', '0010', '${getPlantFromCurrentData()}')">
                            Add Component
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-auto w-full text-sm border">
                        <thead>
                            <tr>
                                <th class="border px-3 py-2 bg-blue-100 text-blue-900 font-semibold">
                                    <input type="checkbox" 
                                           id="select-all-components-${aufnr}" 
                                           onchange="toggleSelectAllComponents('${aufnr}')" 
                                           class="mr-1">
                                    Select
                                </th>
                                <th class="border px-3 py-2 bg-blue-100 text-blue-900 font-semibold">No.</th>
                                <th class="border px-3 py-2 bg-blue-100 text-blue-900 font-semibold">MATERIAL</th>
                                <th class="border px-3 py-2 bg-blue-100 text-blue-900 font-semibold">DESCRIPTION</th>
                                <th class="border px-3 py-2 bg-blue-100 text-blue-900 font-semibold">REQ QTY</th>
                                <th class="border px-3 py-2 bg-blue-100 text-blue-900 font-semibold">STOCK</th>
                                <th class="border px-3 py-2 bg-blue-100 text-blue-900 font-semibold">SPEC PROCUREMENT</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${components.map((bom, index) => `
                                <tr>
                                    <td class="border px-3 py-2 text-center">
                                        <input type="checkbox" 
                                               class="component-select-${aufnr}" 
                                               data-aufnr="${aufnr}"
                                               data-rspos="${bom.RSPOS}"
                                               data-material="${bom.MATNR ? ltrimZeros(bom.MATNR) : '-'}"
                                               onchange="handleComponentSelect('${aufnr}')">
                                    </td>
                                    <td class="border px-3 py-2 text-center">${index + 1}</td>
                                    <td class="border px-3 py-2">${bom.MATNR ? ltrimZeros(bom.MATNR) : '-'}</td>
                                    <td class="border px-3 py-2">${bom.MAKTX || '-'}</td>
                                    <td class="border px-3 py-2">${bom.BDMNG || '-'}</td>
                                    <td class="border px-3 py-2">${bom.KALAB || '-'}</td>
                                    <td class="border px-3 py-2">${bom.LTEXT && bom.LTEXT.trim() !== '' ? bom.LTEXT : 'in house production'}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            
            container.appendChild(div);
        }

        // UPDATED: Component selection handling functions with AJAX delete support
        function handleComponentSelect(aufnr) {
            if (!selectedComponents[aufnr]) {
                selectedComponents[aufnr] = new Set();
            }
            
            const checkboxes = document.querySelectorAll(`.component-select-${aufnr}`);
            const bulkControls = document.getElementById(`bulk-delete-controls-${aufnr}`);
            const bulkDeleteBtn = document.getElementById(`bulk-delete-btn-${aufnr}`);
            const selectAllCheckbox = document.getElementById(`select-all-components-${aufnr}`);
            
            // Clear previous selections for this aufnr
            selectedComponents[aufnr].clear();
            
            // Count selected checkboxes
            let selectedCount = 0;
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    selectedCount++;
                    selectedComponents[aufnr].add({
                        rspos: checkbox.dataset.rspos,
                        material: checkbox.dataset.material
                    });
                }
            });
            
            // Update bulk controls visibility and text
            if (selectedCount > 0) {
                bulkControls.classList.remove('hidden');
                bulkDeleteBtn.textContent = `Delete Selected (${selectedCount})`;
            } else {
                bulkControls.classList.add('hidden');
            }
            
            // Update select all checkbox state
            if (selectedCount === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (selectedCount === checkboxes.length) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
                selectAllCheckbox.checked = false;
            }
        }

        function toggleSelectAllComponents(aufnr) {
            const selectAllCheckbox = document.getElementById(`select-all-components-${aufnr}`);
            const checkboxes = document.querySelectorAll(`.component-select-${aufnr}`);
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            handleComponentSelect(aufnr);
        }

        function clearComponentSelections(aufnr) {
            const checkboxes = document.querySelectorAll(`.component-select-${aufnr}`);
            const selectAllCheckbox = document.getElementById(`select-all-components-${aufnr}`);
            
            checkboxes.forEach(checkbox => checkbox.checked = false);
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            
            if (selectedComponents[aufnr]) {
                selectedComponents[aufnr].clear();
            }
            
            const bulkControls = document.getElementById(`bulk-delete-controls-${aufnr}`);
            bulkControls.classList.add('hidden');
        }

        // UPDATED: Bulk delete with AJAX support
        function bulkDeleteComponents(aufnr) {
            if (!selectedComponents[aufnr] || selectedComponents[aufnr].size === 0) {
                alert('Tidak ada komponen yang dipilih.');
                return;
            }
            
            const componentsArray = Array.from(selectedComponents[aufnr]);
            const message = `Apakah Anda yakin ingin menghapus ${componentsArray.length} komponen yang dipilih?`;
            
            if (!confirm(message)) return;
            
            // Show loading on bulk delete button
            const bulkDeleteBtn = document.getElementById(`bulk-delete-btn-${aufnr}`);
            const originalText = bulkDeleteBtn.textContent;
            bulkDeleteBtn.disabled = true;
            bulkDeleteBtn.textContent = 'Deleting...';
            
            // Process deletions with AJAX
            Promise.all(componentsArray.map(component => {
                const formData = new FormData();
                formData.append('iv_aufnr', aufnr);
                formData.append('iv_rspos', component.rspos);
                
                return fetch('/manufact/delete-component', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
            }))
            .then(responses => {
                return Promise.all(responses.map(r => r.json()));
            })
            .then(results => {
                const failed = results.filter(r => !r.success);
                if (failed.length > 0) {
                    throw new Error(`${failed.length} penghapusan gagal dari ${results.length} total.`);
                }
                
                // Get the last successful result for updated components
                const lastSuccessResult = results.find(r => r.success && r.components);
                if (lastSuccessResult) {
                    updateComponentTable(aufnr, lastSuccessResult.components);
                    showNotification(`${componentsArray.length} komponen berhasil dihapus.`, 'success');
                }
            })
            .catch(error => {
                showNotification(error.message || 'Terjadi kesalahan saat menghapus komponen.', 'error');
            })
            .finally(() => {
                // Reset button state
                bulkDeleteBtn.disabled = false;
                bulkDeleteBtn.textContent = originalText;
            });
        }

        function showTData4ByPlnum(plnum) {
            const existingElement = document.getElementById(`tdata4-plnum-${plnum}`);

            if (existingElement) {
                existingElement.remove();

                // Tampilkan kembali semua baris T_DATA3
                const tdata3Rows = document.querySelectorAll('#tdata3-body tr');
                tdata3Rows.forEach(row => row.classList.remove('hidden'));

                currentActivePlnum = null;
                return;
            }

            // Toggle jika plnum yang sama diklik ulang
            if (currentActivePlnum === plnum) {
                return;
            }

            // Sembunyikan baris T_DATA3 yang tidak mengandung plnum
            const tdata3Rows = document.querySelectorAll('#tdata3-body tr');
            tdata3Rows.forEach(row => {
                if (!row.textContent.includes(plnum)) {
                    row.classList.add('hidden');
                } else {
                    row.classList.remove('hidden');
                }
            });

            currentActivePlnum = plnum;

            const container = document.getElementById('additional-data-container');
            const tData4ByPlnum = @json($allTData4ByPlnum);

            // Hapus elemen sebelumnya jika ada (jaga-jaga)
            const existing = document.getElementById(`tdata4-plnum-${plnum}`);
            if (existing) existing.remove();

            if (tData4ByPlnum[plnum] && tData4ByPlnum[plnum].length > 0) {
                const div = document.createElement('div');
                div.id = `tdata4-plnum-${plnum}`;
                div.className = 'bg-gray-50 p-4 rounded-lg mb-4';
                        
                        div.innerHTML = `
   <div class="flex justify-between items-center mb-2">
       <h4 class="text-md font-semibold">Component Overview</h4>
   </div>
   <div class="overflow-x-auto">
       <table class="table-auto w-full text-sm border">
           <thead>
               <tr>
                   <th class="border px-3 py-2 bg-blue-100 text-blue-900 font-semibold">No.</th>
                   <th class="border px-3 py-2 bg-blue-100 text-blue-900 font-semibold">MATERIAL</th>
                   <th class="border px-3 py-2 bg-blue-100 text-blue-900 font-semibold">DESCRIPTION</th>
                   <th class="border px-3 py-2 bg-blue-100 text-blue-900 font-semibold">REQ QTY</th>
                   <th class="border px-3 py-2 bg-blue-100 text-blue-900 font-semibold">STOCK</th>
                   <th class="border px-3 py-2 bg-blue-100 text-blue-900 font-semibold">SPEC PROCUREMENT</th>
               </tr>
           </thead>
           <tbody>
               ${tData4ByPlnum[plnum].map((bom, index) => `
                   <tr>
                       <td class="border px-3 py-2 text-center">${index + 1}</td>
                       <td class="border px-3 py-2">${bom.MATNR ? ltrimZeros(bom.MATNR) : '-'}</td>
                       <td class="border px-3 py-2">${bom.MAKTX || '-'}</td>
                       <td class="border px-3 py-2">${bom.BDMNG || '-'}</td>
                       <td class="border px-3 py-2">${bom.KALAB || '-'}</td>
                       <td class="border px-3 py-2">${bom.LTEXT || '-'}</td>
                   </tr>
               `).join('')}
           </tbody>
       </table>
   </div>
`;

container.appendChild(div);
           } else {
               alert('Tidak ada data BOM ditemukan.');
           }
       }

       function ltrimZeros(str) {
           // Hapus leading zeros, tapi sisakan minimal 1 karakter jika semuanya 0
           return str.replace(/^0+/, '') || '0';
       }

       // Helper function to get plant from current data
       function getPlantFromCurrentData() {
           return '{{ $plant }}'; // Get plant from blade variable
       }

       // Release
       function releaseOrder(aufnr) {
           if (confirm(`Apakah Anda yakin ingin me-release order ${aufnr}?`)) {
               // Tampilkan loading
               const loader = document.getElementById('global-loading');
               if (loader) {
                   loader.style.display = 'flex';
                   loader.style.opacity = '1';
               }

               // Kirim request GET ke controller
               fetch(`/release-order/${aufnr}`, {
                   method: 'GET',
                   headers: {
                       'Content-Type': 'application/json',
                       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                   }
               })
               .then(response => {
                   if (!response.ok) {
                       throw new Error('Network response was not ok');
                   }
                   return response.json();
               })
               .then(data => {
                   if (data.success) {
                       alert(data.message || `Order ${aufnr} berhasil direlease`);
                       // Refresh data atau update UI sesuai kebutuhan
                       location.reload();
                   } else {
                       throw new Error(data.message || `Gagal me-release order ${aufnr}`);
                   }
               })
               .catch(error => {
                   alert(error.message);
               })
               .finally(() => {
                   // Sembunyikan loading
                   if (loader) {
                       loader.style.opacity = '0';
                       setTimeout(() => loader.style.display = 'none', 300);
                   }
               });
           }
       }
       
       function closeAdditionalData(button) {
           const container = button.closest('.bg-gray-50');
           container.remove();
           
           // Show all T_DATA3 rows again
           const tdata3Rows = document.querySelectorAll('#tdata3-body tr');
           tdata3Rows.forEach(row => {
               row.classList.remove('hidden');
           });
       }

       function convertPlannedOrderFromRow(d3) {
           const plnum = d3.PLNUM;
           const auart = d3.AUART;

           if (!plnum || !auart) {
               alert('PLNUM atau AUART tidak ditemukan.');
               return;
           }

           if (!confirm(`Konversi Planned Order ${plnum} (AUART: ${auart})?`)) return;

           // Tampilkan loading
           const loader = document.getElementById('global-loading');
           if (loader) {
               loader.style.display = 'flex';
               loader.style.opacity = '1';
           }

           const url = `/sap/convert-direct?plnum=${encodeURIComponent(plnum)}&auart=${encodeURIComponent(auart)}`;

           fetch(url, {
               method: 'GET',
               headers: {
                   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
               }
           })
           .then(response => {
               if (!response.ok) throw new Error('Gagal konversi planned order.');
               return response.text();
           })
           .then(() => {
               alert(`Planned Order ${plnum} berhasil dikonversi.`);
               location.reload();
           })
           .catch(error => {
               alert(error.message || 'Terjadi kesalahan saat konversi.');
           })
           .finally(() => {
               if (loader) {
                   loader.style.opacity = '0';
                   setTimeout(() => loader.style.display = 'none', 300);
               }
           });
       }

       function openModalEditWC(aufnr, vornr, currentArbpl = '') {
           document.getElementById('edit-wc-aufnr').value = aufnr;
           document.getElementById('edit-wc-operation').value = vornr;
           document.getElementById('edit-wc-work-center').value = currentArbpl;

           const modal = document.getElementById('modal-edit-wc');
           modal.classList.remove('hidden');
           modal.classList.add('flex');
       }

       function closeModalEditWC() {
           const modal = document.getElementById('modal-edit-wc');
           modal.classList.remove('flex');
           modal.classList.add('hidden');
       }

       function openModalChangePV(aufnr, currentPV) {
           const modal = document.getElementById('modal-change-pv');
           modal.classList.remove('hidden');
           modal.classList.add('flex');

           // Isi form dengan AUFNR dan PV lama
           document.getElementById('change-pv-aufnr').value = aufnr;
           document.getElementById('change-pv-current').value = currentPV;
           document.getElementById('new-pv').value = currentPV; // isikan default value
       }

       function closeModalChangePV() {
           const modal = document.getElementById('modal-change-pv');
           modal.classList.add('hidden');
           modal.classList.remove('flex');
       }

       function openRescheduleModal(aufnr) {
           document.getElementById('reschedule-aufnr').value = aufnr;
           document.getElementById('reschedule-date').value = '';
           document.getElementById('reschedule-time').value = '00.00.00';

           const modal = document.getElementById('reschedule-modal');
           modal.classList.remove('hidden');
           modal.classList.add('flex');
       }

       function closeRescheduleModal() {
           const modal = document.getElementById('reschedule-modal');
           modal.classList.remove('flex');
           modal.classList.add('hidden');
       }

       // Component Management Functions
       function openModalDeleteComponent(aufnr, rspos, materialName) {
           document.getElementById('delete-component-aufnr').value = aufnr;
           document.getElementById('delete-component-rspos').value = rspos;
           document.getElementById('delete-component-info').textContent = `Material: ${materialName} (Position: ${rspos})`;

           const modal = document.getElementById('modal-delete-component');
           modal.classList.remove('hidden');
           modal.classList.add('flex');
       }

       function closeModalDeleteComponent() {
           const modal = document.getElementById('modal-delete-component');
           modal.classList.remove('flex');
           modal.classList.add('hidden');
       }

       // UPDATED: Handle delete component form submission with AJAX
       document.getElementById('delete-component-form').addEventListener('submit', function(e) {
           e.preventDefault(); // Prevent default form submission
           
           const submitBtn = document.getElementById('delete-component-submit-btn');
           const originalText = submitBtn.textContent;
           
           // Show loading state
           submitBtn.disabled = true;
           submitBtn.textContent = 'Deleting...';
           
           // Get form data
           const formData = new FormData(this);
           
           // Send AJAX request
           fetch(this.action, {
               method: 'POST',
               headers: {
                   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                   'X-Requested-With': 'XMLHttpRequest'
               },
               body: formData
           })
           .then(response => response.json())
           .then(data => {
               if (data.success) {
                   // Show success message
                   showNotification(data.message, 'success');
                   
                   // Close modal
                   closeModalDeleteComponent();
                   
                   // Update the component table if it's currently displayed
                   const aufnr = document.getElementById('delete-component-aufnr').value;
                   updateComponentTable(aufnr, data.components);
                   
               } else {
                   showNotification(data.message, 'error');
               }
           })
           .catch(error => {
               console.error('Error:', error);
               showNotification('Terjadi kesalahan saat menghapus komponen.', 'error');
           })
           .finally(() => {
               // Reset button state
               submitBtn.disabled = false;
               submitBtn.textContent = originalText;
           });
       });

       function submitReschedule() {
           const aufnr = document.getElementById('reschedule-aufnr').value;
           const date = document.getElementById('reschedule-date').value;
           const time = document.getElementById('reschedule-time').value;

           if (!date || !time) {
               alert('Silakan pilih tanggal dan jam terlebih dahulu.');
               return;
           }

           const formData = new FormData();
           formData.append('aufnr', aufnr);
           formData.append('date', date);
           formData.append('time', time);

           fetch('/reschedule', {
               method: 'POST',
               headers: {
                   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                   'Accept': 'application/json'
               },
               body: formData
           })
           .then(response => response.json())
           .then(data => {
               alert('Reschedule berhasil');
               closeRescheduleModal();
               location.reload();
           })
           .catch(error => {
               console.error('Reschedule gagal:', error);
               alert('Gagal melakukan reschedule.');
           });
       }

       // Function to show notifications (already defined in add-component-modal)
       function showNotification(message, type = 'success') {
           // Create notification element
           const notification = document.createElement('div');
           notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${
               type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
           }`;
           notification.textContent = message;
           
           // Add to page
           document.body.appendChild(notification);
           
           // Auto remove after 3 seconds
           setTimeout(() => {
               if (notification.parentNode) {
                   notification.parentNode.removeChild(notification);
               }
           }, 3000);
       }

       // Function to update component table (already defined in add-component-modal)
       function updateComponentTable(aufnr, components) {
           const componentTableContainer = document.getElementById(`tdata4-${aufnr}`);
           if (componentTableContainer) {
               // Find the tbody in the component table
               const tbody = componentTableContainer.querySelector('tbody');
               if (tbody) {
                   // Clear existing rows
                   tbody.innerHTML = '';
                   
                   // Add new rows
                   components.forEach((bom, index) => {
                       const row = document.createElement('tr');
                       row.innerHTML = `
                           <td class="border px-3 py-2 text-center">
                               <input type="checkbox" 
                                      class="component-select-${aufnr}" 
                                      data-aufnr="${aufnr}"
                                      data-rspos="${bom.RSPOS}"
                                      data-material="${bom.MATNR ? ltrimZeros(bom.MATNR) : '-'}"
                                      onchange="handleComponentSelect('${aufnr}')">
                           </td>
                           <td class="border px-3 py-2 text-center">${index + 1}</td>
                           <td class="border px-3 py-2">${bom.MATNR ? ltrimZeros(bom.MATNR) : '-'}</td>
                           <td class="border px-3 py-2">${bom.MAKTX || '-'}</td>
                           <td class="border px-3 py-2">${bom.BDMNG || '-'}</td>
                           <td class="border px-3 py-2">${bom.KALAB || '-'}</td>
                           <td class="border px-3 py-2">${bom.LTEXT && bom.LTEXT.trim() !== '' ? bom.LTEXT : 'in house production'}</td>
                       `;
                       tbody.appendChild(row);
                   });
                   
                   // Clear any existing selections
                   clearComponentSelections(aufnr);
               }
           }
       }
   </script>
</x-app-layout>