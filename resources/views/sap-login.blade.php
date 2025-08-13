<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Login SAP') }}
            </h2>

            @php
                $user = Auth::user();
                $backRoute = route('report');

                if ($user && $user->role === 'ceo') {
                    $backRoute = route('dashboard');
                }
            @endphp

            <a href="{{ $backRoute }}"
            class="inline-block px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                ← Back
            </a>
        </div>
    </x-slot>


    <div class="py-12">
        <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
            <form method="POST" action="{{ route('sap.login.submit') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">SAP Username</label>
                    <input type="text" name="sap_username" class="w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">SAP Password</label>
                    <input type="password" name="sap_password" class="w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Login
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
