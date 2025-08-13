<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $title ?? 'Daftar Purchase Order' }}
        </h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($dataPO->isEmpty())
            <div class="text-center text-gray-500 py-10">Tidak ada data purchase order untuk filter ini.</div>
        @else
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left">EBELN</th>
                            <th class="px-4 py-3 text-left">BADAT</th>
                            <th class="px-4 py-3 text-left">BEDAT</th>
                            <th class="px-4 py-3 text-left">BSART</th>
                            <th class="px-4 py-3 text-left">NAME1</th>
                            <th class="px-4 py-3 text-right">TOTPR</th>
                            <th class="px-4 py-3 text-left">WAERK</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($dataPO as $po)
                            <tr>
                                <td class="px-4 py-2 font-mono">{{ $po->EBELN }}</td>
                                <td class="px-4 py-2">{{ $po->BADAT }}</td>
                                <td class="px-4 py-2">{{ $po->BEDAT }}</td>
                                <td class="px-4 py-2">{{ $po->BSART }}</td>
                                <td class="px-4 py-2">{{ $po->NAME1 }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($po->TOTPR, 2) }}</td>
                                <td class="px-4 py-2">{{ $po->WAERK }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
