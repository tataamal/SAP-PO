<x-app-layout>
    <x-slot name="header">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                SAP Result
            </h2>
            <a href="{{ route('workcenter') }}"
               class="inline-block px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                ← Home
            </a>
        </div>
    </x-slot>

    @php
        $columns = [
            'AUFNR' => 'PRO',
            'MATNR' => 'MATERIAL',
            'VERID' => 'VERSION',
            'MAKTX' => 'DESCRIPTION',
            'KTEXT' => 'WC DESCRIPTION',
            'DDAY' => 'DUE DAY (D)',
            'DTIME' => 'DUE TIME (H)',
            'VORNR' => 'OPERATION',
            'AUART' => 'ORDER TYPE',
            'ARBPL' => 'WORK CENTER',
            'VORNR' => 'VORNR',
            'PWWRK' => 'PLANT',
            'PLNUM' => 'PLO',
            'STATS' => 'STATUS',
            'SSSLD' => 'END DATE',
            'SSAVD' => 'START DATE',
            'DISPO' => 'MRP',
            'PSMNG' => 'QTY ORDER',
            'WEMNG' => 'QTY PRD',
            'CPCTYX' => 'TIME REQUIRED (H)',

        ];
        $sortBy = request('sort_by');
        $sortDir = request('sort_direction', 'asc');
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-4">
                <p class="mb-4">
                    <strong>Plant:</strong> {{ $plant }} |
                    <strong>Workcenter:</strong> {{ $workcenter }}
                </p>

                <div class="mb-4 flex items-center justify-between">
                    <form method="GET" class="flex items-center">
                        <input type="hidden" name="plant" value="{{ $plant }}">
                        <input type="hidden" name="workcenter" value="{{ $workcenter }}">
                        <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                        <input type="hidden" name="sort_direction" value="{{ $sortDir }}">
                        <label for="per_page" class="text-sm font-medium mr-2">Show per page:</label>
                        <select name="per_page" id="per_page" onchange="this.form.submit()"
                                class="border border-gray-300 rounded p-1 pr-8 bg-white appearance-none bg-[url('data:image/svg+xml;utf8,<svg fill=\'black\' height=\'24\' viewBox=\'0 0 24 24\' width=\'24\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M7 10l5 5 5-5z\'/></svg>')] bg-no-repeat bg-[right_0.5rem_center] bg-[length:1rem_1rem]">
                            @foreach(['10','20','50','100','all'] as $option)
                                <option value="{{ $option }}" {{ request('per_page') == $option ? 'selected' : '' }}>
                                    {{ ucfirst($option) }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    <div>
                         @if (auth()->user()->role === 'admin')
                            <a href="#" onclick="toggleMessForm(event)"
                                class="inline-block px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                    Change Mess
                            </a>
                        @endif  
                            <a href="#" onclick="toggleConvertForm(event)"
                                class="inline-block px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                                    Convert Mess
                            </a>  
                    </div>

                </div>
                   <form method="GET" class="mb-4 flex flex-wrap gap-3 items-center">
                        <input type="hidden" name="plant" value="{{ $plant }}">
                        <input type="hidden" name="workcenter" value="{{ $workcenter }}">
                        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

                        <!-- Search -->
                        <label class="text-sm">Search:</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="border rounded px-2 py-1 text-sm w-40" id="autoSearch">

                        <!-- Sort by -->
                        <label class="text-sm">Sort by:</label>
                        <select name="sort_by"
                            class="border rounded px-2 py-1 pr-8 text-sm appearance-none bg-white bg-[url('data:image/svg+xml;utf8,<svg fill=\'black\' height=\'24\' viewBox=\'0 0 24 24\' width=\'24\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M7 10l5 5 5-5z\'/></svg>')] bg-no-repeat bg-[right_0.5rem_center] bg-[length:1rem_1rem]">
                            <option value="">-- Select Field --</option>
                            @foreach($columns as $key => $label)
                                <option value="{{ $key }}" {{ request('sort_by') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Sort direction -->
                        <select name="sort_direction"
                            class="border rounded px-2 py-1 pr-8 text-sm appearance-none bg-white bg-[url('data:image/svg+xml;utf8,<svg fill=\'black\' height=\'24\' viewBox=\'0 0 24 24\' width=\'24\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M7 10l5 5 5-5z\'/></svg>')] bg-no-repeat bg-[right_0.5rem_center] bg-[length:1rem_1rem]">
                            <option value="asc" {{ request('sort_direction') === 'asc' ? 'selected' : '' }}>Asc</option>
                            <option value="desc" {{ request('sort_direction') === 'desc' ? 'selected' : '' }}>Desc</option>
                        </select>

                        <button type="submit"
                            class="bg-blue-600 text-white text-sm px-3 py-1 rounded hover:bg-blue-700">
                            Apply Sort
                        </button>
                    </form>

                <div class="overflow-x-auto">
                    <table class="min-w-[1000px] table-auto text-sm text-left whitespace-nowrap">
                        <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-3 py-2">ACTION</th>
                            @foreach($columns as $key => $label)
                                @php
                                    $isSorted = $sortBy === $key;
                                    $newDir = $isSorted && $sortDir === 'asc' ? 'desc' : 'asc';
                                    $arrow = $isSorted ? ($sortDir === 'asc' ? '↑' : '↓') : '';
                                @endphp
                                <th class="px-3 py-2">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => $key, 'sort_direction' => $newDir]) }}"
                                       class="hover:underline">
                                        {{ $label }} {!! $arrow !!}
                                    </a>
                                </th>
                            @endforeach
                        </tr>
                        </thead>

                            <script>
                                document.getElementById('autoSearch').addEventListener('input', function () {
                                    clearTimeout(this.delay);
                                    this.delay = setTimeout(() => {
                                        this.form.submit();
                                    }, 500);
                                });
                            </script>

                        <tbody class="divide-y divide-gray-200">
                       @forelse($paginated as $index => $row)
                            @php
                                $dday = intval($row['DDAY'] ?? 0);
                                $rowClass = $dday < 0 ? 'bg-red-100' : '';
                            @endphp
                            <tr class="{{ $rowClass }}">

                                <td class="px-3 py-2">
                                    @if (auth()->user()->role === 'admin')
                                        @if (strtoupper($row['STATS'] ?? '') === 'CRTD')
                                            <button type="button"
                                                    class="inline-block px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700"
                                                    onclick="toggleEditForm({{ $index }})">Edit WC</button>
                                        @endif
                                        @if (in_array(strtoupper($row['STATS'] ?? ''), ['REL', 'CRTD']))
                                            <button type="button"
                                                    class="inline-block px-3 py-1 bg-yellow-600 text-white rounded text-sm hover:bg-green-700"
                                                    onclick="toggleEditPvForm({{ $index }})">Edit PV</button>
                                            @endif
                                        @if (!empty($row['PLNUM']))
                                            <a href="{{ route('convert.order.direct', ['plnum' => $row['PLNUM'], 'auart' => $row['AUART']]) }}"
                                                    onclick="return confirm('Yakin ingin convert planned order {{ $row['PLNUM'] }}?')"
                                                    class="inline-block px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                                                        Convert
                                            </a>
                                        @endif
                                    @endif
                                        @if (strtoupper($row['STATS'] ?? '') === 'CRTD')
                                            <a href="{{ route('release.order.direct', ['aufnr' => $row['AUFNR']]) }}"
                                                    onclick="return confirm('Yakin ingin release order {{ $row['AUFNR'] }}?')"
                                                    class="inline-block px-3 py-1 bg-orange-600 text-white rounded text-sm hover:bg-orange-700">
                                                        Release
                                            </a>
                                        @endif
                                </td>
                                @foreach(array_keys($columns) as $key)
                                    <td class="px-3 py-2">
                                        @if ($key === 'MATNR')
                                            {{ ltrim($row[$key] ?? '-', '0') }}
                                        @else
                                            {{ $row[$key] ?? '-' }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- Edit WC Form --}}
                            <tr id="edit-form-{{ $index }}" class="hidden bg-gray-50">
                                <td colspan="{{ count($columns) + 1 }}">
                                    <form method="POST" action="{{ route('sap.save_edit') }}">
                                        @csrf
                                        <input type="hidden" name="aufnr" value="{{ $row['AUFNR'] }}">
                                        <div class="flex gap-4 p-3 items-center">
                                            <label class="text-sm">Operation:</label>
                                            <input type="text" name="operation" value="{{ $row['VORNR'] ?? '' }}"
                                                class="border px-2 py-1 rounded w-32">
                                            <label class="text-sm">Work Center:</label>
                                            <input type="text" name="work_center" value="{{ $row['ARBPL'] ?? '' }}"
                                                class="border px-2 py-1 rounded w-48">
                                            <button type="submit"
                                                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                                Save WC
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                            {{-- Edit PV Form --}}
                            <tr id="edit-pv-form-{{ $index }}" class="hidden bg-gray-50">
                                <td colspan="{{ count($columns) + 1 }}">
                                    <form method="POST" action="{{ route('sap.save_edit_pv') }}">
                                        @csrf
                                        <input type="hidden" name="aufnr" value="{{ $row['AUFNR'] }}">
                                        <div class="flex gap-4 p-3 items-center">
                                            <label class="text-sm">Prod Version:</label>
                                            <input type="text" name="prod_version" value="{{ $row['VERID'] ?? '' }}"
                                                class="border px-2 py-1 rounded w-48">
                                            <button type="submit"
                                                    class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                                Save PV
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + 1 }}" class="text-center py-3 text-gray-500">
                                    Not found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($paginated instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-4">
                        {{ $paginated->withQueryString()->links() }}
                    </div>
                @endif

                {{-- Convert Mess Form --}}
                <div id="convert-mess-form" class="mt-6 hidden border-t border-gray-300 pt-4">
                    <h3 class="text-lg font-semibold mb-2">Convert Planned Orders</h3>
                    <form method="POST" action="{{ route('convert.order.massal') }}">
                        @csrf
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">Select PLNUM</label>
                                <div>
                                    <label class="text-sm font-medium text-gray-700 mr-2">
                                        <input type="checkbox" id="checkAllConvert" class="mr-1"> Check All
                                    </label>
                                </div>
                            </div>
                            <div class="space-y-2 max-h-64 overflow-y-auto border p-2 rounded-md">
                                @foreach ($paginated->whereNotNull('PLNUM')->unique('PLNUM') as $item)
                                    <label class="block text-sm text-gray-800">
                                        <input type="checkbox"
                                            name="selected_plnums[]"
                                            value="{{ $item['PLNUM'] }}|{{ $item['AUART'] }}"
                                            class="mr-2 convert-checkbox">
                                        {{ $item['PLNUM'] }} | {{ $item['MATNR'] }} | {{ $item['MAKTX'] }} | {{ $item['AUART'] }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <button type="submit"
                                onclick="return confirm('Yakin ingin convert selected planned orders?')"
                                class="px-4 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                                Convert Selected
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Change Mess Form --}}
                <div id="change-mess-form" class="mt-6 hidden border-t border-gray-200 pt-4">
                    <h3 class="text-lg font-semibold mb-2">Change Mess Form</h3>
                    <form method="POST" action="{{ route('edit.save.massal') }}">
                        @csrf
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">Select Mess Codes</label>
                                <div>
                                    <label class="text-sm font-medium text-gray-700 mr-2">
                                        <input type="checkbox" id="checkAll" class="mr-1"> Check All
                                    </label>
                                </div>
                            </div>
                            <div class="space-y-2 max-h-64 overflow-y-auto border p-2 rounded-md">
                                @foreach ($crtdOptions as $item)
                                    <label class="block text-sm text-gray-800">
                                        <input type="checkbox"
                                            name="selected_rows[]"
                                            value="{{ $item['AUFNR'] }}|{{ $item['VORNR'] }}"
                                            class="mr-2 mess-checkbox">
                                        {{ $item['AUFNR'] }} | {{ $item['ARBPL'] }} | {{ $item['VORNR'] }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="work_center" class="block text-sm font-medium text-gray-700">New Work Center/ New PV</label>
                            <input type="text" name="work_center" id="work_center"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" name="action" value="change_wc"
                                class="px-4 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Change WC
                            </button>

                            <button type="submit" name="action" value="change_pv"
                                class="px-4 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                Change PV
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

        {{-- Script --}}
        <script>
                function toggleEditPvForm(index) {
                    const row = document.getElementById(`edit-pv-form-${index}`);
                    row.classList.toggle('hidden');
                }
                function toggleMessForm(event) {
                    event.preventDefault();
                    const form = document.getElementById('change-mess-form');
                    form.classList.toggle('hidden');
                    // Check All
                    document.getElementById('checkAll').addEventListener('change', function () {
                        const checkboxes = document.querySelectorAll('.mess-checkbox');
                        checkboxes.forEach(cb => cb.checked = this.checked);
                    });

                // Unselect All
                function unselectAll() {
                    document.getElementById('checkAll').checked = false;
                    const checkboxes = document.querySelectorAll('.mess-checkbox');
                    checkboxes.forEach(cb => cb.checked = false);
                }
            }

            function toggleConvertForm(event) {
            event.preventDefault();
            const form = document.getElementById('convert-mess-form');
            form.classList.toggle('hidden');

            // Check All
            document.getElementById('checkAllConvert').addEventListener('change', function () {
                const checkboxes = document.querySelectorAll('.convert-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        }


                function toggleEditForm(index) {
                    const row = document.getElementById(`edit-form-${index}`);
                    row.classList.toggle('hidden');
                }
                        setTimeout(() => {
                    document.querySelectorAll('.alert-auto-hide').forEach(el => {
                        el.style.display = 'none';
                    });
                }, 4000);
        </script>
</x-app-layout>
