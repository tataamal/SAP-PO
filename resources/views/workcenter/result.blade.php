<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                SAP Result
            </h2>
            <a href="{{ route('workcenter') }}"
            class="inline-block px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                ← Back
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-4">
                <p class="mb-4">
                    <strong>Plant:</strong> {{ $plant }} |
                    <strong>Workcenter:</strong> {{ $workcenter }}
                </p>

                <div class="overflow-x-auto">
                    <table class="min-w-[1000px] table-auto text-sm text-left whitespace-nowrap">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                @if(count($paginator) > 0)
                                    @foreach(array_keys($paginator[0]) as $key)
                                        <th class="px-3 py-2">{{ $key }}</th>
                                    @endforeach
                                @endif
                                <th class="px-3 py-2">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($paginator as $row)
                                <tr>
                                    @foreach($row as $value)
                                        <td class="px-3 py-2">{{ $value }}</td>
                                    @endforeach
                                    <td class="px-3 py-2">
                                        @if(isset($row['ARBPL']))
                                            <a href="{{ route('workcenter.result_detail', ['workcenter' => $row['ARBPL'],'plant' => $plant,'per_page' => request('per_page', '10')]) }}"class="inline-block px-3 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700">
                                               DETAIL
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center text-gray-500 py-4">
                                        Tidak ada data tersedia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Controls --}}
                @if(is_a($paginator, \Illuminate\Pagination\LengthAwarePaginator::class))
                    <div class="mt-4">
                        {{ $paginator->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
