<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                MENU
            </h2>
            <a href="#" onclick="toggleModal(true)"
               class="inline-block px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition">
                + Tambah Data
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @if (auth()->check() && in_array(auth()->user()->role, ['admin', 'user']))
                    @forelse ($plants as $plant)
                        <a href="{{ route('manufact.show', $plant->plant) }}"
                           class="flex items-center justify-between bg-white hover:bg-blue-50 border border-gray-200 rounded-lg shadow px-6 py-4 transition duration-200">
                            <div>
                                <div class="text-lg font-semibold text-gray-800 uppercase">
                                    {{ $plant->plant }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    List data plant {{ $plant->plant }}
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-3 text-center text-gray-500">
                            Tidak ada data plant ditemukan.
                        </div>
                    @endforelse
                @endif
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div id="modalForm" class="fixed inset-0 items-center justify-center bg-black bg-opacity-50 hidden z-50">
        <div class="bg-white w-full max-w-md mx-auto rounded-lg shadow-lg p-6">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Tambah Data Production Mapping</h2>
            <form action="{{ route('production.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 uppercase">CODE</label>
                    <input type="text" name="code" required class="w-full border-gray-300 rounded mt-1">
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 uppercase">CATEGORIES</label>
                    <input type="text" name="categories" required class="w-full border-gray-300 rounded mt-1">
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 uppercase">BAGIAN</label>
                    <input type="text" name="bagian" class="w-full border-gray-300 rounded mt-1">
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 uppercase">PLANT</label>
                    <input type="text" name="plant" required class="w-full border-gray-300 rounded mt-1">
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button"
                            onclick="toggleModal(false)"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Toggle Script --}}
    <script>
        function toggleModal(show) {
            const modal = document.getElementById('modalForm');
            if (show) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            } else {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }
        }
    </script>
</x-app-layout>
