<div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-20 md:pb-0 transition-colors duration-300"
     x-data="{ sidebarOpen: window.innerWidth >= 1280 }"
     @resize.window="if (window.innerWidth >= 1280) { sidebarOpen = true } else { sidebarOpen = false }">
    
    @include('livewire.includes.sidebar')

    <div class="xl:pl-64 transition-all duration-300 flex flex-col min-h-screen">
        @include('livewire.includes.header', [
            'title' => 'POS Kasir Cafe', 
            'subtitle' => 'Sistem Pemesanan & Kasir Cafe'
        ])

        <main class="p-3 sm:p-4 lg:p-6 space-y-4 flex-1">
            
            {{-- TOP BAR: ORDER TYPE & TABLE NUMBER SELECTION --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl p-3.5 sm:p-4 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4">
                
                {{-- Order Type Switcher --}}
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        Tipe Pesanan:
                    </span>
                    <div class="inline-flex flex-wrap rounded-lg p-1 bg-slate-100 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 gap-1">
                        <button type="button" wire:click="setOrderType('dine_in')"
                            class="px-3 sm:px-4 py-1.5 rounded-md text-xs font-semibold transition-all flex items-center gap-1.5 {{ $orderType === 'dine_in' ? 'bg-slate-900 text-white dark:bg-blue-600 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                            🍽️ Makan di Tempat (Meja)
                        </button>
                        <button type="button" wire:click="setOrderType('take_away')"
                            class="px-3 sm:px-4 py-1.5 rounded-md text-xs font-semibold transition-all flex items-center gap-1.5 {{ $orderType === 'take_away' ? 'bg-slate-900 text-white dark:bg-blue-600 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                            🛍️ Bawa Pulang (Take Away)
                        </button>
                        <button type="button" wire:click="setOrderType('delivery')"
                            class="px-3 sm:px-4 py-1.5 rounded-md text-xs font-semibold transition-all flex items-center gap-1.5 {{ $orderType === 'delivery' ? 'bg-slate-900 text-white dark:bg-blue-600 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                            🚚 Pesan Antar (Delivery)
                        </button>
                    </div>
                </div>

                {{-- Dynamic Inputs based on Order Type --}}
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    @if($orderType === 'dine_in')
                        <div class="flex flex-wrap items-center gap-2" wire:key="input-container-table">
                            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Pilih Meja:</label>
                            <select wire:model.live="selectedTable" class="px-3 py-1.5 text-xs font-semibold border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer w-36 sm:w-40">
                                <option value="">-- Pilih Meja --</option>
                                @for ($i = 1; $i <= 20; $i++)
                                    <option value="{{ sprintf('%02d', $i) }}">{{ sprintf('%02d', $i) }}</option>
                                @endfor
                                <option value="custom">✏️ Ketik Manual...</option>
                            </select>

                            @if($isCustomTable)
                                <input type="text" wire:model.live="customTableNumber" placeholder="Contoh: Meja 04+05" autofocus
                                    class="px-3 py-1.5 text-xs font-semibold border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white w-36">
                            @endif
                        </div>
                    @else
                        <div class="flex items-center gap-2" wire:key="input-container-customer">
                            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Nama Pelanggan:</label>
                            <input type="text" wire:model.live="customerName" wire:key="input-customer-name" placeholder="Nama Pelanggan"
                                class="px-3 py-1.5 text-xs font-semibold border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white w-44 sm:w-48">
                        </div>
                    @endif
                </div>
            </div>

            {{-- SHIFT STATUS & MANAGEMENT BANNER --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl px-4 py-3 border border-slate-200/80 dark:border-slate-700/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    @if ($activeShift)
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-bold text-slate-800 dark:text-white">
                                Shift Aktif (#SFT-{{ str_pad($activeShift->id, 5, '0', STR_PAD_LEFT) }})
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500">•</span>
                            <span class="text-xs text-slate-600 dark:text-slate-300">
                                Buka: <strong class="text-slate-800 dark:text-white">{{ $activeShift->start_time ? $activeShift->start_time->format('H:i') : '-' }}</strong>
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500 hidden md:inline">•</span>
                            <span class="text-xs text-slate-600 dark:text-slate-300 hidden md:inline">
                                Modal Kas: <strong class="text-emerald-600 dark:text-emerald-400">Rp {{ number_format($activeShift->starting_cash, 0, ',', '.') }}</strong>
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500 hidden lg:inline">•</span>
                            <span class="text-xs text-slate-600 dark:text-slate-300 hidden lg:inline">
                                Kas Laci Saat Ini: <strong class="text-slate-900 dark:text-white">Rp {{ number_format($activeShift->expected_cash, 0, ',', '.') }}</strong>
                            </span>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-xs">
                            <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 font-bold flex items-center gap-1">
                                ⚠️ Shift Belum Dibuka
                            </span>
                            <span class="text-slate-500 dark:text-slate-400">Buka shift untuk mencatat modal kas awal & rekonsiliasi laci.</span>
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @if ($activeShift)
                        <button type="button" wire:click="openEndShiftModal"
                            class="px-3.5 py-1.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/50 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Tutup Shift & Rekap Kas
                        </button>
                    @else
                        <button type="button" wire:click="openStartShiftModal"
                            class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                            ⚡ Buka Shift Kasir
                        </button>
                    @endif
                </div>
            </div>

            {{-- MAIN LAYOUT: SPLIT SCREEN KIRI (PRODUK) & KANAN (KERANJANG MD+) --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-6 items-start">
                
                {{-- LEFT COLUMN: SEARCH & COMPACT PRODUCTS (7 cols on md, 8 cols on xl) --}}
                <div class="md:col-span-7 xl:col-span-8 space-y-4">
                    
                    {{-- SEARCH & CATEGORY SELECTOR --}}
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-3.5 sm:p-4 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="flex-1 relative">
                                <input type="text" wire:model.live="search"
                                    id="searchInput"
                                    placeholder="Cari menu / scan barcode..."
                                    class="w-full pl-10 pr-10 py-2 sm:py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 text-xs"
                                    autocomplete="off">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-slate-400 absolute left-3 top-2.5 sm:top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                @if(!empty($search))
                                    <button type="button" wire:click="clearSearch"
                                        class="absolute right-3 top-2 sm:top-2.5 text-slate-400 hover:text-slate-600 p-1 rounded-md"
                                        title="Hapus pencarian">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                @endif
                            </div>
                            <select wire:model.live="selectedCategory" class="px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer text-xs font-medium w-full sm:w-56">
                                <option value="">Semua Menu ({{ $categories->sum('products_count') }})</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->products_count }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Products Grid: Card Mungil Compact (Tablet: square + title, Desktop XL: detail lengkap) --}}
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-3 2xl:grid-cols-4 gap-2 sm:gap-3 xl:gap-4">
                        @forelse($products as $product)
                            @php
                                $inCartQty = $this->getCartQuantity($product->id);
                            @endphp
                            <div wire:click="addToCart({{ $product->id }})"
                                class="bg-white dark:bg-slate-800 rounded-xl p-2 sm:p-2.5 xl:p-3.5 border {{ $inCartQty > 0 ? 'border-slate-900 dark:border-blue-500 ring-2 ring-slate-900/10 dark:ring-blue-500/20' : 'border-slate-200/80 dark:border-slate-700/80' }} shadow-2xs hover:border-slate-400 dark:hover:border-slate-500 transition-all active:scale-[0.96] flex flex-col justify-between h-full group relative overflow-hidden cursor-pointer">
                                
                                {{-- In-Cart Badge Indicator --}}
                                @if($inCartQty > 0)
                                    <div class="absolute top-1.5 right-1.5 xl:top-2 xl:right-2 z-10 bg-slate-900 dark:bg-blue-600 text-white text-[9px] xl:text-[10px] font-bold px-1.5 py-0.5 rounded-md shadow-xs">
                                        {{ $inCartQty }}x
                                    </div>
                                @endif

                                <div>
                                    {{-- Frame Gambar --}}
                                    <div class="w-full aspect-square xl:aspect-[4/3] rounded-lg mb-1.5 xl:mb-2.5 overflow-hidden bg-slate-100 dark:bg-slate-700 relative flex items-center justify-center">
                                        @if ($product->image)
                                            <img src="{{ Storage::url($product->image) }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-slate-100 dark:bg-slate-700/60 group-hover:bg-slate-200/60 transition-colors">
                                                <span class="text-2xl sm:text-3xl">
                                                    @if(str_contains(strtolower($product->name), 'coffee') || str_contains(strtolower($product->name), 'latte') || str_contains(strtolower($product->name), 'americano') || str_contains(strtolower($product->name), 'v60'))
                                                        ☕
                                                    @elseif(str_contains(strtolower($product->name), 'tea') || str_contains(strtolower($product->name), 'matcha') || str_contains(strtolower($product->name), 'chocolate'))
                                                        🍵
                                                    @elseif(str_contains(strtolower($product->name), 'croissant') || str_contains(strtolower($product->name), 'brownies'))
                                                        🥐
                                                    @elseif(str_contains(strtolower($product->name), 'nasi') || str_contains(strtolower($product->name), 'spaghetti') || str_contains(strtolower($product->name), 'katsu'))
                                                        🍝
                                                    @else
                                                        🍟
                                                    @endif
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    {{-- Judul Menu --}}
                                    <h3 class="font-bold text-slate-900 dark:text-white text-[11px] sm:text-xs xl:text-sm line-clamp-2 leading-tight group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors text-center xl:text-left">
                                        {{ $product->name }}
                                    </h3>
                                    
                                    {{-- Deskripsi (Desktop XL Only) --}}
                                    <p class="hidden xl:block text-[10px] text-slate-500 dark:text-slate-400 line-clamp-1 mb-2 mt-0.5">
                                        {{ $product->description }}
                                    </p>
                                </div>
                                
                                {{-- Harga & Plus Button (Desktop XL Only) --}}
                                <div class="hidden xl:flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-700/60 mt-1">
                                    <p class="font-bold text-emerald-600 dark:text-emerald-400 text-xs sm:text-sm">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                    <span class="text-xs bg-slate-900 dark:bg-blue-600 text-white rounded-lg p-1.5 group-hover:bg-slate-800 dark:group-hover:bg-blue-700 transition-colors shadow-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <div class="bg-white dark:bg-slate-800 rounded-xl p-8 border border-slate-200 dark:border-slate-700 border-dashed">
                                    <span class="text-4xl block mb-2">☕</span>
                                    <p class="text-slate-500 dark:text-slate-400 font-medium text-xs sm:text-sm">Tidak ada menu cafe ditemukan</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- RIGHT COLUMN: KERANJANG PERMANEN (TINGGI PAS DENGAN VIEWPORT LAYAR MD & XL, TIDAK SCROLL HALAMAN) --}}
                <div class="hidden md:flex md:col-span-5 xl:col-span-4 flex-col bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm h-[calc(100vh-12.5rem)] sticky top-4 overflow-hidden transition-colors">
                    
                    {{-- Header Keranjang --}}
                    <div class="p-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-700/50 flex justify-between items-center shrink-0">
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 text-xs sm:text-sm">
                                🛒 Pesanan Menu
                            </h3>
                            <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold uppercase">
                                Mode: {{ $orderType === 'dine_in' ? 'MAKAN DI TEMPAT' : ($orderType === 'take_away' ? 'BAWA PULANG' : 'PESAN ANTAR') }} 
                                @if($orderType === 'dine_in' && $tableNumber)
                                    (MEJA {{ $tableNumber }})
                                @endif
                            </p>
                        </div>
                        @if(count($cart) > 0)
                            <button onclick="confirmResetCart()" class="text-xs text-rose-500 hover:underline font-semibold">Kosongkan</button>
                        @endif
                    </div>

                    {{-- Daftar Item Pesanan (Scroll Mandiri di Dalam Box) --}}
                    <div class="p-2.5 sm:p-3 flex-1 overflow-y-auto space-y-2 scrollbar-thin dark:scrollbar-thumb-slate-700 min-h-0">
                        @include('livewire.pos.partials.cart-items')
                    </div>

                    {{-- Summary & CTA Bayar Terkunci Pas di Bawah --}}
                    @if (!empty($cart))
                        <div class="p-3 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shrink-0 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                            @include('livewire.pos.partials.cart-summary')
                        </div>
                    @endif
                </div>

            </div>
        </main>
    </div>

    {{-- FLOATING CART BAR KHUSUS SMARTPHONE KECIL (< MD) --}}
    @if(count($cart) > 0)
    <div class="fixed bottom-0 left-0 w-full bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 p-3.5 shadow-2xl md:hidden z-40 flex justify-between items-center gap-4 transition-colors">
        <div class="flex flex-col">
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ count($cart) }} Menu di keranjang</span>
            <span class="text-base font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>
        <button wire:click="openMobileCart" class="bg-slate-900 dark:bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2 shadow-md active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            Lihat Pesanan
        </button>
    </div>
    @endif

    {{-- BOTTOM SHEET MODAL KERANJANG KHUSUS SMARTPHONE (< MD) --}}
    @if ($showMobileCart)
    <div class="fixed inset-0 z-50 overflow-y-auto md:hidden">
        <div class="flex items-end justify-center min-h-screen text-center">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closeMobileCart"></div>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-t-2xl text-left overflow-hidden shadow-xl transform transition-all w-full h-[88vh] flex flex-col border-t border-slate-200 dark:border-slate-700">
                <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-700/50">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base">Rincian Pesanan</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Mode: {{ $orderType === 'dine_in' ? 'Makan di Tempat' : ($orderType === 'take_away' ? 'Bawa Pulang' : 'Pesan Antar') }}</p>
                    </div>
                    <button wire:click="closeMobileCart" class="text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white p-2 bg-white dark:bg-slate-700 rounded-full shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
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

    {{-- ITEM CUSTOMIZATION / NOTES MODAL --}}
    @if ($showItemNotesModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closeItemNotesModal"></div>
            <div class="inline-block bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all max-w-md w-full p-5 sm:p-6 border border-slate-200 dark:border-slate-700">
                <div class="flex justify-between items-center pb-3 border-b border-slate-200 dark:border-slate-700 mb-4">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                        📝 Catatan & Pilihan Pesanan
                    </h3>
                    <button wire:click="closeItemNotesModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                @php
                    $categoryName = strtolower($cart[$editingItemIndex]['category_name'] ?? '');
                    $isDrink = str_contains($categoryName, 'coffee') || str_contains($categoryName, 'tea') || str_contains($categoryName, 'minuman');
                @endphp

                <div class="space-y-4">
                    @if($isDrink)
                        {{-- Sugar Level (Grid 3 Kolom Seimbang) --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Sugar Level:</label>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach(['Normal', 'Less Sugar', 'No Sugar'] as $sugar)
                                    <button type="button" wire:click="$set('tempSugarLevel', '{{ $sugar }}')"
                                        class="w-full py-2 px-2 text-xs font-semibold rounded-lg border transition-all text-center {{ $tempSugarLevel === $sugar ? 'bg-slate-900 text-white dark:bg-blue-600 dark:text-white border-slate-900 dark:border-blue-600 shadow-sm' : 'bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-slate-400' }}">
                                        {{ $sugar }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Ice Level --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Ice Level:</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @foreach(['Normal', 'Less Ice', 'No Ice', 'Hot'] as $ice)
                                    <button type="button" wire:click="$set('tempIceLevel', '{{ $ice }}')"
                                        class="py-2 px-1 text-[11px] font-semibold rounded-lg border transition-all text-center {{ $tempIceLevel === $ice ? 'bg-slate-900 text-white dark:bg-blue-600 dark:text-white border-slate-900 dark:border-blue-600' : 'bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-slate-400' }}">
                                        {{ $ice }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Free Text Notes --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan (Dapur/Barista):</label>
                        <textarea wire:model="tempItemNotes" rows="3" placeholder="Contoh: Pisahkan saus, extra whipped cream, pedas manis..."
                            class="w-full px-3 py-2 text-xs border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-700">
                    <button wire:click="closeItemNotesModal" class="px-4 py-2 text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">Batal</button>
                    <button wire:click="saveItemNotes" class="px-4 py-2 text-xs font-medium bg-slate-900 dark:bg-blue-600 text-white rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors shadow-sm">Simpan Catatan</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- PAYMENT MODAL --}}
    @if ($showPaymentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closePaymentModal"></div>
                <div class="inline-block bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all max-w-xl w-full border border-slate-200 dark:border-slate-700">
                    <form wire:submit.prevent="processPayment">
                        {{-- Header --}}
                        <div class="bg-slate-900 dark:bg-slate-700 text-white px-6 py-4 flex justify-between items-center border-b border-slate-800 dark:border-slate-600">
                            <div>
                                <h3 class="text-base font-bold">Pembayaran Pesanan</h3>
                                <p class="text-xs opacity-90 font-medium">Tipe: {{ $orderType === 'dine_in' ? 'MAKAN DI TEMPAT' : ($orderType === 'take_away' ? 'BAWA PULANG' : 'PESAN ANTAR') }} {{ $orderType === 'dine_in' ? ($tableNumber ? '| Meja: '.$tableNumber : '') : ($customerName ? '| Pelanggan: '.$customerName : '') }}</p>
                            </div>
                            <button type="button" wire:click="closePaymentModal" class="text-white hover:opacity-80"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                        </div>
                        
                        <div class="bg-white dark:bg-slate-800 px-6 py-4 space-y-4">
                            {{-- TOTAL TAGIHAN --}}
                            <div class="bg-slate-50 dark:bg-slate-900/60 p-4 rounded-xl text-center border border-slate-200 dark:border-slate-700">
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold mb-1 uppercase tracking-wider">TOTAL TAGIHAN</p>
                                <p class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($total, 0, ',', '.') }}</p>
                            </div>
                            
                            {{-- METODE PEMBAYARAN --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2 uppercase">Metode Pembayaran</label>
                                <div class="grid grid-cols-3 gap-2.5">
                                    @foreach(['cash' => '💵 Tunai', 'qris' => '📱 QRIS', 'transfer' => '🏦 Transfer'] as $val => $label)
                                    <button type="button" wire:click="setPaymentMethod('{{$val}}')"
                                        class="p-2.5 border rounded-lg text-center transition-all font-bold text-xs {{ $paymentMethod === $val ? 'border-slate-900 bg-slate-900 text-white dark:border-blue-600 dark:bg-blue-600 dark:text-white shadow-sm' : 'border-slate-300 hover:border-slate-400 text-slate-700 dark:border-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900' }}">
                                        <span>{{ $label }}</span>
                                    </button>
                                    @endforeach
                                </div>
                            </div>

                            @if($paymentMethod === 'cash')
                                {{-- CASH PAYMENT: INPUT & PRESET PECAHAN --}}
                                <div class="space-y-3 pt-1">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1 uppercase">Uang Diterima (Rp)</label>
                                        <input type="number" wire:model.live="paid" id="paidInput" autofocus
                                            class="block w-full px-3.5 py-2.5 text-lg font-bold border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white"
                                            placeholder="0">
                                    </div>

                                    {{-- Pecahan Cepat --}}
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Pilihan Cepat Pecahan:</label>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                            <button type="button" wire:click="setExactPaid"
                                                class="px-2 py-2 text-xs font-bold border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 rounded-lg hover:bg-emerald-100 transition-colors">
                                                ⚡ Uang Pas
                                            </button>
                                            @foreach ([20000, 50000, 100000, 150000, 200000, 300000, 500000] as $amount)
                                                @if($amount >= $total)
                                                    <button type="button" wire:click="setPaidAmount({{ $amount }})"
                                                        class="px-2 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                                        Rp {{ number_format($amount, 0, ',', '.') }}
                                                    </button>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- KEMBALIAN BOX --}}
                                    @if ($change > 0)
                                        <div class="bg-emerald-50 dark:bg-emerald-950/40 p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-800 flex justify-between items-center">
                                            <p class="text-xs text-emerald-700 dark:text-emerald-400 font-bold uppercase">Kembalian Konsumen</p>
                                            <p class="text-xl font-bold text-emerald-800 dark:text-emerald-300">Rp {{ number_format($change, 0, ',', '.') }}</p>
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
                                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 flex items-center justify-center gap-1.5">
                                        <span>🔒</span>
                                        <span>Pembayaran Non-Tunai ({{ strtoupper($paymentMethod) }})</span>
                                    </p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">
                                        Nominal otomatis uang pas sebesar <strong class="text-slate-800 dark:text-slate-200">Rp {{ number_format($total, 0, ',', '.') }}</strong> tanpa kembalian.
                                    </p>
                                </div>
                            @endif
                        </div>
                        
                        {{-- Action buttons --}}
                        <div class="bg-slate-50 dark:bg-slate-700/50 px-6 py-4 flex justify-end gap-3 border-t border-slate-200 dark:border-slate-700">
                            <button type="button" wire:click="closePaymentModal" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 font-medium text-xs hover:bg-white dark:hover:bg-slate-700 transition-colors">Batal</button>
                            <button type="submit" class="px-5 py-2 bg-slate-900 dark:bg-blue-600 hover:bg-slate-800 dark:hover:bg-blue-700 text-white rounded-lg font-bold text-xs shadow-sm transition-all active:scale-95" {{ $paid < $total ? 'disabled' : '' }}>Selesaikan Transaksi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- SUCCESS & PRINT RECEIPT MODAL --}}
    @if ($showSuccessModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs"></div>
                <div class="inline-block bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all max-w-md w-full p-6 border border-slate-200 dark:border-slate-700">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/40 rounded-full flex items-center justify-center mx-auto mb-3 animate-bounce text-emerald-600 dark:text-emerald-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-1">Pesanan Berhasil Disimpan!</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Transaksi telah tercatat di sistem POS</p>
                        
                        <div class="bg-slate-50 dark:bg-slate-900/60 p-4 rounded-xl mb-4 border border-slate-200 dark:border-slate-700 text-left">
                            <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-2 mb-2">
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Invoice</span>
                                <span class="text-xs font-mono font-bold text-slate-900 dark:text-white">{{ $lastInvoice }}</span>
                            </div>
                            @if($lastTransaction)
                                <div class="text-xs space-y-1 text-slate-700 dark:text-slate-300">
                                    <div class="flex justify-between">
                                        <span>Tipe Pesanan:</span>
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400 uppercase">{{ $lastTransaction->order_type === 'dine_in' ? 'Makan di Tempat' : ($lastTransaction->order_type === 'take_away' ? 'Bawa Pulang' : 'Pesan Antar') }}</span>
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
                                    <div class="flex justify-between border-t border-slate-200 dark:border-slate-700 pt-1 mt-1">
                                        <span class="font-bold">Total:</span>
                                        <span class="font-bold text-slate-900 dark:text-white">Rp {{ number_format($lastTransaction->total, 0, ',', '.') }}</span>
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
                            <button wire:click="closeSuccessModal" class="w-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-white py-2.5 rounded-lg font-bold hover:bg-slate-200 transition-colors text-xs">Transaksi Baru</button>
                            <a href="{{ route('print.struk', $lastInvoice) }}" target="_blank" class="w-full bg-slate-900 dark:bg-blue-600 text-white py-2.5 rounded-lg font-bold flex justify-center items-center gap-1.5 hover:bg-slate-800 dark:hover:bg-blue-700 transition-all text-xs shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Cetak Struk
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL BUKA SHIFT KASIR --}}
    @if ($showStartShiftModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden transform transition-all">
                <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                            ⚡
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
                    <button wire:click="closeStartShiftModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">
                            Modal Kas Awal di Laci (Uang Kembalian):
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-bold text-slate-400">Rp</span>
                            <input type="text" wire:model.live="formattedStartingCash" placeholder="0" autofocus
                                class="w-full pl-10 pr-4 py-2.5 text-lg font-extrabold border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                        </div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Masukkan uang kas fisik di laci saat memulai shift.</p>
                    </div>

                    {{-- Quick Preset Chips --}}
                    <div>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 block mb-1.5">Pilihan Cepat Nominal:</span>
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
                    <button type="button" wire:click="startShift" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                        ⚡ Mulai Buka Shift
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL TUTUP SHIFT & REKAP KASIR --}}
    @if ($showEndShiftModal && $activeShift)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden transform transition-all">
                <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                            🔒
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
                    <button wire:click="closeEndShiftModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
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
                            <span class="text-orange-600 dark:text-orange-400">Rp {{ number_format($activeShift->expected_cash, 0, ',', '.') }}</span>
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
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-bold text-slate-400">Rp</span>
                            <input type="text" wire:model.live="formattedActualCash" placeholder="0" autofocus
                                class="w-full pl-10 pr-4 py-2.5 text-lg font-extrabold border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-rose-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                        </div>
                    </div>

                    {{-- Status Selisih Real-time Preview --}}
                    <div class="p-3.5 rounded-xl border {{ $shiftDifference == 0 ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300' : ($shiftDifference < 0 ? 'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300' : 'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300') }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider">Hasil Selisih Kas:</span>
                            <span class="text-sm font-extrabold">
                                @if ($shiftDifference == 0)
                                    ✅ Sesuai / Pas (Rp 0)
                                @elseif ($shiftDifference < 0)
                                    ⚠️ Uang Kurang -Rp {{ number_format(abs($shiftDifference), 0, ',', '.') }}
                                @else
                                    💡 Uang Lebih +Rp {{ number_format($shiftDifference, 0, ',', '.') }}
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Catatan Penutupan Shift --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">
                            Catatan Shift (Opsional):
                        </label>
                        <textarea wire:model="shiftNotes" rows="2" placeholder="Contoh: Titipan uang receh aman, selisih pas..."
                            class="w-full px-3 py-2 text-xs border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white"></textarea>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
                    <button type="button" wire:click="closeEndShiftModal" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold hover:bg-slate-300 dark:hover:bg-slate-600 transition">
                        Batal
                    </button>
                    <button type="button" wire:click="endShift" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                        🔒 Tutup Shift & Cetak Rekap
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
    });

    function confirmResetCart() {
        Swal.fire({
            title: 'Kosongkan Keranjang Cafe?',
            text: "Semua daftar pesanan akan dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
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