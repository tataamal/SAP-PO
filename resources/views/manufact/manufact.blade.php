<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Data Plant {{ $plant }}
            </h2>
            <a href="{{ route('menu') }}"
                class="inline-block px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                ← Back
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @forelse ($categories as $item)
                    <a href="{{ route('manufact.detail', [$plant, $item->categories]) }}"
                    class="bg-white border border-gray-200 rounded-lg shadow px-6 py-4 hover:bg-blue-50 transition block">
                        <div class="text-md font-semibold text-gray-800 uppercase">
                            {{ ucfirst($item->categories) }}
                        </div>
                        <div class="text-sm text-gray-600">
                            Total bagian: {{ $item->total }}
                        </div>
                    </a>
                @empty
                    <div class="col-span-3 text-center text-gray-500">
                        Tidak ada data untuk plant {{ $plant }}
                    </div>
                @endforelse

            </div>
        </div>
    </div>
</x-app-layout>
