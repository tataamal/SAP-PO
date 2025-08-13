<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                DATA {{ strtoupper($category) }} - PLANT {{ $plant }}
            </h2>
            <a href="{{ route('manufact.show', ['plant' => $plant]) }}"
               class="inline-block px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                ← Back
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @foreach ($data as $item)
                    <form method="POST" action="{{ route('manufact.sync', $item->code) }}" id="form-{{ $item->code }}">
                        @csrf
                        <button type="submit" class="w-full text-left">
                            <div class="block bg-white border border-gray-200 rounded-lg shadow px-6 py-4 hover:bg-blue-50 transition">
                                <div class="text-md font-semibold text-gray-800 uppercase">
                                    {{ $item->bagian ?? '-' }}
                                </div>
                                <div class="text-sm text-gray-600">CODE: {{ $item->code ?? '-' }}</div>
                            </div>
                        </button>
                    </form>
                @endforeach

            </div>
        </div>
    </div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const forms = document.querySelectorAll('form[id^="form-"]');
        forms.forEach(form => {
            form.addEventListener('submit', function () {
                const loader = document.getElementById('global-loading');
                if (loader) {
                    loader.style.display = 'flex';
                    loader.style.opacity = '1';
                }
            });
        });
    });
</script>

</x-app-layout>
