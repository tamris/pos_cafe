@php
    $currentPath = trim(request()->path(), '/');
    if (str_starts_with($currentPath, 'livewire')) {
        $referer = request()->header('Referer') ?? '';
        $currentPath = trim(parse_url($referer, PHP_URL_PATH) ?? '', '/');
    }

    $isPath = function($prefix) use ($currentPath) {
        if ($prefix === 'dashboard') {
            return $currentPath === 'dashboard' || $currentPath === '';
        }
        return str_starts_with($currentPath, $prefix);
    };

    $isMasterData = $isPath('products') || $isPath('categories') || $isPath('hpp') || $isPath('barcodes');
    $isReports    = $isPath('stock-management') || $isPath('reports') || $isPath('shifts');
    $isSettings   = $isPath('users') || $isPath('settings');

    $activeGroup = $isMasterData ? 'master-data' : ($isReports ? 'reports' : ($isSettings ? 'settings' : ''));
@endphp

<div>
    {{-- BACKDROP OVERLAY UNTUK MOBILE --}}
    <div x-show="mobileSidebarOpen" 
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileSidebarOpen = false"
         class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs xl:hidden"
         style="display: none;">
    </div>

    {{-- SIDEBAR CONTAINER --}}
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 
               dark:bg-slate-900 dark:border-slate-700 
               flex flex-col h-screen shadow-lg xl:shadow-none"
        :class="{ 'mobile-open': mobileSidebarOpen }"
        x-data="{ 
            activeGroup: '{{ $activeGroup }}',
            toggleGroup(group) {
                this.activeGroup = (this.activeGroup === group) ? '' : group;
            }
        }">

        {{-- BRAND HEADER --}}
        <div class="flex items-center justify-between h-16 px-5 border-b border-slate-200 dark:border-slate-700 shrink-0">
            @php
                $setting = \App\Models\Setting::first();
                $currentShopName = $setting?->shop_name ?? 'POS Cafe';
                $logoPath = null;
                if (file_exists(public_path('images/logo.png'))) {
                    $logoPath = asset('images/logo.png');
                } elseif (file_exists(public_path('images/logo.svg'))) {
                    $logoPath = asset('images/logo.svg');
                } elseif (file_exists(public_path('images/logo.jpg'))) {
                    $logoPath = asset('images/logo.jpg');
                }
            @endphp
            <div class="flex items-center space-x-3 min-w-0">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="{{ $currentShopName }}" class="w-9 h-9 rounded-xl object-contain bg-white dark:bg-slate-800 p-0.5 border border-slate-200 dark:border-slate-700 shadow-2xs shrink-0">
                @else
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60 flex items-center justify-center shadow-2xs shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zM6 1v3M10 1v3M14 1v3"></path>
                        </svg>
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <span class="text-sm font-extrabold text-slate-900 dark:text-white block leading-tight truncate" title="{{ $currentShopName }}">{{ $currentShopName }}</span>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold block uppercase tracking-wider mt-0.5">POS & Management</span>
                </div>
            </div>

            {{-- TOMBOL CLOSE --}}
            <button @click="mobileSidebarOpen = false" type="button" class="xl:hidden text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- NAVIGATION LINKS --}}
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto scrollbar-thin">
            
            {{-- Dashboard (Admin Only) --}}
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('dashboard') }}" wire:navigate @click="if(window.innerWidth < 1280) mobileSidebarOpen = false"
                class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors
                {{ $isPath('dashboard') ? 'text-slate-900 bg-slate-100 dark:bg-slate-800 dark:text-white font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="font-medium text-sm">Dashboard</span>
            </a>    
            @endif

            {{-- POS (All User) --}}
            <a href="{{ route('pos.index') }}" wire:navigate @click="if(window.innerWidth < 1280) mobileSidebarOpen = false"
                class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors
                {{ $isPath('pos') ? 'text-slate-900 bg-slate-100 dark:bg-slate-800 dark:text-white font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span class="font-medium text-sm">Kasir (POS)</span>
            </a>

            {{-- Pesanan Online (All User) --}}
            @php
                $activeOnlineOrdersCount = \App\Models\Transaction::where('order_source', 'self_order')
                    ->where('payment_status', 'paid')
                    ->whereIn('status', ['pending', 'processing', 'ready'])
                    ->count();
            @endphp
            <a href="{{ route('online-orders.index') }}" wire:navigate @click="if(window.innerWidth < 1280) mobileSidebarOpen = false"
                class="sidebar-link flex items-center justify-between px-4 py-3 rounded-lg transition-colors
                {{ $isPath('online-orders') ? 'text-slate-900 bg-slate-100 dark:bg-slate-800 dark:text-white font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                    </svg>
                    <span class="font-medium text-sm">Pesanan Online</span>
                </div>
                @if($activeOnlineOrdersCount > 0)
                    <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-black text-white bg-rose-600 rounded-full animate-pulse shadow-2xs">
                        {{ $activeOnlineOrdersCount }}
                    </span>
                @endif
            </a>

            {{-- Master Data (Dropdown - Admin Only) --}}
            @if(auth()->user()->role === 'admin')
            <div>
                <button @click="toggleGroup('master-data')" type="button"
                    class="sidebar-link flex items-center justify-between w-full px-4 py-3 rounded-lg transition-colors text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800"
                    :class="activeGroup === 'master-data' ? 'font-semibold text-slate-900 dark:text-white' : ''">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <span class="font-medium text-sm">Data Master</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="activeGroup === 'master-data' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>

                <div x-show="activeGroup === 'master-data'" class="mt-1 space-y-1 pl-4" style="display: none;">
                    <a href="{{ route('products.index') }}" wire:navigate @click="if(window.innerWidth < 1280) mobileSidebarOpen = false"
                        class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm transition-colors {{ $isPath('products') ? 'text-slate-900 font-semibold bg-slate-100 dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                        <span>Menu Produk</span>
                    </a>
                    <a href="{{ route('categories.index') }}" wire:navigate @click="if(window.innerWidth < 1280) mobileSidebarOpen = false"
                        class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm transition-colors {{ $isPath('categories') ? 'text-slate-900 font-semibold bg-slate-100 dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                        <span>Kategori</span>
                    </a>
                    <a href="{{ route('hpp.index') }}" wire:navigate @click="if(window.innerWidth < 1280) mobileSidebarOpen = false"
                        class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm transition-colors {{ $isPath('hpp') ? 'text-slate-900 font-semibold bg-slate-100 dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                        <span>Manajemen HPP & Margin</span>
                    </a>
                </div>
            </div>
            @endif

            {{-- Transaksi (All User) --}}
            <a href="{{ route('transactions.index') }}" wire:navigate @click="if(window.innerWidth < 1280) mobileSidebarOpen = false"
                class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors
                {{ $isPath('transactions') ? 'text-slate-900 bg-slate-100 dark:bg-slate-800 dark:text-white font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span class="font-medium text-sm">Transaksi</span>
            </a>

            {{-- Riwayat Shift (Kasir Non-Admin) --}}
            @if(auth()->user()->role !== 'admin')
            <a href="{{ route('shifts.index') }}" wire:navigate @click="if(window.innerWidth < 1280) mobileSidebarOpen = false"
                class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors
                {{ $isPath('shifts') ? 'text-slate-900 bg-slate-100 dark:bg-slate-800 dark:text-white font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium text-sm">Riwayat Shift</span>
            </a>
            @endif

            {{-- Laporan (Dropdown - Admin Only) --}}
            @if(auth()->user()->role === 'admin')
            <div>
                <button @click="toggleGroup('reports')" type="button"
                    class="sidebar-link flex items-center justify-between w-full px-4 py-3 rounded-lg transition-colors text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800"
                    :class="activeGroup === 'reports' ? 'font-semibold text-slate-900 dark:text-white' : ''">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span class="font-medium text-sm">Laporan</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="activeGroup === 'reports' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>

                <div x-show="activeGroup === 'reports'" class="mt-1 space-y-1 pl-4" style="display: none;">
                    <a href="{{ route('stock-management.index') }}" wire:navigate @click="if(window.innerWidth < 1280) mobileSidebarOpen = false"
                        class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm transition-colors {{ $isPath('stock-management') ? 'text-slate-900 font-semibold bg-slate-100 dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                        <span>Penjualan Menu</span>
                    </a>
                    <a href="{{ route('reports.index') }}" wire:navigate @click="if(window.innerWidth < 1280) mobileSidebarOpen = false"
                        class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm transition-colors {{ $isPath('reports') ? 'text-slate-900 font-semibold bg-slate-100 dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                        <span>Laporan Ringkasan</span>
                    </a>
                    <a href="{{ route('shifts.index') }}" wire:navigate @click="if(window.innerWidth < 1280) mobileSidebarOpen = false"
                        class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm transition-colors {{ $isPath('shifts') ? 'text-slate-900 font-semibold bg-slate-100 dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                        <span>Laporan Shift Kasir</span>
                    </a>
                </div>
            </div>
            @endif

            {{-- Pengaturan (Dropdown - Admin Only) --}}
            @if(auth()->user()->role === 'admin')
            <div>
                <button @click="toggleGroup('settings')" type="button"
                    class="sidebar-link flex items-center justify-between w-full px-4 py-3 rounded-lg transition-colors text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800"
                    :class="activeGroup === 'settings' ? 'font-semibold text-slate-900 dark:text-white' : ''">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span class="font-medium text-sm">Pengaturan</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="activeGroup === 'settings' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>

                <div x-show="activeGroup === 'settings'" class="mt-1 space-y-1 pl-4" style="display: none;">
                    <a href="{{ route('users.index') }}" wire:navigate @click="if(window.innerWidth < 1280) mobileSidebarOpen = false"
                        class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm transition-colors {{ $isPath('users') ? 'text-slate-900 font-semibold bg-slate-100 dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                        <span>Manajemen Pengguna</span>
                    </a>
                    <a href="{{ route('settings.index') }}" wire:navigate @click="if(window.innerWidth < 1280) mobileSidebarOpen = false"
                        class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm transition-colors {{ $isPath('settings') ? 'text-slate-900 font-semibold bg-slate-100 dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800' }}">
                        <span>Pengaturan Struk</span>
                    </a>
                </div>
            </div>
            @endif
        </nav>

        {{-- USER PROFILE / LOGOUT --}}
        <div class="p-4 border-t border-slate-200 bg-white dark:bg-slate-900 dark:border-slate-700 shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-slate-100 dark:bg-slate-800 rounded-full shrink-0">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white truncate max-w-[110px]">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ auth()->user()->role === 'admin' ? 'Administrator' : 'Kasir' }}</p>
                    </div>
                </div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="button" onclick="confirmLogout()" class="p-2 text-slate-400 hover:text-rose-600 dark:text-slate-500 dark:hover:text-rose-400 transition-colors rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 cursor-pointer" title="Keluar / Logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>
</div>