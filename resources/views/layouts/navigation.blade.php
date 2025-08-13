<!-- navigation.blade.php -->
<div class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                    @if (in_array(auth()->user()->role, ['admin', 'user']))
                    <a href="{{ route('report') }}">
                    </a>
                    @endif
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @if (in_array(auth()->user()->role, ['admin', 'user']))
                    <x-nav-link :href="route('report')" :active="request()->routeIs('report')">
                        {{ __('Report') }}
                    </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <div class="relative">
                    <button 
                        id="profileButton" 
                        onclick="toggleDropdown()"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                        <div>{{ Auth::user()->name }}</div>
                        <div class="ml-1">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>

                    <!-- Dropdown Menu -->
                    <div 
                        id="profileDropdown"
                        class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                        style="display: none;">
                        
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            {{ __('Profile') }}
                        </a>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Log Out') }}
                            </button>
                        </form>

                        @if(session('sap_user'))
                            <form method="POST" action="{{ route('sap.logout') }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ __('Logout SAP') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button onclick="toggleMobileMenu()" id="mobileMenuButton" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path id="menuIcon" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path id="closeIcon" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div id="mobileMenu" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if (in_array(auth()->user()->role, ['admin', 'user']))
            <x-responsive-nav-link :href="route('report')" :active="request()->routeIs('report')">
                {{ __('Report') }}
            </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>

                @if(session('sap_user'))
                    <form method="POST" action="{{ route('sap.logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('sap.logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Logout SAP') }}
                        </x-responsive-nav-link>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* Updated Sticky Navigation Specific Styles */
.sticky-navigation {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
    z-index: 50 !important;
}

.sticky-navigation .relative {
    position: relative !important;
}

/* Dropdown positioning adjustment for sticky nav */
.sticky-navigation #profileDropdown {
    position: absolute !important;
    top: 100% !important;
    right: 0 !important;
    z-index: 60 !important;
    margin-top: 0.5rem !important;
    background-color: rgba(255, 255, 255, 0.98) !important;
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
    border: 1px solid rgba(229, 231, 235, 0.8) !important;
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
}

/* Mobile menu positioning for sticky nav */
.sticky-navigation #mobileMenu {
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 60 !important;
    background-color: rgba(255, 255, 255, 0.98) !important;
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
    border-top: 1px solid rgba(229, 231, 235, 0.8) !important;
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    max-height: calc(100vh - 64px) !important;
    overflow-y: auto !important;
}

/* Enhanced hover effects for sticky navigation */
.sticky-navigation .hover\:text-gray-700:hover {
    color: #374151 !important;
}

.sticky-navigation .hover\:bg-gray-100:hover {
    background-color: rgba(243, 244, 246, 0.8) !important;
}

/* Ensure proper contrast for navigation links */
.sticky-navigation x-nav-link,
.sticky-navigation x-responsive-nav-link {
    color: #374151 !important;
}

/* Animation for smooth dropdown appearance */
.sticky-navigation #profileDropdown,
.sticky-navigation #mobileMenu {
    animation: fadeInDown 0.2s ease-out;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Prevent body scroll when mobile menu is open */
body.mobile-menu-open {
    overflow: hidden !important;
}

/* Mobile menu scrollbar styling */
.sticky-navigation #mobileMenu::-webkit-scrollbar {
    width: 4px;
}

.sticky-navigation #mobileMenu::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.sticky-navigation #mobileMenu::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 2px;
}

.sticky-navigation #mobileMenu::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
// Dropdown Profile
function toggleDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    if (dropdown.style.display === 'none' || dropdown.style.display === '') {
        dropdown.style.display = 'block';
        // Add click outside listener
        setTimeout(() => {
            document.addEventListener('click', closeDropdownOnClickOutside);
        }, 0);
    } else {
        dropdown.style.display = 'none';
        document.removeEventListener('click', closeDropdownOnClickOutside);
    }
}

// Close dropdown when clicking outside
function closeDropdownOnClickOutside(event) {
    const dropdown = document.getElementById('profileDropdown');
    const button = document.getElementById('profileButton');
    
    if (!button.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = 'none';
        document.removeEventListener('click', closeDropdownOnClickOutside);
    }
}

// Mobile Menu
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobileMenu');
    const menuIcon = document.getElementById('menuIcon');
    const closeIcon = document.getElementById('closeIcon');
    const body = document.body;
    
    if (mobileMenu.classList.contains('hidden')) {
        mobileMenu.classList.remove('hidden');
        mobileMenu.classList.add('block');
        menuIcon.classList.add('hidden');
        menuIcon.classList.remove('inline-flex');
        closeIcon.classList.remove('hidden');
        closeIcon.classList.add('inline-flex');
        body.classList.add('mobile-menu-open');
    } else {
        mobileMenu.classList.add('hidden');
        mobileMenu.classList.remove('block');
        menuIcon.classList.remove('hidden');
        menuIcon.classList.add('inline-flex');
        closeIcon.classList.add('hidden');
        closeIcon.classList.remove('inline-flex');
        body.classList.remove('mobile-menu-open');
    }
}

// Tutup dropdown ketika klik di luar
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('profileDropdown');
    const button = document.getElementById('profileButton');
    
    if (!button.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileButton = document.getElementById('mobileMenuButton');
    
    if (!mobileButton.contains(event.target) && !mobileMenu.contains(event.target)) {
        if (!mobileMenu.classList.contains('hidden')) {
            toggleMobileMenu();
        }
    }
});

// Handle window resize to close mobile menu on desktop
window.addEventListener('resize', function() {
    if (window.innerWidth >= 640) { // sm breakpoint
        const mobileMenu = document.getElementById('mobileMenu');
        const menuIcon = document.getElementById('menuIcon');
        const closeIcon = document.getElementById('closeIcon');
        const body = document.body;
        
        if (!mobileMenu.classList.contains('hidden')) {
            mobileMenu.classList.add('hidden');
            mobileMenu.classList.remove('block');
            menuIcon.classList.remove('hidden');
            menuIcon.classList.add('inline-flex');
            closeIcon.classList.add('hidden');
            closeIcon.classList.remove('inline-flex');
            body.classList.remove('mobile-menu-open');
        }
    }
});

// Prevent scroll when mobile menu is open
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenu) {
        // Ensure mobile menu starts hidden
        mobileMenu.classList.add('hidden');
    }
});
</script>