<div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-20 md:pb-0 transition-colors duration-300"
     x-data="{ sidebarOpen: window.innerWidth >= 1280 }"
     @resize.window="sidebarOpen = window.innerWidth >= 1280">
    
    @include('livewire.includes.sidebar')

    <div class="xl:pl-64 transition-all duration-300 flex flex-col min-h-screen">
        @include('livewire.includes.header', [
            'title' => 'POS Kasir Cafe', 
            'subtitle' => 'Sistem Pemesanan & Transaksi Kasir'
        ])

        <main class="p-3 sm:p-4 lg:p-5 space-y-3 flex-1 flex flex-col min-h-0">
            
            {{-- ========================================================================= --}}
            {{-- 1. TOP BAR: ORDER TYPE & TABLE NUMBER SELECTION                           --}}
            {{-- ========================================================================= --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl p-3 sm:p-3.5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors flex flex-col md:flex-row md:items-center justify-between gap-2.5 sm:gap-3 shrink-0">
                
                {{-- Order Type Switcher --}}
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        Tipe Pesanan:
                    </span>
                    <div class="inline-flex flex-wrap rounded-xl p-1 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 gap-1">
                        <button type="button" wire:click="setOrderType('dine_in')"
                            class="px-3 sm:px-4 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 {{ $orderType === 'dine_in' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"></path></svg>
                            <span>Dine In (Meja)</span>
                        </button>
                        <button type="button" wire:click="setOrderType('take_away')"
                            class="px-3 sm:px-4 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 {{ $orderType === 'take_away' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path></svg>
                            <span>Take Away</span>
                        </button>
                        <button type="button" wire:click="setOrderType('delivery')"
                            class="px-3 sm:px-4 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 {{ $orderType === 'delivery' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V14.25m0 0h3.75m-3.75 0v3.75m-9-6.75h5.25a1.5 1.5 0 011.5 1.5v1.5"></path></svg>
                            <span>Delivery</span>
                        </button>
                    </div>
                </div>

                {{-- Dynamic Inputs based on Order Type --}}
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    @if($orderType === 'dine_in')
                        <div class="flex flex-wrap items-center gap-2" wire:key="input-container-table">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-300">Pilih Meja:</label>
                            <select wire:model.live="selectedTable" class="px-3 py-1.5 text-xs font-bold border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer w-36 sm:w-40">
                                <option value="">-- Pilih Meja --</option>
                                @for ($i = 1; $i <= 20; $i++)
                                    <option value="{{ sprintf('%02d', $i) }}">Meja {{ sprintf('%02d', $i) }}</option>
                                @endfor
                                <option value="custom">Ketik Meja Manual...</option>
                            </select>

                            @if($isCustomTable)
                                <input type="text" wire:model.live="customTableNumber" placeholder="Contoh: Meja VIP 01" autofocus
                                    class="px-3 py-1.5 text-xs font-semibold border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white w-36">
                            @endif
                        </div>
                    @else
                        <div class="flex items-center gap-2" wire:key="input-container-customer">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-300">Nama Pelanggan:</label>
                            <input type="text" wire:model.live="customerName" wire:key="input-customer-name" placeholder="Nama Pelanggan"
                                class="px-3 py-1.5 text-xs font-semibold border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white w-44 sm:w-48">
                        </div>
                    @endif
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- 2. SHIFT STATUS & MANAGEMENT BANNER                                       --}}
            {{-- ========================================================================= --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl px-3.5 py-2.5 sm:px-4 sm:py-2.5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3 shrink-0">
                <div class="flex items-center gap-3">
                    @if ($activeShift)
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-extrabold text-slate-900 dark:text-white">
                                Shift Aktif (#SFT-{{ str_pad($activeShift->id, 5, '0', STR_PAD_LEFT) }})
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500">•</span>
                            <span class="text-xs text-slate-600 dark:text-slate-300">
                                Buka: <strong class="text-slate-900 dark:text-white">{{ $activeShift->start_time ? $activeShift->start_time->format('H:i') : '-' }}</strong>
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500 hidden md:inline">•</span>
                            <span class="text-xs text-slate-600 dark:text-slate-300 hidden md:inline">
                                Modal Awal: <strong class="text-slate-900 dark:text-white">Rp {{ number_format($activeShift->starting_cash, 0, ',', '.') }}</strong>
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500 hidden lg:inline">•</span>
                            <span class="text-xs text-slate-600 dark:text-slate-300 hidden lg:inline">
                                Kas Laci Saat Ini: <strong class="text-emerald-600 dark:text-emerald-400 font-bold">Rp {{ number_format($activeShift->expected_cash, 0, ',', '.') }}</strong>
                            </span>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-xs">
                            <span class="px-2.5 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 text-amber-700 dark:text-amber-400 font-bold flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path></svg>
                                <span>Shift Belum Dibuka</span>
                            </span>
                            <span class="text-slate-500 dark:text-slate-400">Buka shift kasir untuk mencatat uang modal awal di laci.</span>
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    {{-- Quick Availability / Item 86 Toggle Button --}}
                    <button type="button" wire:click="openAvailabilityModal"
                        class="px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs active:scale-95 cursor-pointer"
                        title="Atur ketersediaan menu / kategori yang habis (Item 86)">
                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"></path>
                        </svg>
                        <span>Ketersediaan Menu</span>
                    </button>

                    @if ($activeShift)
                        <button type="button" wire:click="openEndShiftModal"
                            class="px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/80 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path></svg>
                            <span>Tutup Shift & Rekap Kas</span>
                        </button>
                    @else
                        <button type="button" wire:click="openStartShiftModal"
                            class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"></path></svg>
                            <span>Buka Shift Kasir</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- 3. MAIN LAYOUT: PRODUK (KIRI) & KERANJANG (KANAN)                        --}}
            {{-- ========================================================================= --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 sm:gap-4 lg:gap-5 items-start flex-1 min-h-0">
                
                {{-- LEFT COLUMN: SEARCH & SCROLLABLE PRODUCTS --}}
                <div class="md:col-span-7 xl:col-span-8 flex flex-col md:h-[calc(100vh-14.5rem)] xl:h-[calc(100vh-13.8rem)] space-y-3 min-h-0">
                    
                    {{-- SEARCH & CATEGORY SELECTOR --}}
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-3 sm:p-3.5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors shrink-0">
                        <div class="flex flex-col sm:flex-row gap-2.5 sm:gap-3">
                            <div class="flex-1 relative">
                                <input type="text" wire:model.live="search"
                                    id="searchInput"
                                    placeholder="Cari menu / scan barcode..."
                                    class="w-full pl-9 pr-9 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 text-xs"
                                    autocomplete="off">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path></svg>
                                @if(!empty($search))
                                    <button type="button" wire:click="clearSearch"
                                        class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 p-0.5 rounded"
                                        title="Hapus pencarian">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                @endif
                            </div>
                            <select wire:model.live="selectedCategory" class="px-3 sm:px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer text-xs font-semibold w-full sm:w-56">
                                <option value="">Semua Kategori ({{ $categories->sum('products_count') }})</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->products_count }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Products Grid: Scroll Mandiri Di Dalam Area Ini --}}
                    <div class="flex-1 overflow-y-auto pr-1 sm:pr-1.5 pb-6 scrollbar-thin dark:scrollbar-thumb-slate-700 min-h-0">
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-3 2xl:grid-cols-4 gap-2 sm:gap-3 xl:gap-3.5">
                            @forelse($products as $product)
                                @php
                                    $inCartQty = $this->getCartQuantity($product->id);
                                @endphp
                                @if($product->is_active)
                                    {{-- 1. KARTU MENU AKTIF (KLIK UNTUK ORDER) --}}
                                    <div wire:click="addToCart({{ $product->id }})"
                                        class="bg-white dark:bg-slate-800 rounded-xl p-2 sm:p-2.5 xl:p-3 border {{ $inCartQty > 0 ? 'border-emerald-600 dark:border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200/80 dark:border-slate-700/80' }} shadow-2xs hover:border-emerald-400 dark:hover:border-emerald-500 transition-all active:scale-[0.97] flex flex-col justify-between h-full group relative overflow-hidden cursor-pointer">
                                        
                                        {{-- In-Cart Badge Indicator --}}
                                        @if($inCartQty > 0)
                                            <div class="absolute top-1.5 right-1.5 xl:top-2 xl:right-2 z-10 bg-emerald-600 text-white text-[9px] xl:text-[10px] font-bold px-1.5 py-0.5 rounded-md shadow-xs">
                                                {{ $inCartQty }}x
                                            </div>
                                        @endif

                                        <div>
                                            {{-- Frame Gambar Produk --}}
                                            <div class="w-full aspect-square xl:aspect-[4/3] rounded-lg mb-1.5 xl:mb-2 overflow-hidden bg-slate-100 dark:bg-slate-700/50 relative flex items-center justify-center border border-slate-100 dark:border-slate-700/60">
                                                @if ($product->image)
                                                    <img src="{{ Storage::url($product->image) }}" 
                                                         alt="{{ $product->name }}" 
                                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-slate-400 dark:text-slate-500 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                                        <svg class="w-7 h-7 sm:w-8 sm:h-8 opacity-75" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zM6 1v3M10 1v3M14 1v3"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            {{-- Judul Menu --}}
                                            <h3 class="font-bold text-slate-900 dark:text-white text-[11px] sm:text-xs xl:text-sm line-clamp-2 leading-tight group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors text-center xl:text-left">
                                                {{ $product->name }}
                                            </h3>
                                            
                                            {{-- Deskripsi (Desktop Only) --}}
                                            @if($product->description)
                                                <p class="hidden xl:block text-[10px] text-slate-500 dark:text-slate-400 line-clamp-1 mb-1 mt-0.5">
                                                    {{ $product->description }}
                                                </p>
                                            @endif
                                        </div>
                                        
                                        {{-- Harga & Plus Button --}}
                                        <div class="hidden xl:flex items-center justify-between pt-1.5 border-t border-slate-100 dark:border-slate-700/60 mt-1">
                                            <p class="font-bold text-emerald-600 dark:text-emerald-400 text-xs sm:text-sm">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                            <span class="text-xs bg-emerald-50 text-emerald-700 group-hover:bg-emerald-600 group-hover:text-white dark:bg-emerald-950/40 dark:text-emerald-400 dark:group-hover:bg-emerald-600 dark:group-hover:text-white rounded-lg p-1 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path></svg>
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    {{-- 2. KARTU MENU NON-AKTIF (GRAYED OUT & DISABLED DI PALING BAWAH) --}}
                                    <div wire:click="addToCart({{ $product->id }})"
                                        class="bg-slate-100/80 dark:bg-slate-900/60 rounded-xl p-2 sm:p-2.5 xl:p-3 border border-dashed border-slate-300 dark:border-slate-700 opacity-60 grayscale hover:opacity-75 transition-all flex flex-col justify-between h-full relative overflow-hidden cursor-not-allowed select-none group">
                                        
                                        {{-- Badge Tidak Tersedia --}}
                                        <div class="absolute top-1.5 right-1.5 xl:top-2 xl:right-2 z-10 bg-amber-500/95 dark:bg-amber-600/95 text-white text-[8px] xl:text-[9px] font-bold px-1.5 py-0.5 rounded shadow-xs uppercase tracking-wider flex items-center gap-1">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                            <span>Tidak Tersedia</span>
                                        </div>

                                        <div>
                                            {{-- Frame Gambar Produk --}}
                                            <div class="w-full aspect-square xl:aspect-[4/3] rounded-lg mb-1.5 xl:mb-2 overflow-hidden bg-slate-200/70 dark:bg-slate-800 relative flex items-center justify-center border border-slate-200 dark:border-slate-700/60">
                                                @if ($product->image)
                                                    <img src="{{ Storage::url($product->image) }}" 
                                                         alt="{{ $product->name }}" 
                                                         class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-slate-400">
                                                        <svg class="w-7 h-7 sm:w-8 sm:h-8 opacity-50" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zM6 1v3M10 1v3M14 1v3"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            {{-- Judul Menu --}}
                                            <h3 class="font-semibold text-slate-500 dark:text-slate-400 text-[11px] sm:text-xs xl:text-sm line-clamp-2 leading-tight text-center xl:text-left line-through decoration-slate-400">
                                                {{ $product->name }}
                                            </h3>
                                            
                                            {{-- Deskripsi (Desktop Only) --}}
                                            @if($product->description)
                                                <p class="hidden xl:block text-[10px] text-slate-400 dark:text-slate-500 line-clamp-1 mb-1 mt-0.5">
                                                    {{ $product->description }}
                                                </p>
                                            @endif
                                        </div>
                                        
                                        {{-- Harga Muted & Badge Non-Aktif --}}
                                        <div class="hidden xl:flex items-center justify-between pt-1.5 border-t border-slate-200 dark:border-slate-700/60 mt-1">
                                            <p class="font-semibold text-slate-400 dark:text-slate-500 text-xs sm:text-sm">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                            <span class="text-[10px] text-amber-700 dark:text-amber-300 font-bold bg-amber-100 dark:bg-amber-950/60 px-1.5 py-0.5 rounded border border-amber-200 dark:border-amber-800/60">
                                                Non-Aktif
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="col-span-full text-center py-12">
                                    <div class="bg-white dark:bg-slate-800 rounded-xl p-8 border border-slate-200 dark:border-slate-700 border-dashed max-w-sm mx-auto">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path></svg>
                                        </div>
                                        <p class="text-slate-600 dark:text-slate-300 font-bold text-xs sm:text-sm">Tidak ada menu cafe ditemukan</p>
                                        <p class="text-slate-400 text-xs mt-0.5">Coba kata kunci lain atau pilih kategori</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: KERANJANG PERMANEN (DESKTOP MD+) --}}
                <div class="hidden md:flex md:col-span-5 xl:col-span-4 flex-col bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm md:h-[calc(100vh-14.5rem)] xl:h-[calc(100vh-13.8rem)] overflow-hidden transition-colors shrink-0 sticky top-4">
                    
                    {{-- Header Keranjang --}}
                    <div class="p-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/40 flex justify-between items-center shrink-0">
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 text-xs sm:text-sm">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path></svg>
                                <span>Daftar Pesanan</span>
                            </h3>
                            <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold uppercase mt-0.5">
                                Mode: {{ $orderType === 'dine_in' ? 'Dine In' : ($orderType === 'take_away' ? 'Take Away' : 'Delivery') }} 
                                @if($orderType === 'dine_in' && $tableNumber)
                                    (Meja {{ $tableNumber }})
                                @endif
                            </p>
                        </div>
                        @if(count($cart) > 0)
                            <button onclick="confirmResetCart()" class="text-xs text-rose-500 hover:underline font-bold">Kosongkan</button>
                        @endif
                    </div>

                    {{-- Daftar Item Pesanan (Scroll Mandiri) --}}
                    <div class="p-2.5 sm:p-3 flex-1 overflow-y-auto space-y-2 scrollbar-thin dark:scrollbar-thumb-slate-700 min-h-0">
                        @include('livewire.pos.partials.cart-items')
                    </div>

                    {{-- Summary & CTA Bayar --}}
                    @if (!empty($cart))
                        <div class="p-3 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shrink-0 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.03)]">
                            @include('livewire.pos.partials.cart-summary')
                        </div>
                    @endif
                </div>

            </div>
        </main>
    </div>

    {{-- FLOATING CART BAR KHUSUS SMARTPHONE (< MD) --}}
    @if(count($cart) > 0)
    <div class="fixed bottom-0 left-0 w-full bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 p-3.5 shadow-2xl md:hidden z-40 flex justify-between items-center gap-4 transition-colors">
        <div class="flex flex-col">
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ count($cart) }} Item di keranjang</span>
            <span class="text-base font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>
        <button wire:click="openMobileCart" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2 shadow-md active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path></svg>
            <span>Lihat Pesanan</span>
        </button>
    </div>
    @endif

    {{-- BOTTOM SHEET MODAL KERANJANG KHUSUS SMARTPHONE (< MD) --}}
    @if ($showMobileCart)
    <div class="fixed inset-0 z-50 overflow-y-auto md:hidden">
        <div class="flex items-end justify-center min-h-screen text-center">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closeMobileCart"></div>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-t-2xl text-left overflow-hidden shadow-xl transform transition-all w-full h-[88vh] flex flex-col border-t border-slate-200 dark:border-slate-700">
                <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base">Rincian Pesanan</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Mode: {{ $orderType === 'dine_in' ? 'Dine In' : ($orderType === 'take_away' ? 'Take Away' : 'Delivery') }}</p>
                    </div>
                    <button wire:click="closeMobileCart" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-4 flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 space-y-3">
                    @include('livewire.pos.partials.cart-items')
                </div>
                <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 pb-8">
                    @include('livewire.pos.partials.cart-summary')
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 4. MODAL: ITEM CUSTOMIZATION / NOTES                                      --}}
    {{-- ========================================================================= --}}
    @if ($showItemNotesModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closeItemNotesModal"></div>
            <div class="inline-block bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all max-w-md w-full p-5 sm:p-6 border border-slate-200 dark:border-slate-700">
                <div class="flex justify-between items-center pb-3 border-b border-slate-200 dark:border-slate-700 mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"></path></svg>
                        </div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">
                            Catatan & Opsi Menu
                        </h3>
                    </div>
                    <button wire:click="closeItemNotesModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                @php
                    $categoryName = strtolower($cart[$editingItemIndex]['category_name'] ?? '');
                    $isDrink = str_contains($categoryName, 'coffee') || str_contains($categoryName, 'tea') || str_contains($categoryName, 'minuman');
                @endphp

                <div class="space-y-4">
                    @if($isDrink)
                        {{-- Sugar Level --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Sugar Level:</label>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach(['Normal', 'Less Sugar', 'No Sugar'] as $sugar)
                                    <button type="button" wire:click="$set('tempSugarLevel', '{{ $sugar }}')"
                                        class="w-full py-2 px-2 text-xs font-bold rounded-lg border transition-all text-center {{ $tempSugarLevel === $sugar ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-slate-400' }}">
                                        {{ $sugar }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Ice Level --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Ice Level:</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @foreach(['Normal', 'Less Ice', 'No Ice', 'Hot'] as $ice)
                                    <button type="button" wire:click="$set('tempIceLevel', '{{ $ice }}')"
                                        class="py-2 px-1 text-xs font-bold rounded-lg border transition-all text-center {{ $tempIceLevel === $ice ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-slate-400' }}">
                                        {{ $ice }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Free Text Notes --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">Catatan Tambahan (Dapur / Barista):</label>
                        <textarea wire:model="tempItemNotes" rows="3" placeholder="Contoh: Pisahkan saus, extra whipped cream, pedas manis..."
                            class="w-full px-3 py-2 text-xs border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-700">
                    <button wire:click="closeItemNotesModal" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">Batal</button>
                    <button wire:click="saveItemNotes" class="px-4 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors shadow-xs">Simpan Catatan</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 5. MODAL: PAYMENT / PEMBAYARAN                                            --}}
    {{-- ========================================================================= --}}
    @if ($showPaymentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closePaymentModal"></div>
                <div class="inline-block bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all max-w-xl w-full border border-slate-200 dark:border-slate-700">
                    <form wire:submit.prevent="processPayment">
                        {{-- Header --}}
                        <div class="bg-slate-900 dark:bg-slate-850 text-white px-6 py-4 flex justify-between items-center border-b border-slate-800">
                            <div>
                                <h3 class="text-base font-bold">Pembayaran Pesanan</h3>
                                <p class="text-xs text-slate-400 font-medium mt-0.5">Tipe: {{ $orderType === 'dine_in' ? 'Dine In' : ($orderType === 'take_away' ? 'Take Away' : 'Delivery') }} {{ $orderType === 'dine_in' ? ($tableNumber ? '| Meja: '.$tableNumber : '') : ($customerName ? '| Pelanggan: '.$customerName : '') }}</p>
                            </div>
                            <button type="button" wire:click="closePaymentModal" class="text-slate-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                        </div>
                        
                        <div class="bg-white dark:bg-slate-800 px-6 py-5 space-y-4">
                            {{-- TOTAL TAGIHAN --}}
                            <div class="bg-emerald-50/70 dark:bg-emerald-950/30 p-4 rounded-xl text-center border border-emerald-200/80 dark:border-emerald-800/60">
                                <p class="text-xs text-emerald-800 dark:text-emerald-300 font-bold mb-1 uppercase tracking-wider">TOTAL TAGIHAN</p>
                                <p class="text-2xl sm:text-3xl font-black text-emerald-700 dark:text-emerald-300">Rp {{ number_format($total, 0, ',', '.') }}</p>
                            </div>
                            
                            {{-- METODE PEMBAYARAN --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">Metode Pembayaran</label>
                                <div class="grid grid-cols-3 gap-2.5">
                                    {{-- Cash --}}
                                    <button type="button" wire:click="setPaymentMethod('cash')"
                                        class="p-2.5 border rounded-xl text-center transition-all font-bold text-xs flex flex-col items-center gap-1.5 {{ $paymentMethod === 'cash' ? 'border-emerald-600 bg-emerald-600 text-white shadow-xs' : 'border-slate-200 hover:border-slate-300 text-slate-700 dark:border-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"></path></svg>
                                        <span>Tunai (Cash)</span>
                                    </button>
                                    
                                    {{-- QRIS --}}
                                    <button type="button" wire:click="setPaymentMethod('qris')"
                                        class="p-2.5 border rounded-xl text-center transition-all font-bold text-xs flex flex-col items-center gap-1.5 {{ $paymentMethod === 'qris' ? 'border-emerald-600 bg-emerald-600 text-white shadow-xs' : 'border-slate-200 hover:border-slate-300 text-slate-700 dark:border-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM13.5 13.5h2.25v2.25H13.5V13.5zm3.75 0h3v3h-3v-3zm0 3.75h3v3h-3v-3zm-3.75 0h2.25v3H13.5v-3z"></path></svg>
                                        <span>QRIS</span>
                                    </button>

                                    {{-- Transfer --}}
                                    <button type="button" wire:click="setPaymentMethod('transfer')"
                                        class="p-2.5 border rounded-xl text-center transition-all font-bold text-xs flex flex-col items-center gap-1.5 {{ $paymentMethod === 'transfer' ? 'border-emerald-600 bg-emerald-600 text-white shadow-xs' : 'border-slate-200 hover:border-slate-300 text-slate-700 dark:border-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5m-15 0V21m0 0h18"></path></svg>
                                        <span>Transfer Bank</span>
                                    </button>
                                </div>
                            </div>

                            @if($paymentMethod === 'cash')
                                {{-- CASH PAYMENT: INPUT & PRESET PECAHAN --}}
                                <div class="space-y-3 pt-1">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">Uang Diterima (Rp)</label>
                                        <input type="number" wire:model.live="paid" id="paidInput" autofocus
                                            class="block w-full px-3.5 py-2.5 text-lg font-extrabold border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white"
                                            placeholder="0">
                                    </div>

                                    {{-- Pecahan Cepat --}}
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Pilihan Cepat Pecahan:</label>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                            <button type="button" wire:click="setExactPaid"
                                                class="px-2 py-2 text-xs font-bold border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 rounded-lg hover:bg-emerald-100 transition-colors flex items-center justify-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path></svg>
                                                <span>Uang Pas</span>
                                            </button>
                                            @foreach ([20000, 50000, 100000, 150000, 200000, 300000, 500000] as $amount)
                                                @if($amount >= $total)
                                                    <button type="button" wire:click="setPaidAmount({{ $amount }})"
                                                        class="px-2 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                                        Rp {{ number_format($amount, 0, ',', '.') }}
                                                    </button>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- KEMBALIAN BOX --}}
                                    @if ($change > 0)
                                        <div class="bg-emerald-50 dark:bg-emerald-950/40 p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-800 flex justify-between items-center">
                                            <p class="text-xs text-emerald-800 dark:text-emerald-300 font-bold uppercase tracking-wider">Kembalian Konsumen</p>
                                            <p class="text-xl font-extrabold text-emerald-800 dark:text-emerald-300">Rp {{ number_format($change, 0, ',', '.') }}</p>
                                        </div>
                                    @elseif($paid > 0 && $paid < $total)
                                        <div class="bg-rose-50 dark:bg-rose-950/40 p-3 rounded-xl border border-rose-200 dark:border-rose-800 flex justify-between items-center">
                                            <p class="text-xs text-rose-700 dark:text-rose-400 font-bold">Uang Kurang:</p>
                                            <p class="text-base font-bold text-rose-700 dark:text-rose-400">- Rp {{ number_format($total - $paid, 0, ',', '.') }}</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- NON-CASH PAYMENT --}}
                                <div class="bg-slate-50 dark:bg-slate-900/60 p-4 rounded-xl border border-slate-200 dark:border-slate-700 text-center space-y-1">
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"></path></svg>
                                        <span>Pembayaran Non-Tunai ({{ strtoupper($paymentMethod) }})</span>
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        Nominal otomatis uang pas sebesar <strong class="text-emerald-700 dark:text-emerald-400">Rp {{ number_format($total, 0, ',', '.') }}</strong> tanpa kembalian.
                                    </p>
                                </div>
                            @endif
                        </div>
                        
                        {{-- Action buttons --}}
                        <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 flex justify-end gap-3 border-t border-slate-200 dark:border-slate-700">
                            <button type="button" wire:click="closePaymentModal" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-white dark:hover:bg-slate-700 transition-colors">Batal</button>
                            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow-xs transition-all active:scale-[0.99]" {{ $paid < $total ? 'disabled' : '' }}>Selesaikan Transaksi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 6. MODAL: SUCCESS & PRINT RECEIPT                                         --}}
    {{-- ========================================================================= --}}
    @if ($showSuccessModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" @keydown.window.escape="$wire.closeSuccessModal()">
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                {{-- Backdrop Klik Dimana Saja untuk Menutup --}}
                <div wire:click="closeSuccessModal" class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs cursor-pointer" title="Klik untuk menutup"></div>
                
                <div class="inline-block bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-md w-full p-6 border border-slate-200 dark:border-slate-700 relative z-10">
                    {{-- Tombol Close X di Pojok Kanan Atas --}}
                    <button wire:click="closeSuccessModal" type="button" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <div class="text-center">
                        <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-1">Pesanan Berhasil Disimpan!</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Transaksi telah tercatat di sistem POS</p>
                        
                        <div class="bg-slate-50 dark:bg-slate-900/60 p-4 rounded-xl mb-4 border border-slate-200 dark:border-slate-700 text-left">
                            <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-2 mb-2">
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Invoice</span>
                                <span class="text-xs font-mono font-bold text-slate-900 dark:text-white">{{ $lastInvoice }}</span>
                            </div>
                            @if($lastTransaction)
                                <div class="text-xs space-y-1.5 text-slate-700 dark:text-slate-300">
                                    <div class="flex justify-between">
                                        <span>Tipe Pesanan:</span>
                                        <span class="font-bold text-slate-900 dark:text-white uppercase">{{ $lastTransaction->order_type === 'dine_in' ? 'Dine In' : ($lastTransaction->order_type === 'take_away' ? 'Take Away' : 'Delivery') }}</span>
                                    </div>
                                    @if($lastTransaction->order_type === 'dine_in' && $lastTransaction->table_number)
                                        <div class="flex justify-between">
                                            <span>Nomor Meja:</span>
                                            <span class="font-bold text-slate-900 dark:text-white">{{ $lastTransaction->table_number }}</span>
                                        </div>
                                    @endif
                                    @if($lastTransaction->customer_name)
                                        <div class="flex justify-between">
                                            <span>Pelanggan:</span>
                                            <span class="font-bold text-slate-900 dark:text-white">{{ $lastTransaction->customer_name }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between border-t border-slate-200 dark:border-slate-700 pt-1.5 mt-1.5">
                                        <span class="font-bold">Total:</span>
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($lastTransaction->total, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Bayar:</span>
                                        <span>Rp {{ number_format($lastTransaction->paid, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-bold">
                                        <span>Kembali:</span>
                                        <span>Rp {{ number_format($lastTransaction->change, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <button wire:click="closeSuccessModal" class="w-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-white py-2.5 rounded-xl font-bold hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors text-xs cursor-pointer">Transaksi Baru</button>
                            <button type="button" onclick="printStrukDirect('{{ $lastInvoice }}')" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl font-bold flex justify-center items-center gap-1.5 transition-all text-xs shadow-xs cursor-pointer active:scale-95">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                <span>Cetak Ulang</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 7. MODAL: BUKA SHIFT KASIR                                                --}}
    {{-- ========================================================================= --}}
    @if ($showStartShiftModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden transform transition-all">
                <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                Buka Shift Kasir
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Kasir: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ auth()->user()->name }}</span>
                            </p>
                        </div>
                    </div>
                    <button wire:click="closeStartShiftModal" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                            Modal Kas Awal di Laci (Uang Kembalian):
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-bold text-slate-400">Rp</span>
                            <input type="text" wire:model.live="formattedStartingCash" placeholder="0" autofocus
                                class="w-full pl-11 pr-4 py-2.5 text-lg font-black border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                        </div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1.5">Masukkan uang kas fisik yang ada di laci kasir saat memulai shift.</p>
                    </div>

                    {{-- Quick Preset Chips --}}
                    <div>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 block mb-1.5 uppercase tracking-wider">Pilihan Cepat:</span>
                        <div class="grid grid-cols-4 gap-2">
                            <button type="button" wire:click="setStartingCashPreset(50000)" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-700 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-emerald-950/40 text-xs font-bold rounded-lg transition text-slate-700 dark:text-slate-200">
                                50 Rb
                            </button>
                            <button type="button" wire:click="setStartingCashPreset(100000)" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-700 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-emerald-950/40 text-xs font-bold rounded-lg transition text-slate-700 dark:text-slate-200">
                                100 Rb
                            </button>
                            <button type="button" wire:click="setStartingCashPreset(200000)" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-700 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-emerald-950/40 text-xs font-bold rounded-lg transition text-slate-700 dark:text-slate-200">
                                200 Rb
                            </button>
                            <button type="button" wire:click="setStartingCashPreset(500000)" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-700 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-emerald-950/40 text-xs font-bold rounded-lg transition text-slate-700 dark:text-slate-200">
                                500 Rb
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
                    <button type="button" wire:click="closeStartShiftModal" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold hover:bg-slate-300 dark:hover:bg-slate-600 transition">
                        Batal
                    </button>
                    <button type="button" wire:click="startShift" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                        <span>Mulai Buka Shift</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 8. MODAL: TUTUP SHIFT & REKAP KASIR                                       --}}
    {{-- ========================================================================= --}}
    @if ($showEndShiftModal && $activeShift)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden transform transition-all">
                <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                Tutup Shift & Rekonsiliasi Kas
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Shift #SFT-{{ str_pad($activeShift->id, 5, '0', STR_PAD_LEFT) }} • Kasir: {{ $activeShift->user->name ?? auth()->user()->name }}
                            </p>
                        </div>
                    </div>
                    <button wire:click="closeEndShiftModal" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto scrollbar-thin">
                    {{-- Rekap Ringkas Sistem --}}
                    <div class="bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700 rounded-xl p-3.5 space-y-2 text-xs">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Modal Kas Awal:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($activeShift->starting_cash, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>(+) Penjualan Tunai:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($activeShift->cash_sales, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between pt-1 border-t border-slate-200 dark:border-slate-700 font-extrabold text-slate-900 dark:text-white text-sm">
                            <span>(=) Total Kas Diharapkan di Laci:</span>
                            <span class="text-emerald-600 dark:text-emerald-400">Rp {{ number_format($activeShift->expected_cash, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500 pt-1 text-[11px]">
                            <span>Penjualan Non-Tunai (QRIS & Transfer):</span>
                            <span>Rp {{ number_format($activeShift->qris_sales + $activeShift->transfer_sales, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Input Uang Fisik Aktual --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1.5">
                            Hitung Uang Fisik Aktual di Laci Kasir:
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-bold text-slate-400">Rp</span>
                            <input type="text" wire:model.live="formattedActualCash" placeholder="0" autofocus
                                class="w-full pl-11 pr-4 py-2.5 text-lg font-black border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-rose-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                        </div>
                    </div>

                    {{-- Status Selisih Real-time Preview --}}
                    <div class="p-3.5 rounded-xl border {{ $shiftDifference == 0 ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300' : ($shiftDifference < 0 ? 'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300' : 'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300') }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider">Hasil Selisih Kas:</span>
                            <span class="text-sm font-extrabold flex items-center gap-1.5">
                                @if ($shiftDifference == 0)
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path></svg>
                                    <span>Sesuai / Pas (Rp 0)</span>
                                @elseif ($shiftDifference < 0)
                                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path></svg>
                                    <span>Selisih Kurang: -Rp {{ number_format(abs($shiftDifference), 0, ',', '.') }}</span>
                                @else
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path></svg>
                                    <span>Selisih Lebih: +Rp {{ number_format($shiftDifference, 0, ',', '.') }}</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Catatan Penutupan Shift --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            Catatan Shift (Opsional):
                        </label>
                        <textarea wire:model="shiftNotes" rows="2" placeholder="Contoh: Titipan uang receh aman, selisih pas..."
                            class="w-full px-3 py-2 text-xs border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white"></textarea>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
                    <button type="button" wire:click="closeEndShiftModal" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold hover:bg-slate-300 dark:hover:bg-slate-600 transition">
                        Batal
                    </button>
                    <button type="button" wire:click="endShift" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path></svg>
                        <span>Tutup Shift & Cetak Rekap</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL QUICK KETERSEDIAAN MENU & KATEGORI                                  --}}
    {{-- ========================================================================= --}}
    @if ($showAvailabilityModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col max-h-[88vh] animate-scale-up">
                
                {{-- Header Modal --}}
                <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Kontrol Ketersediaan Menu</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Aktifkan atau nonaktifkan menu & kategori yang habis secara instan</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeAvailabilityModal" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-lg transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Tab Pilihan: Produk vs Kategori --}}
                <div class="px-4 sm:px-5 pt-3 border-b border-slate-200 dark:border-slate-700 flex gap-2">
                    <button type="button" wire:click="setAvailabilityTab('products')"
                        class="pb-2.5 px-3 text-xs font-bold transition-all border-b-2 flex items-center gap-1.5 cursor-pointer {{ $availabilityTab === 'products' ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                        <span>Menu Produk</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $availabilityTab === 'products' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400' }}">
                            {{ count($availabilityProducts) }}
                        </span>
                    </button>
                    <button type="button" wire:click="setAvailabilityTab('categories')"
                        class="pb-2.5 px-3 text-xs font-bold transition-all border-b-2 flex items-center gap-1.5 cursor-pointer {{ $availabilityTab === 'categories' ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }}">
                        <span>Kategori Menu</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $availabilityTab === 'categories' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400' }}">
                            {{ count($allCategories) }}
                        </span>
                    </button>
                </div>

                {{-- Filter & Search di dalam Modal --}}
                <div class="p-3 sm:p-4 bg-slate-50/50 dark:bg-slate-900/30 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row gap-2">
                    <div class="relative flex-1">
                        <input type="text" wire:model.live.debounce.250ms="availabilitySearch"
                            placeholder="Cari nama menu / SKU..."
                            class="w-full pl-8 pr-3 py-1.5 text-xs border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>

                    @if($availabilityTab === 'products')
                        <select wire:model.live="availabilityCategoryFilter"
                            class="px-3 py-1.5 text-xs border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer w-full sm:w-44">
                            <option value="">Semua Kategori</option>
                            @foreach ($allCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                {{-- Daftar Item (Scrollable) --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-2.5 min-h-[250px] max-h-[420px] scrollbar-thin">
                    @if($availabilityTab === 'products')
                        @forelse($availabilityProducts as $prod)
                            <div class="flex items-center justify-between p-2.5 sm:p-3 rounded-xl border {{ $prod->is_active ? 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700' : 'bg-slate-100/70 dark:bg-slate-900/60 border-dashed border-slate-300 dark:border-slate-700' }} transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-700 shrink-0 flex items-center justify-center border border-slate-200 dark:border-slate-600">
                                        @if($prod->image)
                                            <img src="{{ Storage::url($prod->image) }}" alt="{{ $prod->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-sm">☕</span>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white {{ !$prod->is_active ? 'line-through text-slate-400 dark:text-slate-500' : '' }}">
                                            {{ $prod->name }}
                                        </h4>
                                        <div class="flex items-center gap-2 text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            <span>{{ $prod->category->name ?? '-' }}</span>
                                            <span>•</span>
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($prod->price, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Toggle Switch --}}
                                <div class="flex items-center gap-2.5 shrink-0">
                                    <span class="text-[11px] font-bold {{ $prod->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                        {{ $prod->is_active ? 'Tersedia' : 'Habis / Kosong' }}
                                    </span>
                                    <button type="button" wire:click="toggleProductAvailability({{ $prod->id }})"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden {{ $prod->is_active ? 'bg-emerald-600' : 'bg-slate-300 dark:bg-slate-600' }}">
                                        <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out {{ $prod->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-slate-400 text-xs">
                                Tidak ada menu ditemukan.
                            </div>
                        @endforelse
                    @else
                        @forelse($allCategories as $cat)
                            <div class="flex items-center justify-between p-3 rounded-xl border {{ $cat->is_active ? 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700' : 'bg-slate-100/70 dark:bg-slate-900/60 border-dashed border-slate-300 dark:border-slate-700' }} transition-colors">
                                <div>
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white {{ !$cat->is_active ? 'line-through text-slate-400 dark:text-slate-500' : '' }}">
                                        {{ $cat->name }}
                                    </h4>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ $cat->products_count }} Menu Produk Terkait
                                    </p>
                                </div>

                                {{-- Toggle Switch --}}
                                <div class="flex items-center gap-2.5 shrink-0">
                                    <span class="text-[11px] font-bold {{ $cat->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                        {{ $cat->is_active ? 'Aktif di POS' : 'Non-Aktif (Hidden)' }}
                                    </span>
                                    <button type="button" wire:click="toggleCategoryAvailability({{ $cat->id }})"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden {{ $cat->is_active ? 'bg-emerald-600' : 'bg-slate-300 dark:bg-slate-600' }}">
                                        <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out {{ $cat->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-slate-400 text-xs">
                                Tidak ada kategori ditemukan.
                            </div>
                        @endforelse
                    @endif
                </div>

                {{-- Footer Modal --}}
                <div class="p-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center text-xs">
                    <p class="text-slate-500 dark:text-slate-400 text-[11px]">Perubahan ketersediaan langsung diterapkan secara real-time.</p>
                    <button type="button" wire:click="closeAvailabilityModal"
                        class="px-4 py-2 bg-slate-900 dark:bg-emerald-600 text-white rounded-lg font-bold shadow-xs hover:bg-slate-800 dark:hover:bg-emerald-700 transition cursor-pointer">
                        Selesai
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
    document.addEventListener('keydown', function(e) {
        const searchInput = document.getElementById('searchInput');
        if (e.key.length === 1 && document.activeElement !== searchInput && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            searchInput.focus();
        }
        if (e.key === '/' && document.activeElement !== searchInput) {
            e.preventDefault();
            searchInput.focus();
        }
        if (e.key === 'F9') {
            e.preventDefault();
            @this.call('openPaymentModal');
        }
        if (e.key === 'Escape') {
            @this.call('closePaymentModal');
            @this.call('closeItemNotesModal');
            @this.call('closeMobileCart');
            @this.call('closeStartShiftModal');
            @this.call('closeEndShiftModal');
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    @this.call('scanBarcode');
                }
            });
        }

        Livewire.on('open-print-shift-tab', (event) => {
            if (event.url) {
                window.open(event.url, '_blank');
            }
        });

        // AUTO-PRINT INSTAN BEGITU TRANSAKSI SELESAI (SILENT BACKGROUND PRINT)
        Livewire.on('transaction-completed', async (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            const invoice = data.invoice || data.invoice_number || data;
            
            if (invoice && window.posBluetooth && window.posBluetooth.isConnected) {
                try {
                    await window.posBluetooth.printInvoice(invoice);
                } catch (err) {
                    console.warn('Auto print bluetooth error:', err);
                }
            }
        });
    });

    async function printStrukDirect(invoice) {
        if (!invoice) return;

        // 1. PRIORITAS UTAMA: DIRECT WEB BLUETOOTH / USB (INSTAN TANPA NOTIFIKASI GANGGUAN)
        if (window.posBluetooth && window.posBluetooth.isConnected) {
            try {
                await window.posBluetooth.printInvoice(invoice);
                return;
            } catch (err) {
                console.warn('Bluetooth/USB print gagal, beralih ke fallback...', err);
            }
        }

        // 2. FALLBACK ANDROID: RAWBT
        const isAndroid = /Android/i.test(navigator.userAgent);
        if (isAndroid) {
            fetch('/rawbt-struk/' + invoice)
                .then(res => res.json())
                .then(data => {
                    if (data.rawbt) {
                        window.location.href = "rawbt:base64," + data.rawbt + "#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";
                    } else {
                        window.open('/print-struk/' + invoice, '_blank');
                    }
                })
                .catch(() => {
                    window.open('/print-struk/' + invoice, '_blank');
                });
            return;
        }

        // 3. FALLBACK DESKTOP: BROWSER PRINT TAB
        window.open('/print-struk/' + invoice, '_blank');
    }

    function confirmResetCart() {
        Swal.fire({
            title: 'Kosongkan Keranjang Cafe?',
            text: "Semua daftar pesanan akan dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Batal',
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('resetTransaction');
            }
        });
    }
</script>
@endpush