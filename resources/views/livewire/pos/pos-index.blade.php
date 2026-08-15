<div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-24 lg:pb-0 transition-colors duration-300">
    @include('livewire.includes.sidebar')

    <div class="lg:pl-64">
        @include('livewire.includes.header', ['title' => 'POS Kasir Cafe Noli', 'subtitle' => 'Noli Coffee & Eatery - Sistem Kasir'])

        <main class="p-4 lg:p-6 space-y-4">
            
            {{-- TOP BAR: ORDER TYPE & TABLE NUMBER SELECTION --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 card-shadow transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
                {{-- Order Type Switcher --}}
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        Tipe Pesanan:
                    </span>
                    <div class="inline-flex rounded-lg p-1 bg-slate-100 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600">
                        <button type="button" wire:click="setOrderType('dine_in')"
                            class="px-4 py-1.5 rounded-md text-xs font-semibold transition-all flex items-center gap-1.5 {{ $orderType === 'dine_in' ? 'bg-slate-900 text-white dark:bg-blue-600 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                            🍽️ Makan di Tempat (Meja)
                        </button>
                        <button type="button" wire:click="setOrderType('take_away')"
                            class="px-4 py-1.5 rounded-md text-xs font-semibold transition-all flex items-center gap-1.5 {{ $orderType === 'take_away' ? 'bg-slate-900 text-white dark:bg-blue-600 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                            🛍️ Bawa Pulang (Take Away)
                        </button>
                        <button type="button" wire:click="setOrderType('delivery')"
                            class="px-4 py-1.5 rounded-md text-xs font-semibold transition-all flex items-center gap-1.5 {{ $orderType === 'delivery' ? 'bg-slate-900 text-white dark:bg-blue-600 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                            🚚 Pesan Antar (Delivery)
                        </button>
                    </div>
                </div>

                {{-- Dynamic Inputs based on Order Type --}}
                <div class="flex items-center gap-3">
                    @if($orderType === 'dine_in')
                        <div class="flex items-center gap-2" wire:key="input-container-table">
                            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Pilih Meja:</label>
                            <select wire:model.live="selectedTable" class="px-3 py-1.5 text-xs font-semibold border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer w-40">
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
                                class="px-3 py-1.5 text-xs font-semibold border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white w-48">
                        </div>
                    @endif
                </div>
            </div>

            {{-- MAIN LAYOUT --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- LEFT COLUMN: SEARCH & PRODUCTS (col-span-2) --}}
                <div class="lg:col-span-2 space-y-4">
                    
                    {{-- SEARCH & CATEGORY SELECTOR (CLEAN 1-ROW) --}}
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="flex-1 relative">
                                <input type="text" wire:model.live="search"
                                    id="searchInput"
                                    placeholder="Cari menu / scan barcode..."
                                    class="w-full pl-10 pr-10 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs"
                                    autocomplete="off">
                                <svg class="w-5 h-5 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                @if(!empty($search))
                                    <button type="button" wire:click="clearSearch"
                                        class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-md transition-colors"
                                        title="Hapus pencarian">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                @endif
                            </div>
                            <select wire:model.live="selectedCategory" class="px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer text-xs font-medium w-full sm:w-56">
                                <option value="">Semua Menu Cafe ({{ $categories->sum('products_count') }})</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->products_count }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Products Grid --}}
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 lg:gap-4">
                        @forelse($products as $product)
                            @php
                                $inCartQty = $this->getCartQuantity($product->id);
                            @endphp
                            <div wire:click="addToCart({{ $product->id }})"
                                class="bg-white dark:bg-slate-800 rounded-xl p-3 lg:p-4 border {{ $inCartQty > 0 ? 'border-slate-900 dark:border-blue-500 ring-1 ring-slate-900 dark:ring-blue-500' : 'border-slate-200 dark:border-slate-700' }} card-shadow cursor-pointer hover:border-slate-400 dark:hover:border-slate-500 transition-all active:scale-95 flex flex-col h-full group relative overflow-hidden">
                                
                                {{-- In-Cart Badge Indicator --}}
                                @if($inCartQty > 0)
                                    <div class="absolute top-2 right-2 z-10 bg-slate-900 dark:bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow-sm">
                                        {{ $inCartQty }}x
                                    </div>
                                @endif

                                @if ($product->image)
                                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-28 lg:h-32 object-fill rounded-lg mb-3 bg-slate-50 dark:bg-slate-700">
                                @else
                                    <div class="w-full h-28 lg:h-32 bg-slate-100 dark:bg-slate-700/60 rounded-lg flex items-center justify-center mb-3 group-hover:bg-slate-200/60 dark:group-hover:bg-slate-700 transition-colors">
                                        <span class="text-3xl">
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
                                
                                <div class="flex-1 flex flex-col justify-between">
                                    <div>
                                        <h3 class="font-bold text-slate-900 dark:text-white mb-1 text-xs lg:text-sm line-clamp-2 leading-tight group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">{{ $product->name }}</h3>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 line-clamp-1 mb-2">{{ $product->description }}</p>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-700">
                                        <p class="font-bold text-emerald-600 dark:text-emerald-400 text-sm lg:text-base">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                        <span class="text-xs bg-slate-900 dark:bg-blue-600 text-white rounded-lg p-1.5 group-hover:bg-slate-800 dark:group-hover:bg-blue-700 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <div class="bg-white dark:bg-slate-800 rounded-xl p-8 border border-slate-200 dark:border-slate-700 border-dashed">
                                    <span class="text-4xl block mb-2">☕</span>
                                    <p class="text-slate-500 dark:text-slate-400 font-medium">Tidak ada menu cafe ditemukan</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- RIGHT COLUMN: DESKTOP & TABLET ORDER CART (col-span-1) --}}
                <div class="hidden lg:flex flex-col bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow h-[calc(100vh-8rem)] sticky top-24 overflow-hidden transition-colors">
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50 flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2 text-base">
                                🛒 Pesanan Cafe Noli
                            </h3>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5">
                                Mode: {{ $orderType === 'dine_in' ? 'MAKAN DI TEMPAT' : ($orderType === 'take_away' ? 'BAWA PULANG' : 'PESAN ANTAR') }} 
                                @if($orderType === 'dine_in')
                                    ({{ $tableNumber ?: 'Belum isi meja' }})
                                @endif
                            </p>
                        </div>
                        @if(count($cart) > 0)
                            <button onclick="confirmResetCart()" class="text-xs text-red-500 hover:underline font-semibold">Kosongkan</button>
                        @endif
                    </div>

                    <div class="p-4 flex-1 overflow-y-auto space-y-3 scrollbar-thin dark:scrollbar-thumb-slate-700">
                        @include('livewire.pos.partials.cart-items')
                    </div>

                    @if (!empty($cart))
                        <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                            @include('livewire.pos.partials.cart-summary')
                        </div>
                    @endif
                </div>

            </div>
        </main>
    </div>

    {{-- MOBILE & TABLET PORTRAIT FLOATING BAR --}}
    @if(count($cart) > 0)
    <div class="fixed bottom-0 left-0 w-full bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 p-4 shadow-2xl lg:hidden z-40 flex justify-between items-center gap-4 safe-area-bottom animate-slide-up transition-colors">
        <div class="flex flex-col">
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ count($cart) }} Menu di keranjang</span>
            <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>
        <button wire:click="openMobileCart" class="bg-slate-900 dark:bg-blue-600 text-white px-6 py-3 rounded-xl font-bold text-sm flex items-center gap-2 hover:bg-slate-800 dark:hover:bg-blue-700 active:scale-95 transition-all shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            Lihat Pesanan
        </button>
    </div>
    @endif

    {{-- MOBILE & TABLET PORTRAIT CART MODAL --}}
    @if ($showMobileCart)
    <div class="fixed inset-0 z-50 overflow-y-auto lg:hidden">
        <div class="flex items-end justify-center min-h-screen text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" wire:click="closeMobileCart"></div>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-t-2xl text-left overflow-hidden shadow-xl transform transition-all w-full h-[88vh] flex flex-col border-t border-slate-200 dark:border-slate-700">
                <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-700/50">
                    <h3 class="font-bold text-slate-900 dark:text-white text-lg">Rincian Pesanan Cafe Noli</h3>
                    <button wire:click="closeMobileCart" class="text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white p-2 bg-white dark:bg-slate-700 rounded-full shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-4 flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 space-y-3">
                    @include('livewire.pos.partials.cart-items')
                </div>
                <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 pb-8 safe-area-bottom">
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
            <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" wire:click="closeItemNotesModal"></div>
            <div class="inline-block bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all max-w-md w-full p-6 border border-slate-200 dark:border-slate-700">
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
                        {{-- Sugar Level --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Tingkat Manis (Level Gula):</label>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach(['Normal (100%)', 'Sedikit (50%)', 'Rendah (25%)', 'Tanpa Gula'] as $sugar)
                                    <button type="button" wire:click="$set('tempSugarLevel', '{{ $sugar }}')"
                                        class="py-2 text-xs font-semibold rounded-lg border transition-all text-center {{ $tempSugarLevel === $sugar ? 'bg-slate-900 text-white dark:bg-blue-600 dark:text-white border-slate-900 dark:border-blue-600' : 'bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-slate-400' }}">
                                        {{ $sugar }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Ice Level --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Tingkat Es (Level Es):</label>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach(['Normal', 'Sedikit Es', 'Tanpa Es', 'Panas (Hot)'] as $ice)
                                    <button type="button" wire:click="$set('tempIceLevel', '{{ $ice }}')"
                                        class="py-2 text-xs font-semibold rounded-lg border transition-all text-center {{ $tempIceLevel === $ice ? 'bg-slate-900 text-white dark:bg-blue-600 dark:text-white border-slate-900 dark:border-blue-600' : 'bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-slate-400' }}">
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

    {{-- PAYMENT MODAL (WITH ON-SCREEN TOUCH NUMPAD & CLEAN MINIMALIST DESIGN) --}}
    @if ($showPaymentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" wire:click="closePaymentModal"></div>
                <div class="inline-block bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all max-w-xl w-full border border-slate-200 dark:border-slate-700">
                    <form wire:submit.prevent="processPayment">
                        {{-- Header --}}
                        <div class="bg-slate-900 dark:bg-slate-700 text-white px-6 py-4 flex justify-between items-center border-b border-slate-800 dark:border-slate-600">
                            <div>
                                <h3 class="text-base font-bold">Pembayaran Cafe Noli</h3>
                                <p class="text-xs opacity-90 font-medium">Tipe: {{ $orderType === 'dine_in' ? 'MAKAN DI TEMPAT' : ($orderType === 'take_away' ? 'BAWA PULANG' : 'PESAN ANTAR') }} {{ $orderType === 'dine_in' ? ($tableNumber ? '| Meja: '.$tableNumber : '') : ($customerName ? '| Pelanggan: '.$customerName : '') }}</p>
                            </div>
                            <button type="button" wire:click="closePaymentModal" class="text-white hover:opacity-80"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                        </div>
                        
                        <div class="bg-white dark:bg-slate-800 px-6 py-4 space-y-4">
                            {{-- TOTAL TAGIHAN --}}
                            <div class="bg-slate-50 dark:bg-slate-900/60 p-4 rounded-xl text-center border border-slate-200 dark:border-slate-700">
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold mb-1 uppercase">TOTAL TAGIHAN</p>
                                <p class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($total, 0, ',', '.') }}</p>
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
                                <div class="space-y-3 pt-1 animate-fade-in">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1 uppercase">Uang Diterima (Rp)</label>
                                        <input type="number" wire:model.live="paid" id="paidInput" autofocus
                                            class="block w-full px-3.5 py-2.5 text-lg font-bold border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white"
                                            placeholder="0">
                                    </div>

                                    {{-- Pecahan Cepat --}}
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Pilihan Cepat Pecahan:</label>
                                        <div class="grid grid-cols-4 gap-2">
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
                                        <div class="bg-emerald-50 dark:bg-emerald-950/40 p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-800 flex justify-between items-center animate-fade-in">
                                            <p class="text-xs text-emerald-700 dark:text-emerald-400 font-bold uppercase">Kembalian Konsumen</p>
                                            <p class="text-xl font-bold text-emerald-800 dark:text-emerald-300">Rp {{ number_format($change, 0, ',', '.') }}</p>
                                        </div>
                                    @elseif($paid > 0 && $paid < $total)
                                        <div class="bg-red-50 dark:bg-red-950/40 p-3 rounded-xl border border-red-200 dark:border-red-800 flex justify-between items-center">
                                            <p class="text-xs text-red-700 dark:text-red-400 font-bold">Uang Kurang:</p>
                                            <p class="text-base font-bold text-red-700 dark:text-red-400">- Rp {{ number_format($total - $paid, 0, ',', '.') }}</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- NON-CASH PAYMENT: EXACT AMOUNT BANNER --}}
                                <div class="bg-slate-50 dark:bg-slate-900/60 p-4 rounded-xl border border-slate-200 dark:border-slate-700 text-center space-y-1 animate-fade-in">
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
                <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm"></div>
                <div class="inline-block bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all max-w-md w-full p-6 border border-slate-200 dark:border-slate-700">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/40 rounded-full flex items-center justify-center mx-auto mb-3 animate-bounce text-emerald-600 dark:text-emerald-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-1">Pesanan Berhasil Disimpan!</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Transaksi telah tercatat di sistem Cafe Noli</p>
                        
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