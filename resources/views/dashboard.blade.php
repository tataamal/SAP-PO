<!-- dashboard.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    @if (auth()->user()->role === 'ceo')
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @if (auth()->check())
                    @if (in_array(auth()->user()->role, ['admin', 'user', 'ceo']))
                        <!-- Card 2 -->
<a href="{{ route('purchase-order.fetch') }}"
   class="flex items-center justify-between bg-white hover:bg-blue-50 border border-gray-200 rounded-lg shadow px-6 py-4 transition duration-200">
    <div>
        <div class="text-lg font-semibold">Approval</div>
        <div class="text-gray-500 text-sm">List PO not yet released</div>
    </div>
</a>

                    @endif
                @endif
                </div>
            </div>
        </div>
    @endif
    @if (in_array(auth()->user()->role, ['admin', 'user']))
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @if (auth()->check())
                    @if (in_array(auth()->user()->role, ['admin', 'user', 'ceo']))
                        <!-- Card 2 -->
                        <a href="/menu"
                        class="flex items-center justify-between bg-white hover:bg-blue-50 border border-gray-200 rounded-lg shadow px-6 py-4 transition duration-200">
                            <div>
                                <div class="text-lg font-semibold">Manufacturing</div>
                                <div class="text-gray-500 text-sm">All Plant</div>
                            </div>
                        </a>
                    @endif
                @endif
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
