<!-- app.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* CSS untuk Purchase Order Table */
        .po-row-custom {
            background-color: #dbeafe !important; /* Biru muda */
        }
        .po-row-custom:hover {
            background-color: #bfdbfe !important; /* Biru lebih terang saat hover */
        }
        .po-row-custom td {
            background-color: inherit !important;
        }
        .detail-row-custom {
            background-color: #ffffff !important; /* Putih untuk detail */
        }
        .detail-row-custom td {
            background-color: inherit !important;
        }

        /* CSS untuk Header Navy - Updated for better sticky behavior */
        .table-header-navy {
            background-color: #1e3a8a !important; /* Navy blue */
            background-image: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            position: sticky !important;
            top: 0 !important;
            z-index: 20 !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
        }
        .table-header-navy th {
            color: #ffffff !important; /* White text */
            font-weight: 600 !important;
            border-bottom: 2px solid #1e40af !important;
            background-color: inherit !important;
        }
        .table-header-navy .text-gray-400 {
            color: #e5e7eb !important; /* Light gray untuk icon */
        }
        .table-header-navy .hover\:text-gray-600:hover {
            color: #ffffff !important; /* White on hover */
        }

        /* Custom Checkbox Styling - Berbentuk Kotak */
        .custom-checkbox {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            width: 18px;
            height: 18px;
            border: 2px solid #d1d5db;
            border-radius: 3px !important; /* Membuat bentuk kotak dengan sudut sedikit melengkung */
            background-color: #ffffff;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
            outline: none;
        }

        /* Checkbox ketika di hover */
        .custom-checkbox:hover {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Checkbox ketika di focus */
        .custom-checkbox:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        /* Checkbox ketika checked */
        .custom-checkbox:checked {
            background-color: #22c55e; /* Hijau untuk checked */
            border-color: #22c55e;
        }

        /* Tanda centang di dalam checkbox */
        .custom-checkbox:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
            line-height: 1;
        }

        /* Checkbox indeterminate state (untuk master checkbox) */
        .custom-checkbox:indeterminate {
            background-color: #6b7280;
            border-color: #6b7280;
        }

        .custom-checkbox:indeterminate::after {
            content: '−';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 14px;
            font-weight: bold;
            line-height: 1;
        }

        /* Perbaikan untuk checkbox di header navy */
        .table-header-navy .custom-checkbox {
            border-color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.9) !important;
        }

        .table-header-navy .custom-checkbox:hover {
            border-color: #ffffff !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
        }

        .table-header-navy .custom-checkbox:checked {
            background-color: #22c55e !important;
            border-color: #22c55e !important;
        }

        .table-header-navy .custom-checkbox:indeterminate {
            background-color: #f59e0b !important;
            border-color: #f59e0b !important;
        }

        /* Component checkbox styling - extends the existing custom-checkbox class */
        .component-select-1000001,
        .component-select-1000002,
        .component-select-1000003,
        .component-select-1000004,
        .component-select-1000005,
        [class*="component-select-"] {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            width: 18px;
            height: 18px;
            border: 2px solid #d1d5db;
            border-radius: 3px !important;
            background-color: #ffffff;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
            outline: none;
        }

        /* Component checkbox hover */
        [class*="component-select-"]:hover {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Component checkbox focus */
        [class*="component-select-"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        /* Component checkbox checked */
        [class*="component-select-"]:checked {
            background-color: #22c55e !important;
            border-color: #22c55e !important;
        }

        /* Checkmark for component checkboxes */
        [class*="component-select-"]:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
            line-height: 1;
        }

        /* Select all checkbox for components */
        [id*="select-all-components-"]:indeterminate {
            background-color: #6b7280 !important;
            border-color: #6b7280 !important;
        }

        [id*="select-all-components-"]:indeterminate::after {
            content: '−';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 14px;
            font-weight: bold;
            line-height: 1;
        }

        /* CSS untuk mengatur lebar tabel yang lebih kompak */
        .compact-table {
            table-layout: fixed;
            width: 100%;
        }
        .compact-table th,
        .compact-table td {
            padding: 0.35rem 0.5rem !important; /* Further reduced padding */
            font-size: 0.8rem; /* Smaller font */
            line-height: 1.3;
        }
        .compact-table .w-checkbox {
            width: 35px !important;
        }
        .compact-table .w-expand {
            width: 40px !important;
        }
        .compact-table .w-no {
            width: 45px !important;
        }
        .compact-table .w-po-number {
            width: 110px !important;
        }
        .compact-table .w-doc-date {
            width: 85px !important;
        }
        .compact-table .w-week {
            width: 65px !important;
        }
        .compact-table .w-vendor {
            width: 180px !important;
        }
        .compact-table .w-created-by {
            width: 100px !important;
        }
        .compact-table .w-total {
            width: 120px !important;
        }
        .compact-table .w-currency {
            width: 65px !important;
        }

        /* Updated Sticky Layout Styles */
        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            padding-top: 0 !important;
            display: flex;
            flex-direction: column;
        }

        .sticky-navigation {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 50 !important;
            background-color: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            border-bottom: 1px solid rgba(229, 231, 235, 0.8) !important;
            height: 64px !important; /* Fixed height */
        }

        .sticky-header {
            position: fixed !important;
            top: 64px !important; /* Height of navigation */
            left: 0 !important;
            right: 0 !important;
            z-index: 40 !important;
            background-color: rgba(255, 255, 255, 0.4) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            box-shadow: none !important;
            height: 64px !important; /* Fixed height */
        }

        /* Updated main content styles */
        .main-content {
            margin-top: 128px !important; /* Navigation height + Header height */
            background-image: url('{{ asset('images/bg-app.jpg') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center bottom;
            height: calc(100vh - 128px) !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            padding-top: 20px !important;
        }

        /* Ensure proper scrolling behavior */
        .min-h-screen {
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
        }

        /* Table-specific scroll container */
        .table-scroll-container {
            max-height: calc(100vh - 380px);
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
            scroll-behavior: smooth;
        }

        /* Prevent body scroll when table has content */
        body.has-table-scroll {
            overflow: hidden;
        }

        /* Global Loading Overlay */
        #global-loading {
            position: fixed;
            inset: 0;
            background: #f9fafb;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: opacity 0.3s ease;
            font-family: 'Figtree', sans-serif;
        }
        .loading-dots {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            align-items: flex-end;
            height: 2rem;
            margin-bottom: 1rem;
        }
        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #2563eb;
            animation: bounce 1.2s infinite ease-in-out;
        }
        .dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        .dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        @keyframes bounce {
            0%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-16px); }
        }
        .loading-text {
            font-size: 1rem;
            color: #374151;
            animation: fadein 1.5s ease-in-out;
        }
        @keyframes fadein {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Tambahan CSS untuk memastikan dropdown bekerja */
        [x-cloak] { 
            display: none !important; 
        }

        /* CSS untuk spacing yang lebih baik */
        .search-row-spacing {
            gap: 2rem; /* Jarak antara search dan show rows */
        }

        /* Compact table styles - reduced spacing between columns */
        .compact-table th,
        .compact-table td {
            padding: 0.15rem 0.25rem !important; /* Further reduced spacing */
        }
        
        /* Compact detail table */
        .compact-detail-table th,
        .compact-detail-table td {
            padding: 0.15rem 0.25rem !important;
        }
        
        /* Custom margin for better detail alignment */
        .ml-47 {
            margin-left: 3rem; /* Adjusted margin to align with NO column */
        }

        /* Action buttons sticky at bottom */
        .action-buttons-container {
            position: sticky;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-top: 1px solid rgba(229, 231, 235, 0.8);
            z-index: 15;
            padding: 1rem;
            margin-top: 0;
        }

        /* Bulk delete controls styling */
        [id*="bulk-delete-controls-"] {
            transition: all 0.3s ease;
        }

        /* Green Add Component button styling */
        .bg-green-600 {
            background-color: #16a34a !important;
        }

        .bg-green-600:hover {
            background-color: #15803d !important;
        }

        /* Enhanced button transitions */
        button[onclick*="openModalAddComponent"] {
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        button[onclick*="openModalAddComponent"]:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* Scrollbar styling for better UX */
        .table-scroll-container::-webkit-scrollbar {
            width: 8px;
        }

        .table-scroll-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-scroll-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .table-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Firefox scrollbar */
        .table-scroll-container {
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
        }
    </style>
</head>
<body class="font-sans antialiased">

    <!-- 🔄 Global Loading Overlay -->
    <div id="global-loading">
        <div class="text-center">
            <div class="loading-dots">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <p class="loading-text">Mengambil data SAP...</p>
        </div>
    </div>

    <div class="min-h-screen flex flex-col">
        <!-- Sticky Navigation -->
        <nav class="sticky-navigation">
            @include('layouts.navigation')
        </nav>

        <!-- Sticky Header -->
        @if (isset($header))
        <header class="sticky-header">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endif

        <!-- Main Content -->
        <main class="main-content flex-1">
            {{ $slot }}
        </main>
    </div>

    <!-- Scripts -->
    <script>
        // Pastikan Alpine.js sudah ter-load sebelum menjalankan script lain
        document.addEventListener('DOMContentLoaded', function() {
            // Debug Alpine.js
            if (typeof Alpine === 'undefined') {
                console.error('Alpine.js not loaded properly');
            } else {
                console.log('Alpine.js loaded successfully');
            }
        });

        window.addEventListener('pageshow', function () {
            const loader = document.getElementById('global-loading');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => loader.style.display = 'none', 300);
            }
        });

        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll("a").forEach(link => {
                link.addEventListener("click", function (e) {
                    const target = e.currentTarget;
                    const href = target.getAttribute("href");

                    if (
                        href &&
                        !href.startsWith("#") &&
                        !href.startsWith("javascript:") &&
                        target.target !== "_blank"
                    ) {
                        // Jangan tampilkan loader untuk dropdown links
                        if (target.closest('[x-data]')) {
                            return;
                        }
                        
                        const loader = document.getElementById('global-loading');
                        if (loader) {
                            loader.style.display = 'flex';
                            loader.style.opacity = '1';
                        }
                    }
                });
            });
        });
    </script>

    {{-- SweetAlert2 Toast Notifikasi --}}
    @if(session('success'))
    <script>
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: @json(session('success')),
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: @json(session('error')),
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
        });
    </script>
    @endif

    @stack('scripts')
</body>
</html>