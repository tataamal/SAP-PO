<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Approval PO') }}
            </h2>
            <a href="{{ route('report') }}"
                class="inline-block px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                ← Back
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @if (auth()->check())
                @if (in_array(auth()->user()->role, ['ceo']))
                    <!-- Card 2 -->
                    <a href="/purchase-order"
                    class="flex items-center justify-between bg-white hover:bg-blue-50 border border-gray-200 rounded-lg shadow px-6 py-4 transition duration-200">
                        <div>
                            <div class="text-lg font-semibold">Purchase Order</div>
                            <div class="text-gray-500 text-sm">List PO not yet released</div>
                        </div>
                    </a>
                @endif
            @endif
            </div>
        </div>
    </div>
</x-app-layout>
