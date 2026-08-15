@php
    $activeGroup = '';
    
    if (request()->routeIs('products.*') || 
        request()->routeIs('categories.*') || 
        request()->routeIs('hpp.*') || 
        request()->routeIs('barcodes.*')) {
        $activeGroup = 'master-data';
    } 
    elseif (request()->routeIs('stock-management.*') || 
            request()->routeIs('reports.*')) {
        $activeGroup = 'reports';
    }
    elseif (request()->routeIs('users.*') || 
            request()->routeIs('settings.*')) {
        $activeGroup = 'settings';
    }
@endphp

<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 transition-transform duration-300 ease-in-out 
           dark:bg-slate-900 dark:border-slate-700 
           flex flex-col h-screen
           -translate-x-full lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    x-data="{ 
        activeGroup: '{{ $activeGroup }}',
        toggleGroup(group) {
            this.activeGroup = (this.activeGroup === group) ? '' : group;
        }
    }">

    <div class="flex items-center justify-between h-16 px-6 border-b border-slate-200 dark:border-slate-700 shrink-0">
        <div class="flex items-center space-x-3">
            <div class="flex items-center justify-center w-10 h-10 bg-amber-600 dark:bg-amber-700 rounded-xl shadow-md">
                <span class="text-xl">☕</span>
            </div>
            <div>
                <span class="text-lg font-extrabold text-slate-900 dark:text-white block leading-tight">Cafe Noli</span>
                <span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold block uppercase tracking-wider">Coffee & Eatery</span>
            </div>
        </div>
    </div>

    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto scrollbar-thin">
        
        {{-- Dashboard (Admin Only) --}}
        @if(auth()->user()->role === 'admin')
        <a href="{{ route('dashboard') }}" wire:navigate @click="if(window.innerWidth < 1024) sidebarOpen = false"
            class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors
            {{ request()->routeIs('dashboard') ? 'text-slate-900 bg-slate-100 dark:bg-slate-800 dark:text-white font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="font-medium">Dashboard</span>
        </a>    
        @endif

        {{-- POS (All User) --}}
        <a href="{{ route('pos.index') }}" wire:navigate @click="if(window.innerWidth < 1024) sidebarOpen = false"
            class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors
            {{ request()->routeIs('pos.*') ? 'text-slate-900 bg-slate-100 dark:bg-slate-800 dark:text-white font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            <span class="font-medium">Kasir (POS)</span>
        </a>

        {{-- Master Data (Dropdown - Admin Only) --}}
        @if(auth()->user()->role === 'admin')
        <div>
            <button @click="toggleGroup('master-data')" type="button"
                class="sidebar-link flex items-center justify-between w-full px-4 py-3 rounded-lg transition-colors
                {{ $activeGroup === 'master-data' ? 'bg-slate-50 text-slate-900 dark:bg-slate-800 dark:text-white font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span class="font-medium">Master Data</span>
                </div>
                <svg class="w-4 h-4 transition-transform duration-200" :class="activeGroup === 'master-data' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <div x-show="activeGroup === 'master-data'" x-collapse x-cloak class="mt-1 space-y-1 pl-4">
                <a href="{{ route('products.index') }}" wire:navigate @click="if(window.innerWidth < 1024) sidebarOpen = false"
                    class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('products.*') ? 'text-slate-900 font-semibold bg-slate-100 dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                    <span>Produk</span>
                </a>
                <a href="{{ route('categories.index') }}" wire:navigate @click="if(window.innerWidth < 1024) sidebarOpen = false"
                    class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('categories.*') ? 'text-slate-900 font-semibold bg-slate-100 dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                    <span>Kategori</span>
                </a>
                <a href="{{ route('hpp.index') }}" wire:navigate @click="if(window.innerWidth < 1024) sidebarOpen = false"
                    class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('hpp.*') ? 'text-slate-900 font-semibold bg-slate-100 dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                    <span>Manajemen HPP & Margin</span>
                </a>
                <!-- <a href="{{ route('barcodes.index') }}" wire:navigate @click="if(window.innerWidth < 1024) sidebarOpen = false"
                    class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('barcodes.*') ? 'text-slate-900 font-semibold bg-slate-100 dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                    <span>Cetak Barcode</span>
                </a> -->
            </div>
        </div>
        @endif

        {{-- Transaksi (All User) --}}
        <a href="{{ route('transactions.index') }}" wire:navigate @click="if(window.innerWidth < 1024) sidebarOpen = false"
            class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors
            {{ request()->routeIs('transactions.*') ? 'text-slate-900 bg-slate-100 dark:bg-slate-800 dark:text-white font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <span class="font-medium">Transaksi</span>
        </a>

        {{-- Laporan (Dropdown - Admin Only) --}}
        @if(auth()->user()->role === 'admin')
        <div>
            <button @click="toggleGroup('reports')" type="button"
                class="sidebar-link flex items-center justify-between w-full px-4 py-3 rounded-lg transition-colors
                {{ $activeGroup === 'reports' ? 'bg-slate-50 text-slate-900 dark:bg-slate-800 dark:text-white font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="font-medium">Laporan</span>
                </div>
                <svg class="w-4 h-4 transition-transform duration-200" :class="activeGroup === 'reports' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <div x-show="activeGroup === 'reports'" x-collapse x-cloak class="mt-1 space-y-1 pl-4">
                <a href="{{ route('stock-management.index') }}" wire:navigate @click="if(window.innerWidth < 1024) sidebarOpen = false"
                    class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('stock-management.*') ? 'text-slate-900 font-semibold bg-slate-100 dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                    <span>Penjualan Menu</span>
                </a>
                <a href="{{ route('reports.index') }}" wire:navigate @click="if(window.innerWidth < 1024) sidebarOpen = false"
                    class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('reports.*') ? 'text-slate-900 font-semibold bg-slate-100 dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                    <span>Laporan Ringkasan</span>
                </a>
            </div>
        </div>
        @endif

        {{-- Pengaturan (Dropdown - Admin Only) --}}
        @if(auth()->user()->role === 'admin')
        <div>
            <button @click="toggleGroup('settings')" type="button"
                class="sidebar-link flex items-center justify-between w-full px-4 py-3 rounded-lg transition-colors
                {{ $activeGroup === 'settings' ? 'bg-slate-50 text-slate-900 dark:bg-slate-800 dark:text-white font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="font-medium">Pengaturan</span>
                </div>
                <svg class="w-4 h-4 transition-transform duration-200" :class="activeGroup === 'settings' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <div x-show="activeGroup === 'settings'" x-collapse x-cloak class="mt-1 space-y-1 pl-4">
                <a href="{{ route('users.index') }}" wire:navigate @click="if(window.innerWidth < 1024) sidebarOpen = false"
                    class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('users.*') ? 'text-slate-900 font-semibold bg-slate-100 dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                    <span>Manajemen User</span>
                </a>
                <a href="{{ route('settings.index') }}" wire:navigate @click="if(window.innerWidth < 1024) sidebarOpen = false"
                    class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('settings.*') ? 'text-slate-900 font-semibold bg-slate-100 dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                    <span>Pengaturan Struk</span>
                </a>
            </div>
        </div>
        @endif
    </nav>

    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-200 bg-white dark:bg-slate-900 dark:border-slate-700 shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-10 h-10 bg-slate-100 dark:bg-slate-800 rounded-full">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ substr(auth()->user()->name, 0, 1) }}</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-900 dark:text-white truncate w-24">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ ucfirst(auth()->user()->role) }}</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="p-2 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-white transition-colors" title="Keluar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </form>
        </div>
    </div>
</aside>