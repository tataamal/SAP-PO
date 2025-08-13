@php            
use Carbon\Carbon;
use Illuminate\Support\Str;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('List Data') }}
        </h2>
    </x-slot>

<div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-6">
                <!-- === T_DATA2 === -->
                <h3 class="text-lg font-semibold mb-4">Result</h3>
                <table class="min-w-full table-auto text-sm text-left whitespace-nowrap border">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">PLANT</th>
                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">WORKCENTER</th>
                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">DESC WORKCENTER</th>
                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">TOTAL PRO</th>
                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">END DATE</th>
                        </tr>
                    </thead>

                   <tbody>
                        @foreach($t_data2 as $item)
                            <tr class="hover:bg-blue-100 cursor-pointer" onclick="toggleTData3('{{ $item->ARBPL }}')">
                                <td class="px-2 py-1 border">{{ $item->WERKS }}</td>
                                <td class="px-2 py-1 border">{{ $item->ARBPL }}</td>
                                <td class="px-2 py-1 border">{{ $item->KTEXT }}</td>
                                <td class="px-2 py-1 border text-blue-600 underline cursor-pointer"
                                    onclick="event.stopPropagation(); showAllAufnrByArbpl('{{ $item->ARBPL }}')">
                                    {{ $item->jumlah_aufnr ?? 0 }}
                                </td>
                                <td class="px-2 py-1 border">
                                    @php
                                        try {
                                            $formattedSSSLD2 = \Carbon\Carbon::createFromFormat('Ymd', $item->SSSLD2)->format('d-m-Y');
                                        } catch (\Exception $e) {
                                            $formattedSSSLD2 = $item->SSSLD2;
                                        }
                                    @endphp
                                    {{ $formattedSSSLD2 }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>

                <!-- === T_DATA3 + T_DATA1 per ARBPL === -->
                @foreach($t_data2 as $t2)
                    @php $arbpl = $t2->ARBPL; @endphp
                    <div id="tdata3-{{ $arbpl }}" class="hidden mb-6">
                    <h4 class="font-bold text-md mb-2">SO from : {{ $arbpl }}</h4>
                    <div class="overflow-y-auto max-h-60 mb-4 border rounded">
                        <table class="min-w-full table-auto text-sm border mb-4">
                            <thead class="bg-gray-100 sticky-header">
                                <tr>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">No.</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">NO SO</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">SO ITEM</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">MATFG</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">MAKFG</th>
                                    <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">EDATU</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @foreach($t_data3 as $t3)
                                    @php
                                        $t1match = $t_data1->first(fn($t1) =>
                                            $t1->KDAUF === $t3->KDAUF &&
                                            $t1->KDPOS === $t3->KDPOS &&
                                            $t1->ARBPL === $arbpl
                                        );
                                    @endphp
                                    @if($t1match)
                                        <tr id="tdata3row-{{ $arbpl }}_{{ $t3->KDAUF }}_{{ $t3->KDPOS }}" 
                                            class="cursor-pointer hover:bg-gray-100"
                                            onclick="toggleTData1('{{ $arbpl }}', '{{ $t3->KDAUF }}', '{{ $t3->KDPOS }}')">
                                            <td class="border px-2 py-1 text-center">{{ $no++ }}</td> {{-- Nomor urut --}}
                                            <td class="border px-2 py-1">{{ $t3->KDAUF }}</td>
                                            <td class="border px-2 py-1">
                                                {{ Str::contains($t3->KDPOS, '.') ? $t3->KDPOS : ltrim($t3->KDPOS, '0') }}
                                            </td>
                                            <td class="border px-2 py-1">
                                                {{ Str::contains($t3->MATFG, '.') ? $t3->MATFG : ltrim($t3->MATFG, '0') }}
                                            </td>
                                            <td class="border px-2 py-1">{{ $t3->MAKFG }}</td>
                                            <td class="px-2 py-1">{{ \Carbon\Carbon::parse($item->EDATU)->format('d-m-Y') ?? 'N/A' }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>

                        </table>
                    </div>

                        <!-- === T_DATA1 Detail (Group by KDAUF+KDPOS) === -->
                        @php
                            $groupedKeys = $t_data1->where('ARBPL', $arbpl)
                                ->map(fn($item) => $item->KDAUF . '_' . $item->KDPOS)
                                ->unique();
                        @endphp

                        @foreach($groupedKeys as $key)
                            @php
                                [$kdauf, $kdpos] = explode('_', $key);
                                $filtered = $t_data1->where('KDAUF', $kdauf)->where('KDPOS', $kdpos)->where('ARBPL', $arbpl);
                            @endphp

                            <div id="tdata1-{{ $arbpl }}_{{ $kdauf }}_{{ $kdpos }}" class="hidden mb-4">
                                <h5 class="font-semibold mb-2">PRO from (SO: {{ $kdauf }}, SO ITEM: {{ $kdpos }})</h5>
                                <table class="min-w-full table-auto text-sm border">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">No.</th>
                                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">NO PRO</th>
                                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">ACTION</th>
                                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">END DATE</th>
                                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">STATUS</th>
                                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">DESC MATERIAL</th>
                                            <th class="px-3 py-2 border bg-blue-100 text-blue-900 font-semibold">WORKCENTER</th>
                                        </tr>
                                    </thead>
                                   <tbody>
                                        @php $no = 1; @endphp
                                        @foreach($filtered as $t1)
                                            @php
                                                $statusRaw = strtoupper($t1->STATS ?? '');
                                                $statusText = match (true) {
                                                    str_starts_with($statusRaw, 'PCNF'), str_starts_with($statusRaw, 'CNF') => 'On Proccess',
                                                    str_starts_with($statusRaw, 'REL') => 'Release',
                                                    str_starts_with($statusRaw, 'CRTD') => 'Created',
                                                    default => $statusRaw,
                                                };

                                                try {
                                                    $ssldDate = Carbon::createFromFormat('Ymd', $t1->SSSLD);
                                                    $isLate = $ssldDate->lt(Carbon::today());
                                                    $formattedSSSLD = $ssldDate->format('d-m-Y');
                                                } catch (\Exception $e) {
                                                    $isLate = false;
                                                    $formattedSSSLD = $t1->SSSLD;
                                                }

                                                $statusClass = $isLate ? 'bg-red-100 text-red-800 font-semibold' : '';
                                            @endphp

                                            <tr>
                                                <td class="border px-2 py-1 text-center">{{ $no++ }}</td> 
                                                <td class="border px-2 py-1">{{ $t1->AUFNR }}</td>
                                                <td class="border px-2 py-1 text-center">
                                                    @if(strtoupper($t1->STATS) === 'CRTD')
                                                    <a href="{{ route('release.order.direct', ['aufnr' => $t1->AUFNR]) }}"
                                                    onclick="return confirm('Yakin ingin release order {{ $t1->AUFNR }}?')"
                                                    class="inline-block px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                                                        🏳️
                                                    </a>
                                                    @endif
                                                </td>
                                                <td class="border px-2 py-1">{{ $formattedSSSLD }}</td>
                                                <td class="border px-2 py-1 {{ $statusClass }}">{{ $statusText }}</td>
                                                <td class="border px-2 py-1">{{ $t1->MAKTX }}</td>
                                                <td class="border px-2 py-1">{{ $t1->ARBPL }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach

                    </div>
                @endforeach

                <div class="mt-6">
                    <a href="{{ route('workcenter') }}" class="text-blue-600 hover:underline">&larr; Kembali ke Workcenter</a>
                </div>
            </div>
        </div>
    </div>

    <style>
    .sticky-header th {
        position: sticky;
        top: 0;
        background-color: 219 234 254; /* sesuai warna bg-blue-100 */
        z-index: 10;
    }
    </style>
    <script>
            let activeTData3Key = null;

function toggleTData3(arbpl) {
    const container = document.getElementById('tdata3-' + arbpl);

    if (!container) return;

    const isVisible = !container.classList.contains('hidden');

    // Sembunyikan semua T_DATA3 dan T_DATA1
    document.querySelectorAll("[id^='tdata3-']").forEach(el => el.classList.add('hidden'));
    document.querySelectorAll("[id^='tdata1-']").forEach(el => el.classList.add('hidden'));

    if (!isVisible) {
        // Tampilkan container dan baris T_DATA3-nya
        container.classList.remove('hidden');
        container.querySelectorAll("tr[id^='tdata3row-" + arbpl + "_']").forEach(row => {
            row.classList.remove('hidden');
        });

        activeTData3Key = null;
    }
}


    function toggleTData1(arbpl, kdauf, kdpos) {
        const key = arbpl + '_' + kdauf + '_' + kdpos;

        // Jika klik baris yang sama lagi → reset tampilan semula
        if (activeTData3Key === key) {
            // Show all T_DATA3 rows
            document.querySelectorAll("tr[id^='tdata3row-" + arbpl + "_']").forEach(row => {
                row.classList.remove('hidden');
            });
            // Hide all T_DATA1
            document.querySelectorAll("[id^='tdata1-" + arbpl + "_']").forEach(el => el.classList.add('hidden'));
            // Reset key
            activeTData3Key = null;
        } else {
            // Hide all T_DATA3 rows
            document.querySelectorAll("tr[id^='tdata3row-" + arbpl + "_']").forEach(row => {
                row.classList.add('hidden');
            });
            // Show clicked row only
            const clickedRow = document.getElementById('tdata3row-' + arbpl + '_' + kdauf + '_' + kdpos);
            if (clickedRow) clickedRow.classList.remove('hidden');

            // Hide all T_DATA1
            document.querySelectorAll("[id^='tdata1-" + arbpl + "_']").forEach(el => el.classList.add('hidden'));
            // Show only this one
            const tdata1 = document.getElementById('tdata1-' + arbpl + '_' + kdauf + '_' + kdpos);
            if (tdata1) tdata1.classList.remove('hidden');

            // Update active
            activeTData3Key = key;
        }
    }
        function showAllAufnrByArbpl(arbpl) {
        // Sembunyikan semua T_DATA3 dan T_DATA1
        document.querySelectorAll("[id^='tdata3-']").forEach(el => el.classList.add('hidden'));
        document.querySelectorAll("[id^='tdata1-']").forEach(el => el.classList.add('hidden'));

        // Tampilkan tdata3 container untuk ARBPL tersebut
        const container = document.getElementById('tdata3-' + arbpl);
        if (container) {
            container.classList.remove('hidden');
        }

        // Tampilkan semua T_DATA1 dari ARBPL (berdasarkan AUFNR saja)
        document.querySelectorAll("[id^='tdata1-" + arbpl + "_']").forEach(el => el.classList.remove('hidden'));

        // Reset active key biar tidak konflik
        activeTData3Key = null;
    }

    </script>

</x-app-layout>
