<div class="min-h-screen bg-[#f8faf9] flex flex-col justify-between pb-28 lg:pb-12 text-slate-800">
    
    {{-- ========================================================================= --}}
    {{-- 1. FORE COFFEE SIGNATURE HEADER (SPACIOUS & LUXURIOUS)                    --}}
    {{-- ========================================================================= --}}
    <header class="bg-[#0e382c] text-white pt-10 sm:pt-6 pb-5 sm:pb-6 rounded-b-[2rem] sticky top-0 z-30 shadow-lg shadow-[#0e382c]/15 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            
            {{-- Top Row: Brand Info & Table Selector --}}
            <div class="flex items-center justify-between gap-3 pt-1">
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 mb-1">
                        @if(!$isStoreOpen)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-500/30 text-rose-200 border border-rose-400/40 uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400 mr-1.5"></span>
                                Kafe Tutup
                            </span>
                        @elseif(!$isOnlineOrderActive)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-500/30 text-amber-200 border border-amber-400/40 uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-1.5 animate-pulse"></span>
                                Pesanan Dijeda • Toko Sibuk
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                                Cafe Buka
                            </span>
                        @endif
                        @if(!empty($customerName))
                            <span class="text-[11px] text-emerald-100/90 truncate hidden sm:inline">• Hai, <strong>{{ $customerName }}</strong></span>
                        @endif
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black font-heading tracking-tight truncate leading-tight text-white">
                        {{ $setting->shop_name ?? 'POS Cafe' }}
                    </h1>
                </div>

                {{-- Riwayat Pesanan Saya Button (Top Right) --}}
                @php
                    $activeOrdersCount = $this->activeOrders->count();
                    $activeOrder = $this->activeOrders->first();
                @endphp
                <div class="shrink-0">
                    <button type="button" 
                            wire:click="openHistoryModal"
                            class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 active:scale-95 transition border border-white/15 text-left flex items-center gap-2 shadow-sm relative cursor-pointer">
                        <div class="w-7 h-7 rounded-xl bg-emerald-400/20 flex items-center justify-center text-emerald-300 text-xs shrink-0">
                            <i class="fas fa-clock-rotate-left"></i>
                        </div>
                        <div class="text-left leading-tight hidden xs:block">
                            <span class="text-[9px] text-emerald-200/80 block font-bold uppercase tracking-wider">Pesanan</span>
                            <span class="text-xs font-black text-white flex items-center gap-1 mt-0.5">
                                Riwayat
                                @if($activeOrdersCount > 0)
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse ml-0.5"></span>
                                @endif
                            </span>
                        </div>
                        @if($activeOrdersCount > 0)
                            <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-emerald-500 text-white font-black text-[10px] rounded-full flex items-center justify-center border-2 border-[#0e382c] shadow-sm animate-pulse">
                                {{ $activeOrdersCount }}
                            </span>
                        @endif
                    </button>
                </div>
            </div>

            {{-- Search Bar with Comfortable Spacing --}}
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-emerald-200/70">
                    <i class="fas fa-search text-xs sm:text-sm"></i>
                </div>
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Cari kopi, pastry, minuman segar favoritmu..." 
                       class="w-full pl-10 sm:pl-11 pr-10 py-3 rounded-2xl bg-[#09271f] border border-emerald-900/90 text-white placeholder-emerald-200/50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-[#061e18] shadow-inner transition">
                @if(!empty($search))
                    <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-emerald-300 hover:text-white">
                        <i class="fas fa-times-circle text-xs sm:text-sm"></i>
                    </button>
                @endif
            </div>

            {{-- Customer Name Strip (Responsive Bar) --}}
            <div class="flex items-center justify-between pt-2 pb-0.5 text-xs text-emerald-200/90 border-t border-white/10">
                <div class="truncate flex items-center gap-1 text-[11px] sm:text-xs">
                    <span class="text-emerald-300/80">Pemesan:</span>
                    <strong class="text-white font-bold truncate max-w-[170px] sm:max-w-xs">{{ $customerName ?: 'Belum diisi' }}</strong>
                </div>
                <button type="button" 
                        wire:click="openIdentityModal" 
                        class="px-2.5 py-1 rounded-lg sm:rounded-xl bg-white/10 hover:bg-white/20 active:scale-95 text-emerald-200 hover:text-white font-bold text-[11px] shrink-0 border border-white/10 transition cursor-pointer flex items-center gap-1">
                    <i class="fas fa-pen-to-square text-[10px]"></i>
                    <span>{{ empty($customerName) ? 'Isi Data' : 'Ubah' }}</span>
                </button>
            </div>

        </div>
    </header>

    {{-- FLOATING ACTIVE ORDER BANNER (CLEAN & INFORMATIVE) --}}
    @if($activeOrder)
        <div class="max-w-7xl mx-auto w-full px-3.5 sm:px-6 lg:px-8 mt-3">
            <div class="bg-gradient-to-r from-emerald-800 via-[#0e382c] to-[#09271f] border border-emerald-500/40 rounded-2xl p-3 sm:p-3.5 text-white flex items-center justify-between gap-3 shadow-md">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center text-sm shrink-0">
                        @if($activeOrder->status === 'ready')
                            <i class="fas fa-bell text-amber-300"></i>
                        @else
                            <i class="fas fa-spinner fa-spin text-emerald-400"></i>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-xs sm:text-sm font-black text-white truncate leading-tight">
                            @if($activeOrder->status === 'ready')
                                Pesanan Siap Diambil!
                            @else
                                Pesanan Sedang Disiapkan
                            @endif
                        </h4>
                        <div class="text-[11px] text-emerald-200/90 mt-0.5 truncate font-medium">
                            <span>{{ $activeOrder->order_type === 'dine_in' ? 'Meja ' . ($activeOrder->table_number ?? '-') : 'Takeaway' }}</span>
                            <span>•</span>
                            <span>{{ $activeOrder->details->sum('quantity') }} Menu</span>
                            <span>•</span>
                            <span class="font-bold text-white">Rp {{ number_format($activeOrder->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('customer.status', $activeOrder->order_token) }}" 
                   class="px-3.5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-black text-xs shrink-0 shadow-2xs flex items-center gap-1.5">
                    <span>Cek Status</span>
                  
                </a>
            </div>
        </div>
    @endif

    {{-- Closed Store / Rush Mode Pause Notice Banner --}}
    @if(!$isStoreOpen)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-3">
            <div class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-3.5 flex items-center gap-3 text-amber-900 text-xs">
                <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-700 flex items-center justify-center text-sm shrink-0">
                    <i class="fas fa-moon"></i>
                </div>
                <div>
                    <strong class="block font-black text-amber-950">Pemesanan Belum Dibuka</strong>
                    <span class="text-amber-800 text-[11px]">Mohon maaf, saat ini kafe belum menerima pesanan. Anda tetap dapat melihat-lihat daftar menu kami.</span>
                </div>
            </div>
        </div>
    @elseif(!$isOnlineOrderActive)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-3">
            <div class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-3.5 flex items-center gap-3 text-amber-900 text-xs">
                <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-700 flex items-center justify-center text-sm shrink-0">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <strong class="block font-black text-amber-950">Pemesanan Sedang Dijeda Sementara (Dapur Padat)</strong>
                    <span class="text-amber-800 text-[11px]">Mohon maaf, antrean dapur saat ini sedang sangat padat sehingga kami menunda pesanan baru untuk sementara. Anda tetap dapat melihat daftar menu.</span>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 2. MAIN BODY (GRID FOR MOBILE & TABLET)                                   --}}
    {{-- ========================================================================= --}}
    <main class="max-w-7xl mx-auto w-full px-3.5 sm:px-6 lg:px-8 pt-4 sm:pt-6 flex-1">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            {{-- LEFT COLUMN: CATEGORIES & PRODUCT GRID --}}
            <div class="lg:col-span-8 space-y-4">
                
                {{-- Sticky Category Chips (Fore Clean Style) --}}
                <div class="sticky top-[180px] sm:top-[170px] z-20 bg-[#f8faf9]/95 backdrop-blur-md py-2 -mx-3.5 px-3.5 sm:mx-0 sm:px-0">
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5">
                        <button type="button" 
                                wire:click="clearCategory"
                                class="px-4 py-2 rounded-2xl text-xs font-black whitespace-nowrap flex items-center gap-1.5 {{ empty($selectedCategory) ? 'bg-[#0e382c] text-white shadow-xs' : 'bg-white border border-slate-200/90 text-slate-600 hover:bg-slate-50' }}">
                            <i class="fas fa-border-all text-[11px]"></i>
                            <span>Semua Menu</span>
                        </button>

                        @foreach($categories as $category)
                            <button type="button" 
                                    wire:click="selectCategory({{ $category->id }})"
                                    class="px-4 py-2 rounded-2xl text-xs font-black whitespace-nowrap flex items-center gap-1.5 {{ $selectedCategory == $category->id ? 'bg-[#0e382c] text-white shadow-xs' : 'bg-white border border-slate-200/90 text-slate-600 hover:bg-slate-50' }}">
                                <span>{{ $category->name }}</span>
                                <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold {{ $selectedCategory == $category->id ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $category->products->count() }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Product List Grid (ENTIRE CARD IS CLICKABLE - NO ANIMATION DELAY) --}}
                @if($products->isEmpty())
                    <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-xs my-6">
                        <div class="w-16 h-16 bg-emerald-50 text-[#0e382c] rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl">
                            <i class="fas fa-mug-hot"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800 font-heading">Menu Tidak Ditemukan</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                            @if(!empty($search))
                                Tidak ada hasil untuk kata kunci "{{ $search }}". Silakan coba kata kunci lain.
                            @else
                                Belum ada menu aktif di kategori ini.
                            @endif
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                        @foreach($products as $product)
                            @php
                                $inCartCount = 0;
                                foreach($cart as $cItem) {
                                    if($cItem['id'] == $product->id) {
                                        $inCartCount += $cItem['quantity'];
                                    }
                                }
                            @endphp
                            {{-- WHOLE CARD CLICKABLE (INSTANT RESPONSE, NO BORDER/HOVER ANIMATIONS) --}}
                            <div wire:click="openCustomizeModal({{ $product->id }})"
                                 class="bg-white rounded-3xl p-3 border border-slate-200/80 shadow-xs flex flex-col justify-between relative cursor-pointer select-none">
                                
                                {{-- Image Thumbnail Container --}}
                                <div class="w-full aspect-square rounded-2xl bg-slate-100 overflow-hidden relative mb-2.5">
                                    {{-- Best Seller / Top Order Badge --}}
                                    @if(in_array($product->id, $bestSellerIds ?? []))
                                        <div class="absolute top-2 left-2 z-10 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[9.5px] font-black px-2.5 py-1 rounded-xl shadow-sm shadow-orange-500/30 border border-white/30 flex items-center gap-1.5 leading-none">
                                            <i class="fas fa-fire text-[9.5px] text-amber-100"></i>
                                            <span>Best Seller</span>
                                        </div>
                                    @elseif(in_array($product->id, $topOrderIds ?? []))
                                        <div class="absolute top-2 left-2 z-10 bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-[9.5px] font-black px-2.5 py-1 rounded-xl shadow-sm shadow-emerald-600/30 border border-white/30 flex items-center gap-1.5 leading-none">
                                            <i class="fas fa-star text-[9px] text-emerald-100"></i>
                                            <span>Top Order</span>
                                        </div>
                                    @endif

                                    {{-- In-Cart Badge Indicator --}}
                                    @if($inCartCount > 0)
                                        <div class="absolute top-2 right-2 z-10 bg-[#0e382c] text-white text-[10.5px] font-black px-2 py-0.5 rounded-xl border border-emerald-400/40 shadow-sm flex items-center justify-center leading-none">
                                            {{ $inCartCount }}x
                                        </div>
                                    @endif

                                    @if(!empty($product->image))
                                        <img src="{{ asset('storage/' . $product->image) }}" 
                                             alt="{{ $product->name }}" 
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-300 bg-gradient-to-br from-slate-50 to-slate-100">
                                            <i class="fas fa-coffee text-3xl mb-1 text-slate-300"></i>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $product->category?->name ?? 'Menu' }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Details --}}
                                <div class="flex-1 flex flex-col justify-between">
                                    <div>
                                        <span class="text-[10px] uppercase font-black tracking-wider text-emerald-800 block">
                                            {{ $product->category?->name }}
                                        </span>
                                        <h3 class="font-extrabold text-slate-900 text-xs sm:text-sm line-clamp-1 font-heading leading-tight mt-0.5" title="{{ $product->name }}">
                                            {{ $product->name }}
                                        </h3>
                                        @if(!empty($product->description))
                                            <p class="text-[11px] text-slate-400 line-clamp-2 mt-1 leading-relaxed hidden xs:block">
                                                {{ $product->description }}
                                            </p>
                                        @endif
                                    </div>

                                    {{-- Price & Plus Icon --}}
                                    <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between gap-1">
                                        <div>
                                            <span class="text-xs sm:text-sm font-black text-slate-900 block font-heading">
                                                Rp {{ number_format($product->price, 0, ',', '.') }}
                                            </span>
                                        </div>

                                        {{-- Plus Icon --}}
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-emerald-50 text-[#0e382c] flex items-center justify-center shadow-2xs">
                                            <i class="fas fa-plus text-xs"></i>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endif

            </div>

            {{-- RIGHT COLUMN: TABLET & DESKTOP PERSISTENT CART (>= 1024px) --}}
            <div class="hidden lg:block lg:col-span-4 sticky top-[80px]">
                <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-md flex flex-col max-h-[calc(100vh-100px)]">
                    
                    {{-- Cart Header --}}
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-2xl bg-emerald-50 text-[#0e382c] flex items-center justify-center text-sm font-black">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm font-heading">Pesanan Anda</h3>
                                <span class="text-[11px] text-slate-400 font-medium">{{ $totalItemsInCart }} menu dipilih</span>
                            </div>
                        </div>

                        <button type="button" 
                                wire:click="$set('showIdentityModal', true)" 
                                class="text-[11px] font-black text-[#0e382c] hover:underline">
                            {{ $orderType === 'dine_in' ? (!empty($tableNumber) ? 'Meja ' . $tableNumber : 'Pilih Meja') : 'Takeaway' }}
                        </button>
                    </div>

                    {{-- Customer Identity Mini Bar --}}
                    <div class="my-3 p-3 rounded-2xl bg-emerald-50/60 border border-emerald-200/60 flex items-center justify-between text-xs">
                        <div class="truncate pr-2">
                            <span class="text-[10px] text-emerald-900/80 uppercase font-bold block">Pemesan</span>
                            <strong class="text-slate-900 font-black truncate block">{{ $customerName ?: 'Belum diisi' }}</strong>
                        </div>
                        <button type="button" wire:click="$set('showIdentityModal', true)" class="px-2.5 py-1 rounded-xl bg-white border border-emerald-300 text-emerald-900 text-[11px] font-extrabold shadow-2xs hover:bg-emerald-50 shrink-0">
                            Ubah
                        </button>
                    </div>

                    {{-- Cart Items Scrollable List --}}
                    @if(empty($cart))
                        <div class="py-12 text-center text-slate-400">
                            <i class="fas fa-shopping-basket text-3xl mb-2 text-slate-300"></i>
                            <p class="text-xs font-bold">Keranjang masih kosong</p>
                            <span class="text-[11px] text-slate-400 block mt-0.5">Pilih menu favoritmu di samping</span>
                        </div>
                    @else
                        <div class="flex-1 overflow-y-auto space-y-2.5 pr-1 my-2 max-h-[320px]">
                            @foreach($cart as $key => $item)
                                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/70 flex items-start justify-between gap-2.5">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-black text-slate-900 text-xs font-heading truncate">{{ $item['name'] }}</h4>
                                        <span class="text-xs font-black text-emerald-800 block mt-0.5">
                                            Rp {{ number_format($item['price'], 0, ',', '.') }}
                                        </span>
                                        @if(!empty($item['notes']))
                                            <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                                {{ $item['notes'] }}
                                            </div>
                                        @endif
                                        <button type="button" 
                                                wire:click="editCartItem('{{ $key }}')"
                                                class="text-[11px] font-bold text-[#0e382c] hover:underline inline-flex items-center gap-1 mt-1 cursor-pointer">
                                            <i class="fas fa-pencil-alt text-[9px]"></i>
                                            <span>Ubah</span>
                                        </button>
                                    </div>

                                    {{-- Quantity Controls --}}
                                    <div class="flex items-center space-x-1.5 bg-white px-2 py-1 rounded-xl border border-slate-200 shrink-0">
                                        <button type="button" 
                                                wire:click="updateQuantity('{{ $key }}', 'decrease')"
                                                class="w-5 h-5 rounded-lg text-slate-600 hover:bg-slate-100 flex items-center justify-center font-bold text-xs">
                                            <i class="fas fa-minus text-[9px]"></i>
                                        </button>
                                        <span class="font-black text-xs text-slate-900 w-4 text-center">{{ $item['quantity'] }}</span>
                                        <button type="button" 
                                                wire:click="updateQuantity('{{ $key }}', 'increase')"
                                                class="w-5 h-5 rounded-lg text-emerald-700 hover:bg-emerald-50 flex items-center justify-center font-bold text-xs">
                                            <i class="fas fa-plus text-[9px]"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Calculation Summary --}}
                        <div class="border-t border-slate-100 pt-3 space-y-1.5 text-xs text-slate-600">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span class="font-bold text-slate-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            @if($taxRate > 0)
                                <div class="flex justify-between">
                                    <span>Pajak ({{ $taxRate }}%)</span>
                                    <span class="font-bold text-slate-900">Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-sm font-black text-slate-900 pt-2 border-t border-slate-200">
                                <span>Total Pembayaran</span>
                                <span class="text-[#0e382c] font-heading">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Checkout Action --}}
                        <div class="mt-4 pt-1">
                            @if(!$isStoreOpen)
                                <button type="button" 
                                        disabled
                                        class="w-full py-3.5 rounded-2xl bg-slate-300 text-slate-500 font-black text-xs shadow-none cursor-not-allowed flex items-center justify-center gap-2">
                                    <i class="fas fa-lock text-xs"></i>
                                    <span>Kafe Sedang Tutup</span>
                                </button>
                            @elseif(!$isOnlineOrderActive)
                                <button type="button" 
                                        disabled
                                        class="w-full py-3.5 rounded-2xl bg-amber-100 text-amber-800 border border-amber-300 font-black text-xs shadow-none cursor-not-allowed flex items-center justify-center gap-2">
                                    <i class="fas fa-pause-circle text-xs"></i>
                                    <span>Dapur Padat • Pesanan Dijeda</span>
                                </button>
                            @elseif(empty($customerName))
                                <button type="button" 
                                        wire:click="openIdentityModal"
                                        class="w-full py-3.5 rounded-2xl bg-[#0e382c] hover:bg-[#134e3f] text-white font-extrabold text-xs shadow-xs flex items-center justify-center gap-2">
                                    <i class="fas fa-user-edit"></i>
                                    <span>Isi Nama Pemesan Dulu</span>
                                </button>
                            @else
                                <button type="button" 
                                        wire:click="proceedToCheckout"
                                        wire:loading.attr="disabled"
                                        class="w-full py-3.5 rounded-2xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-95 text-white font-black text-xs shadow-lg shadow-[#0e382c]/25 transition flex items-center justify-center gap-2">
                                    <span wire:loading.remove>Lanjut ke Pembayaran QRIS</span>
                                    <span wire:loading><i class="fas fa-spinner fa-spin mr-1"></i> Memproses...</span>
                                    <i wire:loading.remove class="fas fa-arrow-right text-[10px]"></i>
                                </button>
                            @endif
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </main>

    {{-- ========================================================================= --}}
    {{-- 3. MOBILE FLOATING STICKY CART BAR (Fore Forest Green)                    --}}
    {{-- ========================================================================= --}}
    @if(!empty($cart))
        <div class="lg:hidden fixed bottom-4 left-0 right-0 z-40 px-4 max-w-md mx-auto">
            <div class="bg-[#0e382c] text-white p-2.5 pl-3.5 rounded-2xl shadow-2xl flex items-center justify-between border border-emerald-900/60">
                
                <div class="flex items-center gap-3 cursor-pointer" wire:click="openCartDrawer">
                    <div class="relative w-10 h-10 rounded-xl bg-white/15 text-white flex items-center justify-center font-bold text-sm shadow-inner">
                        <i class="fas fa-shopping-bag"></i>
                        <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-rose-500 text-white text-[10px] rounded-full flex items-center justify-center font-black border-2 border-[#0e382c]">
                            {{ $totalItemsInCart }}
                        </span>
                    </div>
                    <div>
                        <span class="text-[10px] text-emerald-200/80 block font-medium">Total Pesanan</span>
                        <span class="text-sm font-black text-white font-heading">
                            Rp {{ number_format($total, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <button type="button" 
                        wire:click="openCartDrawer"
                        class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 active:scale-95 text-[#0e382c] text-xs font-black shadow-md flex items-center gap-1.5 transition">
                    <span>Lihat Pesanan</span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </button>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 1: CUSTOMER IDENTITY & TABLE SELECTOR                              --}}
    {{-- ========================================================================= --}}
    @if($showIdentityModal)
        <div class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-950/70 backdrop-blur-xs">
            <div class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto">
                <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-4 sm:hidden"></div>

                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="font-extrabold text-slate-900 text-base font-heading">Informasi Pemesan</h3>
                    <button type="button" wire:click="closeIdentityModal" class="text-slate-400 hover:text-slate-600 p-1">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- Order Type Toggle --}}
                    <div>
                        <label class="block text-xs font-black text-slate-700 mb-2 uppercase tracking-wide">Pilihan Santap</label>
                        <div class="grid grid-cols-2 gap-2.5">
                            <button type="button" 
                                    wire:click="setOrderType('dine_in')"
                                    class="p-3.5 rounded-2xl border-2 text-center flex flex-col items-center justify-center gap-1.5 {{ $orderType === 'dine_in' ? 'border-[#0e382c] bg-emerald-50 text-[#0e382c] font-black shadow-xs' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                <i class="fas fa-chair text-lg {{ $orderType === 'dine_in' ? 'text-[#0e382c]' : 'text-slate-400' }}"></i>
                                <span class="text-xs">Makan di Tempat</span>
                            </button>

                            <button type="button" 
                                    wire:click="setOrderType('take_away')"
                                    class="p-3.5 rounded-2xl border-2 text-center flex flex-col items-center justify-center gap-1.5 {{ $orderType === 'take_away' ? 'border-[#0e382c] bg-emerald-50 text-[#0e382c] font-black shadow-xs' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                <i class="fas fa-bag-shopping text-lg {{ $orderType === 'take_away' ? 'text-[#0e382c]' : 'text-slate-400' }}"></i>
                                <span class="text-xs">Bawa Pulang (Takeaway)</span>
                            </button>
                        </div>
                    </div>

                    {{-- Customer Name --}}
                    <div>
                        <label class="block text-xs font-black text-slate-700 mb-1.5">Nama Pemesan <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" 
                                   wire:model="customerName" 
                                   placeholder="Nama Anda" 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600">
                        </div>
                        @error('customerName') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Customer Phone (Optional) --}}
                    <div>
                        <label class="block text-xs font-black text-slate-700 mb-1.5">No. WhatsApp <span class="text-slate-400 font-normal text-[11px]">(Opsional)</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                                <i class="fab fa-whatsapp"></i>
                            </span>
                            <input type="tel" 
                                   wire:model="customerPhone" 
                                   placeholder="081234567890" 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600">
                        </div>
                    </div>

                    {{-- Info Banner Pick-Up Counter --}}
                    <div class="p-3 bg-emerald-50/80 border border-emerald-200/70 rounded-2xl flex items-start gap-2.5 text-xs text-[#0e382c]">
                        <i class="fas fa-bullhorn text-emerald-600 mt-0.5 text-[11px]"></i>
                        <span class="leading-snug">Pesanan yang sudah siap dapat diambil di <strong>Pick-up Counter</strong> saat nomor pesananmu dipanggil.</span>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="button" 
                            wire:click="closeIdentityModal" 
                            class="w-full py-3.5 rounded-2xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-95 text-white font-extrabold text-xs shadow-md transition">
                        Simpan Informasi
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 2: PRODUCT CUSTOMIZATION (Notes, Sugar, Ice Level)                  --}}
    {{-- ========================================================================= --}}
    @if($showCustomizeModal && $selectedProduct)
        <div class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-950/70 backdrop-blur-xs">
            <div class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto">
                <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-4 sm:hidden"></div>

                <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-1.5 mb-1">
                            <span class="text-[10px] uppercase font-black text-emerald-800">
                                {{ $editingCartKey ? 'Ubah Pesanan • ' : '' }}{{ $selectedProduct->category?->name }}
                            </span>
                            @if(in_array($selectedProduct->id, $bestSellerIds ?? []))
                                <span class="bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[9px] font-black px-2 py-0.2 rounded-full shadow-2xs flex items-center gap-0.5">
                                    <i class="fas fa-fire text-[8px]"></i> Best Seller
                                </span>
                            @elseif(in_array($selectedProduct->id, $topOrderIds ?? []))
                                <span class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-[9px] font-black px-2 py-0.2 rounded-full shadow-2xs flex items-center gap-0.5">
                                    <i class="fas fa-star text-[8px]"></i> Top Order
                                </span>
                            @endif
                        </div>
                        <h3 class="font-black text-slate-900 text-base font-heading">{{ $selectedProduct->name }}</h3>
                        <span class="text-sm font-black text-slate-900 block mt-0.5 font-heading">
                            Rp {{ number_format($selectedProduct->price, 0, ',', '.') }}
                        </span>
                    </div>
                    <button type="button" wire:click="closeCustomizeModal" class="text-slate-400 hover:text-slate-600 p-1">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                @php
                    $catName = strtolower($selectedProduct->category?->name ?? '');
                    $isBeverage = str_contains($catName, 'coffee') || str_contains($catName, 'tea') || str_contains($catName, 'minuman') || str_contains($catName, 'drink');
                @endphp

                <div class="space-y-4 my-4">
                    @if($isBeverage)
                        {{-- Drink Serving Type: Ice vs Hot --}}
                        <div>
                            <label class="block text-xs font-black text-slate-700 mb-2">Varian Sajian</label>
                            <div class="grid grid-cols-2 gap-2.5">
                                <button type="button" 
                                        wire:click="$set('drinkType', 'Ice')"
                                        class="py-2.5 px-3 rounded-2xl text-xs font-black text-center border-2 flex items-center justify-center gap-2 {{ $drinkType === 'Ice' ? 'border-[#0e382c] bg-emerald-50 text-[#0e382c] shadow-xs' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                    <i class="fas fa-snowflake text-xs {{ $drinkType === 'Ice' ? 'text-emerald-700' : 'text-slate-400' }}"></i>
                                    <span>Ice (Dingin)</span>
                                </button>

                                <button type="button" 
                                        wire:click="$set('drinkType', 'Hot')"
                                        class="py-2.5 px-3 rounded-2xl text-xs font-black text-center border-2 flex items-center justify-center gap-2 {{ $drinkType === 'Hot' ? 'border-[#0e382c] bg-emerald-50 text-[#0e382c] shadow-xs' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                    <i class="fas fa-mug-hot text-xs {{ $drinkType === 'Hot' ? 'text-emerald-700' : 'text-slate-400' }}"></i>
                                    <span>Hot (Panas)</span>
                                </button>
                            </div>
                        </div>

                        {{-- Sugar Level (Tingkat Kemanisan) --}}
                        <div>
                            <label class="block text-xs font-black text-slate-700 mb-2">Tingkat Kemanisan (Sugar)</label>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach(['Normal', 'Less Sugar', 'No Sugar'] as $sugar)
                                    <button type="button" 
                                            wire:click="$set('sugarLevel', '{{ $sugar }}')"
                                            class="py-2.5 px-2 rounded-2xl text-xs font-black text-center border-2 {{ $sugarLevel === $sugar ? 'border-[#0e382c] bg-emerald-50 text-[#0e382c] shadow-xs' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                        {{ $sugar }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Ice Level (Tingkat Es - HANYA MUNCUL JIKA PILIH ICE) --}}
                        @if($drinkType === 'Ice')
                            <div>
                                <label class="block text-xs font-black text-slate-700 mb-2">Tingkat Es (Ice)</label>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach(['Normal', 'Less Ice', 'No Ice'] as $ice)
                                        <button type="button" 
                                                wire:click="$set('iceLevel', '{{ $ice }}')"
                                                class="py-2.5 px-2 rounded-2xl text-xs font-black text-center border-2 {{ $iceLevel === $ice ? 'border-[#0e382c] bg-emerald-50 text-[#0e382c] shadow-xs' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                                            {{ $ice }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- Special Instructions / Notes --}}
                    <div>
                        <label class="block text-xs font-black text-slate-700 mb-1.5">Catatan Khusus (Opsional)</label>
                        <textarea wire:model="itemNotes" 
                                  rows="2" 
                                  placeholder="Contoh: Pisahkan gula, jangan terlalu manis, dll..."
                                  class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600"></textarea>
                    </div>

                    {{-- Quantity Selector --}}
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <span class="text-xs font-black text-slate-700">Jumlah Pesanan:</span>
                        <div class="flex items-center space-x-3 bg-slate-100 p-1 rounded-xl border border-slate-200">
                            <button type="button" 
                                    wire:click="decrementModalQty"
                                    class="w-8 h-8 rounded-lg bg-white shadow-xs text-slate-700 hover:bg-slate-200 flex items-center justify-center font-bold text-sm">
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <span class="font-extrabold text-sm text-slate-900 w-6 text-center">{{ $modalQty }}</span>
                            <button type="button" 
                                    wire:click="incrementModalQty"
                                    class="w-8 h-8 rounded-lg bg-[#0e382c] shadow-xs text-white hover:bg-[#134e3f] flex items-center justify-center font-bold text-sm">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Action Button --}}
                <div class="mt-5">
                    <button type="button" 
                            wire:click="addConfiguredToCart"
                            class="w-full py-3.5 rounded-2xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-95 text-white font-black text-xs shadow-md shadow-[#0e382c]/20 flex items-center justify-between px-5 transition">
                        <span>{{ $editingCartKey ? 'Simpan Perubahan' : 'Tambahkan ke Pesanan' }}</span>
                        <span>Rp {{ number_format($selectedProduct->price * $modalQty, 0, ',', '.') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 3: MOBILE CART DRAWER (Review & Checkout on Mobile)                 --}}
    {{-- ========================================================================= --}}
    @if($showCartDrawer)
        <div class="lg:hidden fixed inset-0 z-50 flex items-end justify-center bg-slate-950/70 backdrop-blur-xs">
            <div class="bg-white w-full max-w-md rounded-t-3xl p-5 shadow-2xl relative max-h-[92vh] flex flex-col">
                <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-3"></div>

                {{-- Header Drawer --}}
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-shopping-bag text-[#0e382c] text-base"></i>
                        <h3 class="font-extrabold text-slate-900 text-base font-heading">Ringkasan Pesanan</h3>
                    </div>
                    <button type="button" wire:click="closeCartDrawer" class="text-slate-400 hover:text-slate-600 p-1">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                {{-- Identity Summary in Drawer --}}
                <div class="my-3 p-3 rounded-2xl bg-emerald-50 border border-emerald-200/80 flex items-center justify-between text-xs">
                    <div>
                        <div class="font-extrabold text-emerald-950">
                            {{ $orderType === 'dine_in' ? 'Makan di Tempat' : 'Bawa Pulang' }}
                            @if($orderType === 'dine_in' && !empty($tableNumber))
                                <span class="text-emerald-800 font-black">• Meja {{ $tableNumber }}</span>
                            @endif
                        </div>
                        <div class="text-[11px] text-emerald-800/90">
                            Pemesan: <strong class="text-slate-900 font-bold">{{ $customerName ?: 'Belum diisi' }}</strong>
                        </div>
                    </div>
                    <button type="button" wire:click="openIdentityModal" class="px-2.5 py-1 rounded-xl bg-white border border-emerald-300 text-emerald-900 text-[11px] font-black shadow-2xs">
                        Ubah
                    </button>
                </div>

                {{-- Cart Items List --}}
                <div class="flex-1 overflow-y-auto space-y-2.5 pr-1 my-1">
                    @foreach($cart as $key => $item)
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/70 flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-black text-slate-900 text-xs font-heading truncate">{{ $item['name'] }}</h4>
                                <span class="text-xs font-black text-emerald-800 block mt-0.5">
                                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                                </span>
                                @if(!empty($item['notes']))
                                    <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                        {{ $item['notes'] }}
                                    </div>
                                @endif
                                <button type="button" 
                                        wire:click="editCartItem('{{ $key }}')"
                                        class="text-[11px] font-bold text-[#0e382c] hover:underline inline-flex items-center gap-1 mt-1 cursor-pointer">
                                    <i class="fas fa-pencil-alt text-[9px]"></i>
                                    <span>Ubah</span>
                                </button>
                            </div>

                            {{-- Quantity Controller --}}
                            <div class="flex items-center space-x-1.5 bg-white px-2 py-1 rounded-xl border border-slate-200 shadow-2xs shrink-0">
                                <button type="button" 
                                        wire:click="updateQuantity('{{ $key }}', 'decrease')"
                                        class="w-6 h-6 rounded-lg text-slate-600 hover:bg-slate-100 flex items-center justify-center font-bold text-xs">
                                    <i class="fas fa-minus text-[10px]"></i>
                                </button>
                                <span class="font-black text-xs text-slate-900 w-4 text-center">{{ $item['quantity'] }}</span>
                                <button type="button" 
                                        wire:click="updateQuantity('{{ $key }}', 'increase')"
                                        class="w-6 h-6 rounded-lg text-emerald-700 hover:bg-emerald-50 flex items-center justify-center font-bold text-xs">
                                    <i class="fas fa-plus text-[10px]"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Bill Calculation --}}
                <div class="border-t border-slate-100 pt-3 space-y-1.5 text-xs text-slate-600">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span class="font-bold text-slate-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($taxRate > 0)
                        <div class="flex justify-between">
                            <span>Pajak ({{ $taxRate }}%)</span>
                            <span class="font-bold text-slate-900">Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm font-black text-slate-900 pt-2 border-t border-slate-200">
                        <span>Total Pembayaran</span>
                        <span class="text-[#0e382c] font-heading">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Action Button --}}
                <div class="mt-4">
                    @if(!$isStoreOpen)
                        <button type="button" 
                                disabled
                                class="w-full py-3.5 rounded-2xl bg-slate-300 text-slate-500 font-black text-xs shadow-none cursor-not-allowed flex items-center justify-center gap-2">
                            <i class="fas fa-lock text-xs"></i>
                            <span>Kafe Sedang Tutup</span>
                        </button>
                    @elseif(!$isOnlineOrderActive)
                        <button type="button" 
                                disabled
                                class="w-full py-3.5 rounded-2xl bg-amber-100 text-amber-800 border border-amber-300 font-black text-xs shadow-none cursor-not-allowed flex items-center justify-center gap-2">
                            <i class="fas fa-pause-circle text-xs"></i>
                            <span>Dapur Padat • Pesanan Dijeda</span>
                        </button>
                    @elseif(empty($customerName))
                        <button type="button" 
                                wire:click="openIdentityModal"
                                class="w-full py-3.5 rounded-2xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-95 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center gap-2">
                            <i class="fas fa-user-edit"></i>
                            <span>Isi Nama Pemesan Dulu</span>
                        </button>
                    @else
                        <button type="button" 
                                wire:click="proceedToCheckout"
                                wire:loading.attr="disabled"
                                class="w-full py-3.5 rounded-2xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-95 text-white font-black text-xs shadow-lg shadow-[#0e382c]/20 transition flex items-center justify-center gap-2">
                            <span wire:loading.remove>Lanjut ke Pembayaran QRIS</span>
                            <span wire:loading><i class="fas fa-spinner fa-spin mr-1"></i> Memproses Pesanan...</span>
                            <i wire:loading.remove class="fas fa-arrow-right text-[11px]"></i>
                        </button>
                    @endif
                </div>

            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 4: RIWAYAT PESANAN SAYA (History & Active Orders Drawer)             --}}
    {{-- ========================================================================= --}}
    @if($showHistoryModal)
        @php
            $historyList = $this->historyOrders;
        @endphp
        <div class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-950/70 backdrop-blur-xs">
            <div class="bg-white w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 shadow-2xl relative max-h-[90vh] flex flex-col">
                <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-3 sm:hidden"></div>

                {{-- Header Modal --}}
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-[#0e382c] flex items-center justify-center text-sm font-bold shadow-2xs">
                            <i class="fas fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-base font-heading leading-tight">Riwayat Pesanan Saya</h3>
                            <span class="text-[11px] text-slate-400">Daftar pesanan aktif & riwayat</span>
                        </div>
                    </div>
                    <button type="button" wire:click="closeHistoryModal" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                {{-- Orders List --}}
                <div class="flex-1 overflow-y-auto space-y-3 pr-1 my-3">
                    @if($historyList->isEmpty())
                        <div class="p-8 text-center bg-slate-50 rounded-2xl border border-slate-200/80 my-4">
                            <div class="w-14 h-14 rounded-2xl bg-white text-slate-300 flex items-center justify-center mx-auto mb-3 text-2xl shadow-2xs">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <h4 class="text-sm font-black text-slate-800 font-heading">Belum Ada Riwayat Pesanan</h4>
                            <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">
                                Pesanan yang kamu buat di kafe ini akan otomatis tersimpan di sini untuk dipantau statusnya.
                            </p>
                        </div>
                    @else
                        @foreach($historyList as $hist)
                            @php
                                $status = $hist->status;
                                $isPaid = $hist->payment_status === 'paid';
                            @endphp
                            <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition-all space-y-2.5">
                                
                                {{-- Top Status Row --}}
                                <div class="flex items-center justify-between gap-2 pb-2 border-b border-slate-100 text-xs">
                                    <div class="flex items-center gap-1.5">
                                        @if($hist->order_type === 'dine_in')
                                            <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 font-black text-[10px] border border-amber-200">
                                                Meja {{ $hist->table_number ?? '-' }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-800 font-black text-[10px] border border-blue-200">
                                                Takeaway
                                            </span>
                                        @endif
                                        <span class="font-bold text-slate-800 text-xs">Pesanan {{ $hist->short_order_number }}</span>
                                    </div>

                                    <div>
                                        @if($status === 'cancelled')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-50 text-rose-700 border border-rose-200">
                                                <i class="fas fa-times-circle mr-1 text-[9px]"></i> Dibatalkan
                                            </span>
                                        @elseif(!$isPaid)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200">
                                                Belum Dibayar
                                            </span>
                                        @elseif($status === 'processing' || $status === 'pending')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-50 text-amber-800 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Disiapkan
                                            </span>
                                        @elseif($status === 'ready')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-800 border border-emerald-200 animate-pulse">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Siap Diambil
                                            </span>
                                        @elseif($status === 'completed')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                                <i class="fas fa-check text-[9px] text-emerald-600"></i> Selesai
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Details --}}
                                <div class="text-xs space-y-1">
                                    @foreach($hist->details as $d)
                                        <div class="flex items-center justify-between text-slate-700">
                                            <span class="truncate">
                                                <strong>{{ $d->quantity }}x</strong> {{ $d->product?->name ?? 'Menu' }}
                                                @if(!empty($d->notes))
                                                    <span class="text-slate-400 text-[11px]">({{ $d->notes }})</span>
                                                @endif
                                            </span>
                                            <span class="font-mono text-slate-600 shrink-0 ml-2">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Total & Action Links --}}
                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                                    <div>
                                        <span class="text-[10px] text-slate-400 block leading-none">{{ $hist->created_at->diffForHumans() }}</span>
                                        <span class="font-black text-sm text-slate-900 font-heading">
                                            Rp {{ number_format($hist->total, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-1.5">
                                        @if($status === 'cancelled')
                                            <a href="{{ route('customer.status', $hist->order_token) }}" 
                                               class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs shadow-2xs">
                                                Detail
                                            </a>
                                        @elseif(!$isPaid)
                                            <a href="{{ route('customer.payment', $hist->order_token) }}" 
                                               class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-black text-xs shadow-2xs">
                                                Bayar QRIS
                                            </a>
                                        @else
                                            <a href="{{ route('customer.status', $hist->order_token) }}" 
                                               class="px-3 py-1.5 rounded-xl bg-[#0e382c] hover:bg-[#134e3f] text-white font-bold text-xs shadow-2xs flex items-center gap-1">
                                                <span>Cek Status</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Footer Button --}}
                <div class="pt-2 border-t border-slate-100 shrink-0">
                    <button type="button" 
                            wire:click="closeHistoryModal"
                            class="w-full py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition cursor-pointer">
                        Tutup Riwayat
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- Browser Token Sync Script --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            try {
                let tokens = JSON.parse(localStorage.getItem('self_order_tokens') || '[]');
                if (Array.isArray(tokens) && tokens.length > 0) {
                    @this.syncTokens(tokens);
                }
            } catch(e) {}
        });

        window.addEventListener('store-order-token', (event) => {
            try {
                let token = event.detail?.token;
                if (token) {
                    let tokens = JSON.parse(localStorage.getItem('self_order_tokens') || '[]');
                    if (!tokens.includes(token)) {
                        tokens.unshift(token);
                        localStorage.setItem('self_order_tokens', JSON.stringify(tokens.slice(0, 25)));
                    }
                }
            } catch(e) {}
        });
    </script>

</div>
