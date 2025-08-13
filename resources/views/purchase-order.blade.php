<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Approve Purchase Order') }}
            </h2>
            <a href="{{ url('/dashboard') }}">
                <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded">
                    ⬅️BACK
                </button>
            </a>
        </div>
    </x-slot>
    <div class="py-12"> 
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
<!-- Card SEMARANG -->
                <a href="{{ route('po.menu', ['lokasi' => 'semarang']) }}"
                   class="flex items-center justify-between bg-white hover:bg-blue-50 border border-gray-200 rounded-lg shadow px-6 py-6 transition duration-200">
                    <div>
                        <div class="text-xl font-semibold text-gray-800">SEMARANG</div>
                            <div class="text-sm text-gray-500 mt-1">
                                Total PO:
                                <span class="text-xl font-bold text-blue-600">{{ $totalSemarang }}</span>
                            </div>
                    </div>
                    <div class="w-[88px] h-[88px] ml-4 flex-shrink-0">
                        <img src="{{ asset('images/semarang.png') }}" alt="Semarang" class="w-full h-full object-contain">
                    </div>
                </a>
                
                <!-- Card SURABAYA -->
                <a href="{{ route('po.menu', ['lokasi' => 'surabaya']) }}"
                   class="flex items-center justify-between bg-white hover:bg-blue-50 border border-gray-200 rounded-lg shadow px-6 py-6 transition duration-200">
                    <div>
                        <div class="text-xl font-semibold text-gray-800">SURABAYA</div>
                                    <div class="text-sm text-gray-500 mt-1">
                                        Total PO:
                                        <span class="text-xl font-bold text-blue-600">{{ $totalSurabaya }}</span>
                                    </div>
                    </div>
                    <div class="w-[88px] h-[88px] ml-4 flex-shrink-0">
                        <img src="{{ asset('images/surabaya.png') }}" alt="Surabaya" class="w-full h-full object-contain">
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Function untuk tombol Back
        function goBack() {
            window.history.back();
        }
    </script>
</x-app-layout>