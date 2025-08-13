<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reports') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <a href="/report-pp"
                class="flex items-center justify-between bg-white hover:bg-blue-50 border border-gray-200 rounded-lg shadow px-6 py-4 transition duration-200">
                    <div>
                        <div class="text-lg font-semibold text-gray-800">MM</div>
                        <div class="text-sm text-gray-500">Material Management</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
