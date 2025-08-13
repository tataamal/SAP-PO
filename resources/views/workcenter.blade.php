<x-app-layout> 
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Menu') }}
            </h2>
            <div class="flex items-center gap-4">
                @if(session('sap_user'))
                    <span class="text-green-700 text-sm">
                        SAP Login as: <strong>{{ session('sap_user') }}</strong>
                    </span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('workcenter.submitData') }}" id="workcenter-form">
                        @csrf
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-1/2">
                                <label for="plant" class="block text-sm font-medium text-gray-700">Plant</label>
                                <select name="plant" id="plant" class="w-full border border-gray-300 rounded-md p-2 pr-8 appearance-none relative bg-white" required>
                                    <option value="">Choose Plant</option>
                                    @foreach($plants as $plant)
                                        <option value="{{ $plant }}">{{ $plant }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="w-1/3 text-right">
                                <label class="block text-sm font-medium text-gray-700">Search Workcenter (Code)</label>
                                <input type="text" id="search-workcenter" placeholder="Contoh: WC001" class="border border-gray-300 rounded-md p-2 w-full">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="category_initial" class="block text-sm font-medium text-gray-700">Category Initial</label>
                            <select name="category_initial" id="category_initial" class="w-full border border-gray-300 rounded-md p-2"></select>
                        </div>

                        <div class="mb-4">
                            <label for="category_detail" class="block text-sm font-medium text-gray-700">Category Detail</label>
                            <select name="category_detail" id="category_detail" class="w-full border border-gray-300 rounded-md p-2"></select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Workcenters</label>
                            <div id="workcenter-checkboxes" class="space-y-2 max-h-64 overflow-y-auto border p-2 rounded-md"></div>
                        </div>

                        <input type="hidden" name="workcenter" id="workcenter-hidden">

                        <div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 transition">
                                Submit
                            </button>
                                <button type="button" onclick="openModal()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                + Add Workcenter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Tambah Workcenter -->
    <div id="workcenterModal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white w-full max-w-md p-6 rounded shadow-lg relative">
            <h3 class="text-lg font-semibold mb-4">Add New Workcenter</h3>
            <form method="POST" action="{{ route('workcenter.form.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700">Code</label>
                    <input type="text" name="code" class="w-full border border-gray-300 p-2 rounded" required>
                    @error('code') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" name="description" class="w-full border border-gray-300 p-2 rounded" required>
                    @error('description') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700">Categories</label>
                    <input type="text" name="categories" class="w-full border border-gray-300 p-2 rounded" required>
                    @error('categories') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Plant</label>
                    <input type="text" name="plant" class="w-full border border-gray-300 p-2 rounded" required>
                    @error('plant') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
                </div>
            </form>
            <button class="absolute top-2 right-2 text-gray-600 hover:text-black" onclick="closeModal()">✕</button>
        </div>
    </div>

    <script>
        const allData = @json($workcenters);

        const plantSelect = document.getElementById('plant');
        const categoryInitialSelect = document.getElementById('category_initial');
        const categoryDetailSelect = document.getElementById('category_detail');
        const workcenterContainer = document.getElementById('workcenter-checkboxes');
        const hiddenInput = document.getElementById('workcenter-hidden');
        const form = document.getElementById('workcenter-form');
        const searchInput = document.getElementById('search-workcenter');

        let currentMatchedWCs = [];

        function populate(select, items, placeholder = 'Pilih') {
            select.innerHTML = `<option value="">${placeholder}</option>`;
            items.forEach(i => {
                const opt = document.createElement('option');
                opt.value = i;
                opt.textContent = i;
                select.appendChild(opt);
            });
        }

        function renderWorkcenters(list) {
            workcenterContainer.innerHTML = list.map(wc => `
                <label class="block">
                    <input type="checkbox" value="${wc.code}" class="workcenter-cb mr-2">
                    ${wc.code} - ${wc.description}
                </label>
            `).join('');
        }

        function resetDropdowns() {
            plantSelect.value = '';
            populate(categoryInitialSelect, []);
            populate(categoryDetailSelect, []);
            workcenterContainer.innerHTML = '';
        }

        searchInput.addEventListener('input', function () {
            const keyword = this.value.trim().toLowerCase();
            if (!keyword) {
                resetDropdowns();
                return;
            }

            const match = allData.find(wc => wc.code.toLowerCase().includes(keyword));
            if (match) {
                plantSelect.value = match.plant;

                const categoryParts = (match.categories || '').split(',').map(c => c.trim());
                if (categoryParts.length) {
                    const [initial, ...detail] = categoryParts[0].split(' ');
                    populate(categoryInitialSelect, [initial]);
                    categoryInitialSelect.value = initial;
                    const detailText = detail.join(' ');
                    populate(categoryDetailSelect, [detailText]);
                    categoryDetailSelect.value = detailText;
                }

                currentMatchedWCs = allData.filter(wc => wc.code.toLowerCase().includes(keyword));
                renderWorkcenters(currentMatchedWCs);
            } else {
                resetDropdowns();
            }
        });

        plantSelect.addEventListener('change', () => {
            const plant = plantSelect.value;
            const initialCategories = [...new Set(
                allData
                    .filter(d => d.plant === plant)
                    .flatMap(d => (d.categories || '').split(','))
                    .map(cat => cat.trim().split(' ')[0])
                    .filter(Boolean)
            )];
            populate(categoryInitialSelect, initialCategories, 'Choose Category');
            populate(categoryDetailSelect, [], 'Choose Detail');
            workcenterContainer.innerHTML = '';
        });

        categoryInitialSelect.addEventListener('change', () => {
            const plant = plantSelect.value;
            const initial = categoryInitialSelect.value;
            const detailCategories = [...new Set(
                allData
                    .filter(d => d.plant === plant)
                    .flatMap(d => (d.categories || '').split(','))
                    .map(cat => cat.trim())
                    .filter(cat => cat.startsWith(initial))
                    .map(cat => cat.split(' ').slice(1).join(' '))
                    .filter(Boolean)
            )];
            populate(categoryDetailSelect, detailCategories, 'Pilih Detail');
            workcenterContainer.innerHTML = '';
        });

        categoryDetailSelect.addEventListener('change', () => {
            const plant = plantSelect.value;
            const initial = categoryInitialSelect.value;
            const detail = categoryDetailSelect.value;

            currentMatchedWCs = allData.filter(d => {
                const categories = (d.categories || '').split(',').map(c => c.trim());
                return d.plant === plant && categories.some(cat => {
                    const [first, ...rest] = cat.split(' ');
                    return first === initial && rest.join(' ') === detail;
                });
            });

            renderWorkcenters(currentMatchedWCs);
        });

        form.addEventListener('submit', function(e) {
            const checked = [...document.querySelectorAll('.workcenter-cb:checked')];
            const values = checked.map(cb => cb.value);
            hiddenInput.value = values.join(',');
        });

        function openModal() {
            const modal = document.getElementById('workcenterModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            const modal = document.getElementById('workcenterModal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }
    </script>
</x-app-layout>
