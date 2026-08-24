<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    @include('livewire.includes.sidebar')

    <div class="main-content-layout flex flex-col min-h-screen">
        @include('livewire.includes.header', [
            'title' => 'Manajemen HPP & Margin Keuntungan',
            'subtitle' => 'Kelola harga modal resep (HPP)',
        ])

        <main class="p-4 sm:p-6 space-y-6 flex-1">
            
            {{-- KPI SUMMARY CARDS (RINGKASAN FINANSIAL CAFE) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5">
                {{-- 1. Rata-Rata Margin --}}
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm transition-colors flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        @if($avgMargin >= 50)
                            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                Sangat Sehat
                            </span>
                        @else
                            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                Perlu Pantau
                            </span>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Rata-Rata Margin</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($avgMargin, 1) }}%</h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Rata-rata persentase margin menu</p>
                    </div>
                </div>

                {{-- 2. Rata-Rata Profit / Item --}}
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm transition-colors flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2.5 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-xl shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            Per Cup/Porsi
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Rata-Rata Profit / Porsi</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-blue-600 dark:text-blue-400 mt-1 truncate">Rp {{ number_format($avgProfitPerItem, 0, ',', '.') }}</h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Keuntungan bersih per menu</p>
                    </div>
                </div>

                {{-- 3. Rata-Rata Food Cost --}}
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm transition-colors flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2.5 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-xl shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        @if($avgFoodCost <= 35)
                            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                Ideal (&le; 35%)
                            </span>
                        @else
                            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                Tinggi (&gt; 35%)
                            </span>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Rata-Rata Food Cost</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ number_format($avgFoodCost, 1) }}%</h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Rasio modal bahan vs harga jual</p>
                    </div>
                </div>

                {{-- 4. Margin Kritis (< 35%) --}}
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm transition-colors flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2.5 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 rounded-xl shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        @if($lowMarginCount > 0)
                            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800">
                                Perlu Review
                            </span>
                        @else
                            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                Semua Aman
                            </span>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Margin Kritis (&lt; 35%)</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-red-600 dark:text-red-400 mt-1">{{ $lowMarginCount }} <span class="text-sm font-bold text-slate-500 dark:text-slate-400">Menu</span></h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Menu dengan margin tipis</p>
                    </div>
                </div>
            </div>

            {{-- TOP CONTROL BAR --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm transition-colors p-4 sm:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                        {{ $showCalculator ? 'Kalkulator Resep & Simulasi HPP Menu' : 'Daftar Menu & Margin Keuntungan' }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ $showCalculator ? 'Hitung modal bahan per porsi dan dapatkan saran penetapan harga jual optimal dengan AI' : 'Pantau harga modal resep, dan persentase keuntungan setiap menu' }}
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3">
                    <button wire:click="toggleCalculator" class="px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-bold bg-slate-900 hover:bg-slate-800 active:bg-slate-950 dark:bg-emerald-600 dark:hover:bg-emerald-700 text-white rounded-xl transition-colors shadow-xs flex items-center justify-center shrink-0">
                        @if($showCalculator)
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Kembali ke Daftar Menu
                        @else
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            Kalkulator HPP
                        @endif
                    </button>
                    
                    @if(!$showCalculator)
                    {{-- Filter Kategori --}}
                    <div class="relative group w-full sm:w-auto">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 group-hover:text-emerald-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                        </div>
                        <select wire:model.live="categoryFilter" 
                            class="appearance-none w-full sm:w-48 pl-10 pr-10 py-2 sm:py-2.5 text-xs sm:text-sm font-medium border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer transition-all outline-none">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"></path></svg>
                        </div>
                    </div>

                    {{-- Search Input --}}
                    <div class="relative w-full sm:w-60">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari nama menu / SKU..."
                            class="w-full pl-9 pr-3.5 py-2 sm:py-2.5 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs sm:text-sm font-medium">
                        <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    @endif
                </div>
            </div>

            @if(!$showCalculator)
            {{-- TABLE SECTION --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm transition-colors overflow-hidden">
                <div class="overflow-x-auto scrollbar-thin">
                    <table class="w-full text-left border-collapse min-w-[760px]">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 text-[11px] uppercase text-slate-500 dark:text-slate-400 font-bold tracking-wider border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-5 sm:px-6 py-3.5">Menu Cafe</th>
                                <th class="px-4 py-3.5">Kategori</th>
                                <th class="px-4 py-3.5 text-rose-600 dark:text-rose-400">Modal (HPP)</th>
                                <th class="px-4 py-3.5">Harga Jual</th>
                                <th class="px-4 py-3.5 text-emerald-600 dark:text-emerald-400">Profit / Porsi</th>
                                <th class="px-4 py-3.5 text-center">Margin (%)</th>
                                <th class="px-5 sm:px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/80 dark:divide-slate-700 text-xs sm:text-sm">
                            @forelse($products as $product)
                                @php
                                    $hpp = (float) $product->harga_beli;
                                    $jual = (float) $product->price;
                                    $profit = max(0, $jual - $hpp);
                                    $margin = $jual > 0 ? ($profit / $jual) * 100 : 0;
                                @endphp
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="shrink-0 h-10 w-10">
                                                @if ($product->image)
                                                    <img class="h-10 w-10 rounded-xl object-cover border border-slate-200 dark:border-slate-700" src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}">
                                                @else
                                                    <div class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-lg text-slate-500 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                                        ☕
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-bold text-slate-900 dark:text-white truncate max-w-[180px] sm:max-w-none">{{ $product->name }}</div>
                                                <div class="text-xs font-mono text-slate-400 dark:text-slate-500">{{ $product->sku }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-slate-600 dark:text-slate-300 font-medium">
                                        {{ $product->category->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap font-bold text-rose-600 dark:text-rose-400">
                                        Rp {{ number_format($hpp, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap font-bold text-slate-900 dark:text-white">
                                        Rp {{ number_format($jual, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap font-bold text-emerald-600 dark:text-emerald-400">
                                        +Rp {{ number_format($profit, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                        @if($margin >= 50)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                Sangat Baik ({{ number_format($margin, 1) }}%)
                                            </span>
                                        @elseif($margin >= 35)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                Normal ({{ number_format($margin, 1) }}%)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                Tipis ({{ number_format($margin, 1) }}%)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-right shrink-0">
                                        <button wire:click="editProductInCalculator({{ $product->id }})"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 rounded-lg text-xs font-bold transition-colors ml-auto active:scale-95">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Hitung / Edit HPP
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <p class="text-slate-500 dark:text-slate-400 font-medium text-xs sm:text-sm">Menu tidak ditemukan</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                <div class="px-5 sm:px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    {{ $products->links() }}
                </div>
            </div>
            @else
            {{-- CALCULATOR VIEW (RESPONSIVE FULL WIDTH STACK) --}}
            <div class="w-full space-y-6">
                
                {{-- CARD 1: INPUT DATA PRODUK & KOMPONEN BIAYA --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm p-4 sm:p-6 lg:p-8 transition-colors">
                    
                    {{-- Form Header: Pilih Menu & Nama Produk --}}
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5 mb-6">
                        <div class="lg:col-span-5" x-data="{
                            open: false,
                            search: '',
                            products: @js($allProducts->map(fn($p) => ['id' => (string)$p->id, 'name' => $p->name])),
                            selectedId: @entangle('selected_product_id').live,
                            get selectedName() {
                                if (!this.selectedId) return '-- Pilih Menu Cafe --';
                                let item = this.products.find(p => p.id == this.selectedId);
                                return item ? item.name : '-- Pilih Menu Cafe --';
                            },
                            get filteredProducts() {
                                if (!this.search) return this.products;
                                return this.products.filter(p => p.name.toLowerCase().includes(this.search.toLowerCase()));
                            },
                            select(id) {
                                this.selectedId = id;
                                this.open = false;
                                this.search = '';
                            }
                        }" @click.outside="open = false">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                <span>Pilih Dari Menu Cafe</span>
                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">Bisa Cari Menu</span>
                            </label>
                            
                            {{-- Trigger Button --}}
                            <div class="relative">
                                <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())" class="w-full px-3.5 py-2.5 text-xs sm:text-sm text-left border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 flex items-center justify-between cursor-pointer">
                                    <span class="truncate font-medium" :class="!selectedId ? 'text-slate-400 dark:text-slate-500' : ''" x-text="selectedName"></span>
                                    <svg class="w-4 h-4 text-slate-400 ml-2 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                
                                {{-- Dropdown Panel --}}
                                <div x-show="open" x-transition.origin.top.duration.150ms class="absolute left-0 right-0 top-full mt-1.5 z-50 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl overflow-hidden" style="display: none;">
                                    
                                    {{-- Search Box --}}
                                    <div class="p-2 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                                        <div class="relative">
                                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                            <input type="text" x-model="search" x-ref="searchInput" @keydown.escape="open = false" placeholder="Ketik cari nama menu..." class="w-full pl-9 pr-3 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:outline-hidden">
                                        </div>
                                    </div>
                                    
                                    {{-- Items List --}}
                                    <div class="max-h-60 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50">
                                        {{-- Option Reset --}}
                                        <div @click="select('')" class="px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer flex items-center justify-between transition-colors">
                                            <span>-- Pilih Menu Cafe --</span>
                                            <template x-if="!selectedId">
                                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </template>
                                        </div>
                                        
                                        {{-- Options --}}
                                        <template x-for="p in filteredProducts" :key="p.id">
                                            <div @click="select(p.id)" class="px-4 py-2.5 text-xs font-medium text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/50 cursor-pointer flex items-center justify-between transition-colors" :class="selectedId == p.id ? 'bg-emerald-50/70 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300 font-bold' : ''">
                                                <span x-text="p.name"></span>
                                                <template x-if="selectedId == p.id">
                                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                </template>
                                            </div>
                                        </template>
                                        
                                        <div x-show="filteredProducts.length === 0" class="px-4 py-6 text-xs text-center text-slate-400 dark:text-slate-500">
                                            Menu tidak ditemukan.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="lg:col-span-7">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Nama Produk / Menu Cafe</label>
                            <input type="text" wire:model="nama_produk" placeholder="Contoh: Kopi Susu Gula Aren, Croissant Butter, Matcha Latte" class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 font-medium">
                            @error('nama_produk') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Tombol AI CTA --}}
                    <div class="mb-6 sm:mb-8">
                        <button type="button" wire:click="analyzeWithAI" wire:loading.attr="disabled" wire:target="analyzeWithAI" class="relative overflow-hidden bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-85 disabled:cursor-not-allowed text-white font-bold rounded-xl py-3 px-4 sm:px-6 w-full text-xs sm:text-sm transition-all duration-200 shadow-sm flex justify-center items-center min-h-[46px]">
                            <div wire:loading.remove wire:target="analyzeWithAI" class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <span>Bantu Analisis Resep & Modal Bahan dengan AI</span>
                            </div>
                            
                            <div wire:loading.flex wire:target="analyzeWithAI" class="items-center justify-center gap-2.5">
                                <svg class="animate-spin h-4 w-4 sm:h-5 sm:w-5 text-white shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg> 
                                <span class="font-medium tracking-wide">Menganalisis Takaran Resep Terbaik...</span>
                            </div>
                        </button>
                    </div>

                    {{-- List Biaya Bahan Baku --}}
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Daftar Resep & Biaya Bahan Baku</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Rincian takaran resep per porsi dan estimasi harga beli supplier</p>
                            </div>
                        </div>
                        
                        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200/80 dark:border-slate-700 overflow-hidden">
                            <div class="hidden md:grid grid-cols-12 gap-4 p-3.5 text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900">
                                <div class="col-span-4">Nama Bahan Baku</div>
                                <div class="col-span-3">Takaran per Porsi</div>
                                <div class="col-span-3">Harga Beli Supplier</div>
                                <div class="col-span-2 text-right pr-8">Modal Bahan</div>
                            </div>
                            
                            <div class="divide-y divide-slate-200/80 dark:divide-slate-700">
                                @foreach($bahan_baku as $index => $bahan)
                                <div class="p-3.5 sm:p-4 grid grid-cols-1 md:grid-cols-12 gap-3 md:gap-4 items-center hover:bg-slate-100/50 dark:hover:bg-slate-800/50 transition-colors relative"
                                     x-data="{
                                         rawVal: @entangle('bahan_baku.' . $index . '.harga_beli').live,
                                         displayVal: '',
                                         formatRupiah(val) {
                                             let clean = String(val).replace(/\D/g, '');
                                             this.displayVal = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                             this.rawVal = clean ? parseInt(clean, 10) : 0;
                                         },
                                         init() {
                                             if (this.rawVal && this.rawVal > 0) {
                                                 this.displayVal = new Intl.NumberFormat('id-ID').format(this.rawVal);
                                             }
                                             this.$watch('rawVal', (newVal) => {
                                                 if (newVal !== undefined && newVal !== null) {
                                                     let clean = String(newVal).replace(/\D/g, '');
                                                     let formatted = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                                     if (this.displayVal !== formatted) {
                                                         this.displayVal = formatted;
                                                     }
                                                 }
                                             });
                                         }
                                     }">
                                    
                                    {{-- Nama Bahan --}}
                                    <div class="md:col-span-4">
                                        <label class="block md:hidden text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Nama Bahan Baku</label>
                                        <input type="text" wire:model.live="bahan_baku.{{ $index }}.nama" placeholder="Contoh: Biji Kopi Espresso, Susu UHT" class="w-full px-3 py-2 text-xs sm:text-sm font-medium border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                    </div>
                                    
                                    {{-- Takaran Resep --}}
                                    <div class="md:col-span-3">
                                        <label class="block md:hidden text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Takaran per Porsi</label>
                                        <div class="flex gap-2">
                                            <input type="number" step="any" wire:model.live.debounce.400ms="bahan_baku.{{ $index }}.takaran" wire:change="calculateSubtotal({{ $index }})" placeholder="0" class="w-1/2 px-2.5 py-2 text-xs sm:text-sm font-bold border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                            <div class="relative group w-1/2">
                                                <select wire:model.live="bahan_baku.{{ $index }}.satuan_takaran" wire:change="calculateSubtotal({{ $index }})" class="appearance-none w-full px-2 py-2 pr-6 text-xs font-medium border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 cursor-pointer transition-all outline-none">
                                                    <option value="gram">Gram (gr)</option>
                                                    <option value="ml">Mililiter (ml)</option>
                                                    <option value="pcs">Pcs / Butir</option>
                                                    <option value="sachet">Sachet</option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-1.5 pointer-events-none text-slate-400">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"></path></svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Harga Beli Supplier (Auto Format Rupiah Realtime) --}}
                                    <div class="md:col-span-3">
                                        <label class="block md:hidden text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Harga Beli Supplier</label>
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1.5">
                                                <div class="relative flex-1">
                                                    <span class="absolute inset-y-0 left-0 flex items-center pl-2 text-[11px] font-bold text-slate-400 pointer-events-none">Rp</span>
                                                    <input type="text" 
                                                           x-model="displayVal" 
                                                           @input="formatRupiah($event.target.value)" 
                                                           @change="$wire.calculateSubtotal({{ $index }})"
                                                           placeholder="0" 
                                                           class="w-full pl-7 pr-2 py-2 text-xs sm:text-sm font-bold border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                                </div>
                                                <span class="text-xs text-slate-400 font-bold">/</span>
                                                <input type="number" step="any" wire:model.live.debounce.400ms="bahan_baku.{{ $index }}.jumlah_beli" wire:change="calculateSubtotal({{ $index }})" placeholder="1" class="w-12 px-1.5 py-2 text-xs sm:text-sm font-bold text-center border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                                <div class="relative group w-18">
                                                    <select wire:model.live="bahan_baku.{{ $index }}.satuan_beli" wire:change="calculateSubtotal({{ $index }})" class="appearance-none w-full px-1.5 py-2 pr-5 text-xs font-medium border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 cursor-pointer transition-all outline-none">
                                                        <option value="kg">Kg</option>
                                                        <option value="liter">Liter</option>
                                                        <option value="gram">Gram</option>
                                                        <option value="ml">Ml</option>
                                                        <option value="pack">Pack</option>
                                                        <option value="pcs">Pcs</option>
                                                        <option value="botol">Botol</option>
                                                        <option value="kaleng">Kaleng</option>
                                                        <option value="dus">Dus</option>
                                                    </select>
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-1 pointer-events-none text-slate-400">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"></path></svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Modal per Porsi --}}
                                    <div class="md:col-span-2 flex md:block items-center justify-between md:text-right pr-0 md:pr-8 pt-2 md:pt-0 border-t md:border-t-0 border-slate-200/60 dark:border-slate-700/60">
                                        <label class="block md:hidden text-xs font-bold text-slate-600 dark:text-slate-300">Modal Bahan:</label>
                                        <div>
                                            <div class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($bahan['subtotal'], 0, ',', '.') }}</div>
                                            <span class="text-[10px] text-slate-400 font-medium hidden md:inline">per porsi</span>
                                        </div>
                                    </div>
                                    
                                    {{-- Tombol Hapus --}}
                                    <button wire:click="removeIngredientRow({{ $index }})" class="absolute right-3 top-3 md:top-1/2 md:-translate-y-1/2 text-slate-400 hover:text-rose-600 bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors p-1.5 border border-slate-200 dark:border-slate-700" title="Hapus Bahan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            
                            @if(count($bahan_baku) == 0)
                            <div class="p-8 text-center text-slate-500 dark:text-slate-400 text-xs sm:text-sm">
                                Belum ada komponen biaya bahan. Tambahkan secara manual atau gunakan bantuan AI.
                            </div>
                            @endif
                            
                            <div class="p-4 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700">
                                <button wire:click="addIngredientRow" class="w-full py-2.5 border-2 border-dashed border-emerald-300 dark:border-emerald-700/50 rounded-xl text-emerald-600 dark:text-emerald-400 font-bold text-xs sm:text-sm hover:bg-emerald-50 dark:hover:bg-emerald-900/20 hover:border-emerald-400 transition-colors flex justify-center items-center">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    <span>Tambah Bahan Baku</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 1.5: ALOKASI BIAYA TETAP / OVERHEAD OPERASIONAL CAFE --}}
                @php $calc = $this->hppCalculation; @endphp
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm p-5 sm:p-7 lg:p-8 relative overflow-hidden transition-colors" x-data="{ expanded: true }">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 cursor-pointer" @click="expanded = !expanded">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 border border-blue-200/60 dark:border-blue-800/60">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                                        Alokasi Biaya Tetap / Overhead Cafe
                                    </h2>
                                    @if($mode_alokasi_ops === 'rincian' && ($calc['totalBiayaTetapBulanan'] ?? 0) > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                            Rp {{ number_format($calc['totalBiayaTetapBulanan'], 0, ',', '.') }} / bln
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                                    Bagi rata total beban operasional cafe (sewa, gaji, listrik) ke seluruh volume penjualan menu
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 self-end sm:self-center">
                            <button class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 bg-slate-100 dark:bg-slate-700 rounded-xl transition-colors">
                                <svg class="w-5 h-5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div x-show="expanded" x-collapse class="mt-6 space-y-5">
                        
                        {{-- MODE ALOKASI TABS --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button type="button" wire:click="setModeAlokasi('rincian')"
                                class="p-3.5 rounded-xl border-2 text-left transition-all flex items-center justify-between {{ $mode_alokasi_ops === 'rincian' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-950/30 ring-1 ring-blue-500/30' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 hover:border-slate-300' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center {{ $mode_alokasi_ops === 'rincian' ? 'border-blue-500 bg-blue-500' : 'border-slate-300 dark:border-slate-600' }}">
                                        @if($mode_alokasi_ops === 'rincian') <div class="w-1.5 h-1.5 rounded-full bg-white"></div> @endif
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 dark:text-white">Bagi Rata Seluruh Penjualan Cafe (Standar F&B)</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400">Total biaya cafe dibagi estimasi total seluruh porsi terjual</div>
                                    </div>
                                </div>
                            </button>

                            <button type="button" wire:click="setModeAlokasi('manual')"
                                class="p-3.5 rounded-xl border-2 text-left transition-all flex items-center justify-between {{ $mode_alokasi_ops === 'manual' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-950/30 ring-1 ring-blue-500/30' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 hover:border-slate-300' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center {{ $mode_alokasi_ops === 'manual' ? 'border-blue-500 bg-blue-500' : 'border-slate-300 dark:border-slate-600' }}">
                                        @if($mode_alokasi_ops === 'manual') <div class="w-1.5 h-1.5 rounded-full bg-white"></div> @endif
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 dark:text-white">Input Manual per Porsi</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400">Langsung tentukan nominal overhead per cup</div>
                                    </div>
                                </div>
                            </button>
                        </div>

                        @if($mode_alokasi_ops === 'rincian')
                            {{-- TARGET ESTIMASI TOTAL PENJUALAN SELURUH MENU CAFE --}}
                            <div class="p-4 bg-blue-50/60 dark:bg-blue-950/20 rounded-xl border border-blue-200/80 dark:border-blue-900/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-blue-950 dark:text-blue-200 uppercase tracking-wider">Estimasi Total Penjualan Seluruh Menu Cafe (Total Porsi / Bulan)</label>
                                    <p class="text-[11px] text-blue-700/80 dark:text-blue-400/80">Perkiraan akumulasi seluruh menu yang laku di cafe sebulan (misal: 3.000 cup ≈ 100 cup/hari) untuk membagi rata beban tetap bersama.</p>
                                </div>
                                <div class="relative w-full sm:w-48 shrink-0">
                                    <input type="number" wire:model.live.debounce.300ms="target_penjualan_bulanan" placeholder="3000" min="1" class="w-full px-3 py-2 text-xs sm:text-sm font-bold text-center border border-blue-300 dark:border-blue-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">total cup</span>
                                </div>
                            </div>

                            {{-- LIST KOMPONEN BIAYA TETAP --}}
                            <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200/80 dark:border-slate-700 overflow-hidden">
                                <div class="flex items-center justify-between p-3.5 bg-slate-100 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                    <div class="w-7/12">Nama Biaya Operasional / Overhead</div>
                                    <div class="w-5/12 text-right pr-10">Total Biaya (per bulan)</div>
                                </div>

                                <div class="divide-y divide-slate-200/80 dark:divide-slate-700">
                                    @foreach($biaya_tetap_items as $bIndex => $bItem)
                                    <div class="p-3 sm:p-3.5 flex items-center gap-3 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 transition-colors"
                                         x-data="{
                                             rawVal: @entangle('biaya_tetap_items.' . $bIndex . '.nominal').live,
                                             displayVal: '',
                                             formatRupiah(val) {
                                                 let clean = String(val).replace(/\D/g, '');
                                                 this.displayVal = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                                 this.rawVal = clean ? parseInt(clean, 10) : 0;
                                             },
                                             init() {
                                                 if (this.rawVal && this.rawVal > 0) {
                                                     this.displayVal = new Intl.NumberFormat('id-ID').format(this.rawVal);
                                                 }
                                                 this.$watch('rawVal', (newVal) => {
                                                     if (newVal !== undefined && newVal !== null) {
                                                         let clean = String(newVal).replace(/\D/g, '');
                                                         let formatted = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                                         if (this.displayVal !== formatted) {
                                                             this.displayVal = formatted;
                                                         }
                                                     }
                                                 });
                                             }
                                         }">
                                        
                                        {{-- Nama Biaya --}}
                                        <div class="w-7/12">
                                            <input type="text" wire:model.live="biaya_tetap_items.{{ $bIndex }}.nama" placeholder="Contoh: Sewa Ruko, Gaji Barista, Listrik & Wi-Fi" class="w-full px-3 py-2 text-xs sm:text-sm font-medium border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                        </div>

                                        {{-- Nominal Bulanan --}}
                                        <div class="w-5/12 flex items-center gap-2">
                                            <div class="relative flex-1">
                                                <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-xs font-bold text-slate-400 pointer-events-none">Rp</span>
                                                <input type="text" x-model="displayVal" @input="formatRupiah($event.target.value)" placeholder="0" class="w-full pl-8 pr-3 py-2 text-xs sm:text-sm font-bold text-right border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <button type="button" wire:click="removeBiayaTetapItem({{ $bIndex }})" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors shrink-0" title="Hapus Biaya">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <div class="p-3.5 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-3">
                                    <button type="button" wire:click="addBiayaTetapItem" class="w-full sm:w-auto px-4 py-2 border border-blue-300 dark:border-blue-700/60 rounded-xl text-blue-600 dark:text-blue-400 font-bold text-xs hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-colors flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        <span>Tambah Biaya</span>
                                    </button>

                                    <button type="button" wire:click="resetBiayaTetapPreset" class="text-xs font-semibold text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-300 transition-colors flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        <span>Muat Preset Biaya Cafe Standar</span>
                                    </button>
                                </div>
                            </div>

                            {{-- SUMMARY ALOKASI BIAYA MASUK HPP --}}
                            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div>
                                        <span class="text-xs font-bold text-emerald-900 dark:text-emerald-300 block">Alokasi Beban Masuk HPP:</span>
                                        <span class="text-[11px] text-emerald-700/80 dark:text-emerald-400/80">
                                            Total Beban Rp {{ number_format($calc['totalBiayaTetapBulanan'] ?? 0, 0, ',', '.') }} &divide; {{ number_format($calc['targetUnits'] ?? 3000, 0, ',', '.') }} total porsi cafe/bulan
                                        </span>
                                    </div>
                                </div>
                                <div class="text-left sm:text-right">
                                    <span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400">+Rp {{ number_format($calc['biayaTetap'] ?? 0, 0, ',', '.') }}</span>
                                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300">/ porsi</span>
                                </div>
                            </div>

                        @else
                            {{-- MANUAL INPUT PER PORSI --}}
                            <div wire:key="manual-op-box-{{ $selected_product_id }}"
                                 class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700"
                                 x-data="{
                                     rawOp: @entangle('alokasi_biaya_tetap').live,
                                     displayOp: '',
                                     formatOp(val) {
                                         let clean = String(val).replace(/\D/g, '');
                                         this.displayOp = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                         this.rawOp = clean ? parseInt(clean, 10) : 0;
                                     },
                                     init() {
                                         if (this.rawOp && this.rawOp > 0) {
                                             this.displayOp = new Intl.NumberFormat('id-ID').format(this.rawOp);
                                         }
                                         this.$watch('rawOp', (newVal) => {
                                             if (newVal !== undefined && newVal !== null) {
                                                 let clean = String(newVal).replace(/\D/g, '');
                                                 let formatted = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                                 if (this.displayOp !== formatted) {
                                                     this.displayOp = formatted;
                                                 }
                                             }
                                         });
                                     }
                                 }">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Alokasi Biaya Operasional Langsung (Rp / Porsi)</label>
                                <div class="relative max-w-sm">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400 pointer-events-none">Rp</span>
                                    <input type="text" x-model="displayOp" @input="formatOp($event.target.value)" placeholder="0" class="w-full pl-9 pr-3 py-2.5 text-xs sm:text-sm font-bold border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- CARD 2: HASIL PERHITUNGAN & ANALISIS SENSITIVITAS --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm p-4 sm:p-6 lg:p-8 transition-colors">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        
                        {{-- Rincian HPP --}}
                        <div class="lg:col-span-5 space-y-3.5">
                            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white uppercase tracking-wider">Rincian Modal (HPP) per Porsi</h3>
                            
                            <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-3">
                                {{-- Biaya Bahan Baku --}}
                                <div class="flex justify-between items-center p-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                        </div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Total Bahan Baku</span>
                                    </div>
                                    <span class="text-xs sm:text-sm font-black text-slate-900 dark:text-white">
                                        Rp {{ number_format($calc['totalVariable'], 0, ',', '.') }}
                                    </span>
                                </div>

                                {{-- Biaya Operasional --}}
                                <div class="flex justify-between items-center p-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Biaya Operasional</span>
                                            <span class="text-[10px] text-slate-400 font-medium">
                                                {{ $mode_alokasi_ops === 'rincian' ? 'dari rincian beban tetap' : 'alokasi per porsi' }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="text-xs sm:text-sm font-black text-blue-600 dark:text-blue-400">
                                        +Rp {{ number_format($calc['biayaTetap'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                                
                                {{-- Total HPP Banner --}}
                                <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-800/60 flex items-center justify-between">
                                    <div>
                                        <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider block">Total HPP</span>
                                        <span class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80">Bahan Baku + Biaya Operasional</span>
                                    </div>
                                    <span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">Rp {{ number_format($calc['totalHpp'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Analisis Sensitivitas --}}
                        <div class="lg:col-span-7 space-y-3.5">
                            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white uppercase tracking-wider">Simulasi Sensitivitas Harga</h3>
                            
                            <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-3">
                                <div class="p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700 space-y-2.5">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs">
                                        <span class="font-bold text-slate-700 dark:text-slate-300">
                                            Kenaikan Bahan Baku: <span class="text-emerald-600 dark:text-emerald-400 font-black">+{{ $kenaikan_persen }}%</span>
                                        </span>
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                            HPP Baru: <span class="font-black">Rp {{ number_format($calc['simulatedHpp'], 0, ',', '.') }}</span>
                                        </span>
                                    </div>
                                    <input type="range" wire:model.live="kenaikan_persen" min="0" max="100" class="w-full h-2 bg-slate-100 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                                    <div class="flex justify-between items-center text-[10px] font-bold text-slate-400 pt-0.5">
                                        <span>0%</span>
                                        <span>50%</span>
                                        <span>100%</span>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                    <div class="bg-white dark:bg-slate-800 p-2.5 sm:p-3 rounded-xl border border-slate-200/80 dark:border-slate-700 text-center">
                                        <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase">HPP Saat Ini</div>
                                        <div class="text-xs sm:text-sm font-black text-slate-900 dark:text-white mt-0.5 truncate">Rp {{ number_format($calc['totalHpp'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="bg-emerald-50/80 dark:bg-emerald-950/40 p-2.5 sm:p-3 rounded-xl border border-emerald-200 dark:border-emerald-800/60 text-center">
                                        <div class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase">HPP Baru</div>
                                        <div class="text-xs sm:text-sm font-black text-emerald-700 dark:text-emerald-300 mt-0.5 truncate">Rp {{ number_format($calc['simulatedHpp'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="bg-amber-50/80 dark:bg-amber-950/40 p-2.5 sm:p-3 rounded-xl border border-amber-200 dark:border-amber-800/60 text-center">
                                        <div class="text-[10px] font-bold text-amber-700 dark:text-amber-400 uppercase">Jual Min.</div>
                                        <div class="text-xs sm:text-sm font-black text-amber-700 dark:text-amber-300 mt-0.5 truncate">Rp {{ number_format($calc['simulatedHpp'] * 1.1, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="bg-blue-50/80 dark:bg-blue-950/40 p-2.5 sm:p-3 rounded-xl border border-blue-200 dark:border-blue-800/60 text-center">
                                        <div class="text-[10px] font-bold text-blue-700 dark:text-blue-400 uppercase">Margin Baru</div>
                                        <div class="text-xs sm:text-sm font-black text-blue-700 dark:text-blue-300 mt-0.5">
                                            {{ $calc['simulatedHpp'] > 0 ? number_format(max(0, (($price ?? 0) - $calc['simulatedHpp']) / ($price ?: 1) * 100), 1) : 0 }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 3: REKOMENDASI STRATEGI HARGA JUAL --}}
                @php $tiers = $this->pricingTiers; @endphp
                @if(!empty($tiers))
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 card-shadow p-5 sm:p-7 lg:p-8 relative overflow-hidden transition-colors" x-data="{ expanded: true }">
                    <div class="flex justify-between items-center cursor-pointer mb-2" @click="expanded = !expanded">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center">
                                Rekomendasi Strategi Harga Jual
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Saran penetapan harga jual untuk menu: <span class="font-bold text-slate-700 dark:text-slate-300">{{ $nama_produk ?: 'Menu Baru' }}</span></p>
                        </div>
                        <button class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 bg-slate-100 dark:bg-slate-700 rounded-xl transition-colors">
                            <svg class="w-5 h-5 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </div>
                    
                    <div x-show="expanded" x-collapse class="mt-6 space-y-4">
                        
                        {{-- Tier Kompetitif --}}
                        <div wire:click="selectTier('kompetitif')" class="w-full flex flex-col md:flex-row justify-between items-start md:items-center p-4 sm:p-5 rounded-2xl border-2 cursor-pointer transition-all {{ $selected_tier === 'kompetitif' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 shadow-xs ring-1 ring-emerald-500/30' : 'border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/40 hover:border-slate-300 dark:hover:border-slate-600' }}">
                            <div class="flex items-center gap-3 sm:gap-4 mb-3 md:mb-0">
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 {{ $selected_tier === 'kompetitif' ? 'border-emerald-500 bg-emerald-500' : 'border-slate-300 dark:border-slate-600' }}">
                                    @if($selected_tier === 'kompetitif')
                                        <div class="w-2 h-2 rounded-full bg-white"></div>
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2.5 mb-1">
                                        <span class="bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 rounded-full px-3 py-0.5 text-xs font-bold uppercase tracking-wide">Kompetitif</span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">"Harga bersaing di pasar, cocok untuk menarik pelanggan baru dengan margin tetap sehat."</p>
                                </div>
                            </div>
                            <div class="text-left md:text-right w-full md:w-auto pl-8 md:pl-0 space-y-0.5">
                                <div class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($tiers['kompetitif']['harga'], 0, ',', '.') }}</div>
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                    Profit: <span class="text-emerald-600 dark:text-emerald-400">+Rp {{ number_format($tiers['kompetitif']['profit'], 0, ',', '.') }}</span>
                                </div>
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                    Margin: <span class="text-blue-600 dark:text-blue-400">{{ number_format($tiers['kompetitif']['margin'], 1) }}%</span>
                                </div>
                            </div>
                        </div>

                        {{-- Tier Standar --}}
                        <div wire:click="selectTier('standar')" class="w-full flex flex-col md:flex-row justify-between items-start md:items-center p-4 sm:p-5 rounded-2xl border-2 cursor-pointer transition-all {{ $selected_tier === 'standar' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 shadow-xs ring-1 ring-emerald-500/30' : 'border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/40 hover:border-slate-300 dark:hover:border-slate-600' }}">
                            <div class="flex items-center gap-3 sm:gap-4 mb-3 md:mb-0">
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 {{ $selected_tier === 'standar' ? 'border-emerald-500 bg-emerald-500' : 'border-slate-300 dark:border-slate-600' }}">
                                    @if($selected_tier === 'standar')
                                        <div class="w-2 h-2 rounded-full bg-white"></div>
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2.5 mb-1">
                                        <span class="bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded-full px-3 py-0.5 text-xs font-bold uppercase tracking-wide">Standar (Rekomendasi)</span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">"Harga pasar rata-rata, keseimbangan optimal antara volume penjualan dan keuntungan."</p>
                                </div>
                            </div>
                            <div class="text-left md:text-right w-full md:w-auto pl-8 md:pl-0 space-y-0.5">
                                <div class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($tiers['standar']['harga'], 0, ',', '.') }}</div>
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                    Profit: <span class="text-emerald-600 dark:text-emerald-400">+Rp {{ number_format($tiers['standar']['profit'], 0, ',', '.') }}</span>
                                </div>
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                    Margin: <span class="text-emerald-600 dark:text-emerald-400">{{ number_format($tiers['standar']['margin'], 1) }}%</span>
                                </div>
                            </div>
                        </div>

                        {{-- Tier Premium --}}
                        <div wire:click="selectTier('premium')" class="w-full flex flex-col md:flex-row justify-between items-start md:items-center p-4 sm:p-5 rounded-2xl border-2 cursor-pointer transition-all {{ $selected_tier === 'premium' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 shadow-xs ring-1 ring-emerald-500/30' : 'border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/40 hover:border-slate-300 dark:hover:border-slate-600' }}">
                            <div class="flex items-center gap-3 sm:gap-4 mb-3 md:mb-0">
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 {{ $selected_tier === 'premium' ? 'border-emerald-500 bg-emerald-500' : 'border-slate-300 dark:border-slate-600' }}">
                                    @if($selected_tier === 'premium')
                                        <div class="w-2 h-2 rounded-full bg-white"></div>
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2.5 mb-1">
                                        <span class="bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 rounded-full px-3 py-0.5 text-xs font-bold uppercase tracking-wide">Premium</span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">"Harga kualitas premium, memaksimalkan citra eksklusif cafe dan margin keuntungan."</p>
                                </div>
                            </div>
                            <div class="text-left md:text-right w-full md:w-auto pl-8 md:pl-0 space-y-0.5">
                                <div class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($tiers['premium']['harga'], 0, ',', '.') }}</div>
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                    Profit: <span class="text-emerald-600 dark:text-emerald-400">+Rp {{ number_format($tiers['premium']['profit'], 0, ',', '.') }}</span>
                                </div>
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                    Margin: <span class="text-purple-600 dark:text-purple-400">{{ number_format($tiers['premium']['margin'], 1) }}%</span>
                                </div>
                            </div>
                        </div>
                        
                        </div>
                    </div>
                </div>
                @endif

                {{-- CARD 4: TARGET & PROYEKSI PENJUALAN --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 card-shadow p-5 sm:p-7 lg:p-8 relative overflow-hidden transition-colors" x-data="{ expanded: true }">
                    <div class="flex justify-between items-center cursor-pointer mb-2" @click="expanded = !expanded">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 border border-purple-200/60 dark:border-purple-800/60">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    Target & Proyeksi Penjualan
                                </h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    Simulasi volume penjualan & omzet harian/bulanan untuk mencapai target laba bersih menu
                                </p>
                            </div>
                        </div>
                        <button class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 bg-slate-100 dark:bg-slate-700 rounded-xl transition-colors">
                            <svg class="w-5 h-5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </div>

                    <div x-show="expanded" x-collapse class="mt-6 space-y-6">
                        @php $proj = $this->salesProjection; @endphp

                        {{-- INPUT PARAMETERS --}}
                        <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200 dark:border-slate-700">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                
                                {{-- Input 1: Target Laba Bersih / Bulan --}}
                                <div x-data="{
                                    rawProfit: @entangle('target_laba_bulanan').live,
                                    displayProfit: '',
                                    formatProfit(val) {
                                        let clean = String(val).replace(/\D/g, '');
                                        this.displayProfit = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                        this.rawProfit = clean ? parseInt(clean, 10) : 0;
                                    },
                                    init() {
                                        if (this.rawProfit && this.rawProfit > 0) {
                                            this.displayProfit = new Intl.NumberFormat('id-ID').format(this.rawProfit);
                                        }
                                        this.$watch('rawProfit', (newVal) => {
                                            if (newVal !== undefined && newVal !== null) {
                                                let clean = String(newVal).replace(/\D/g, '');
                                                let formatted = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                                if (this.displayProfit !== formatted) {
                                                    this.displayProfit = formatted;
                                                }
                                            }
                                        });
                                    }
                                }">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                        <span>Target Laba / Bulan</span>
                                        <span class="text-[10px] text-purple-600 dark:text-purple-400 font-semibold lowercase">bersih</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400 pointer-events-none">Rp</span>
                                        <input type="text" x-model="displayProfit" @input="formatProfit($event.target.value)" placeholder="0" class="w-full pl-9 pr-3 py-2.5 text-xs sm:text-sm font-bold border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                                    </div>
                                </div>

                                {{-- Input 2: Harga Jual Pilihan (Rp) --}}
                                <div x-data="{
                                    rawPrice: @entangle('price').live,
                                    displayPrice: '',
                                    formatPrice(val) {
                                        let clean = String(val).replace(/\D/g, '');
                                        this.displayPrice = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                        this.rawPrice = clean ? parseInt(clean, 10) : 0;
                                    },
                                    init() {
                                        if (this.rawPrice && this.rawPrice > 0) {
                                            this.displayPrice = new Intl.NumberFormat('id-ID').format(this.rawPrice);
                                        }
                                        this.$watch('rawPrice', (newVal) => {
                                            if (newVal !== undefined && newVal !== null) {
                                                let clean = String(newVal).replace(/\D/g, '');
                                                let formatted = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                                if (this.displayPrice !== formatted) {
                                                    this.displayPrice = formatted;
                                                }
                                            }
                                        });
                                    }
                                }">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                        <span>Harga Jual Pilihan</span>
                                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold lowercase">per porsi</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400 pointer-events-none">Rp</span>
                                        <input type="text" x-model="displayPrice" @input="formatPrice($event.target.value)" placeholder="0" class="w-full pl-9 pr-3 py-2.5 text-xs sm:text-sm font-bold border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                    </div>
                                </div>

                                {{-- Input 3: Biaya Operasional per Porsi --}}
                                @if($mode_alokasi_ops === 'rincian')
                                <div wire:key="op-cost-rincian-{{ $proj['opCostPerUnit'] }}">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                        <span>Biaya Operasional</span>
                                        <span class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold lowercase">otomatis rincian</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400 pointer-events-none">Rp</span>
                                        <input type="text" 
                                               wire:key="input-op-cost-val-{{ $proj['opCostPerUnit'] }}" 
                                               value="{{ number_format($proj['opCostPerUnit'] ?? 0, 0, ',', '.') }}" 
                                               readonly 
                                               class="w-full pl-9 pr-3 py-2.5 text-xs sm:text-sm font-bold border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 cursor-not-allowed">
                                    </div>
                                </div>
                                @else
                                <div wire:key="proj-op-cost-manual-{{ $selected_product_id }}"
                                     x-data="{
                                    rawOp: @entangle('alokasi_biaya_tetap').live,
                                    displayOp: '',
                                    formatOp(val) {
                                        let clean = String(val).replace(/\D/g, '');
                                        this.displayOp = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                        this.rawOp = clean ? parseInt(clean, 10) : 0;
                                    },
                                    init() {
                                        if (this.rawOp && this.rawOp > 0) {
                                            this.displayOp = new Intl.NumberFormat('id-ID').format(this.rawOp);
                                        }
                                        this.$watch('rawOp', (newVal) => {
                                            if (newVal !== undefined && newVal !== null) {
                                                let clean = String(newVal).replace(/\D/g, '');
                                                let formatted = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                                if (this.displayOp !== formatted) {
                                                    this.displayOp = formatted;
                                                }
                                            }
                                        });
                                    }
                                }">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                        <span>Biaya Operasional</span>
                                        <span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold lowercase">per porsi</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400 pointer-events-none">Rp</span>
                                        <input type="text" x-model="displayOp" @input="formatOp($event.target.value)" placeholder="0" class="w-full pl-9 pr-3 py-2.5 text-xs sm:text-sm font-bold border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                                    </div>
                                </div>
                                @endif

                                {{-- Input 4: Hari Buka / Bulan --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                        <span>Hari Buka / Bulan</span>
                                        <span class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold lowercase">operasional</span>
                                    </label>
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 group-hover:text-blue-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"></path></svg>
                                        </div>
                                        <select wire:model.live="hari_operasional_sebulan" class="appearance-none w-full pl-10 pr-10 py-2.5 text-xs sm:text-sm font-bold border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 cursor-pointer transition-all outline-none">
                                            <option value="30">30 Hari (Setiap Hari)</option>
                                            <option value="26">26 Hari (Libur 1 Hari/Mgg)</option>
                                            <option value="24">24 Hari (Libur Weekend)</option>
                                            <option value="20">20 Hari (Hari Kerja)</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-slate-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"></path></svg>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- PROJECTION RESULT CARDS (MATCHING USER SCREENSHOT) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            
                            {{-- 1. Target Jual / Hari --}}
                            <div class="bg-purple-50/80 dark:bg-purple-950/30 p-5 rounded-2xl border border-purple-200/90 dark:border-purple-900/50 shadow-xs flex flex-col justify-between transition-all hover:border-purple-300 dark:hover:border-purple-800">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-purple-900 dark:text-purple-300 uppercase tracking-wider">Target Jual / Hari</span>
                                    <div class="group relative cursor-pointer inline-flex items-center">
                                        <svg class="w-4 h-4 text-purple-400 dark:text-purple-400 hover:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <div class="absolute bottom-full right-0 mb-2 w-52 p-2.5 bg-slate-900 text-white text-[11px] rounded-xl shadow-xl hidden group-hover:block z-20 text-center font-normal">
                                            Rata-rata porsi menu yang harus terjual per hari agar mencapai target bulanan.
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-2xl sm:text-3xl font-black text-purple-950 dark:text-purple-100 tracking-tight">
                                        {{ number_format($proj['targetUnitsDay'], 0, ',', '.') }} <span class="text-base font-bold text-purple-700 dark:text-purple-300">pcs</span>
                                    </div>
                                    <p class="text-[11px] text-purple-600/80 dark:text-purple-400/80 mt-1 font-medium">Berdasarkan {{ $proj['days'] }} hari operasional</p>
                                </div>
                            </div>

                            {{-- 2. Total Jual / Bulan --}}
                            <div class="bg-indigo-50/80 dark:bg-indigo-950/30 p-5 rounded-2xl border border-indigo-200/90 dark:border-indigo-900/50 shadow-xs flex flex-col justify-between transition-all hover:border-indigo-300 dark:hover:border-indigo-800">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-indigo-900 dark:text-indigo-300 uppercase tracking-wider">Total Jual / Bulan</span>
                                    <div class="group relative cursor-pointer inline-flex items-center">
                                        <svg class="w-4 h-4 text-indigo-400 dark:text-indigo-400 hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <div class="absolute bottom-full right-0 mb-2 w-52 p-2.5 bg-slate-900 text-white text-[11px] rounded-xl shadow-xl hidden group-hover:block z-20 text-center font-normal">
                                            Total porsi menu yang harus laku terjual dalam 1 bulan kalender.
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-2xl sm:text-3xl font-black text-indigo-950 dark:text-indigo-100 tracking-tight">
                                        {{ number_format($proj['totalUnitsMonth'], 0, ',', '.') }} <span class="text-base font-bold text-indigo-700 dark:text-indigo-300">pcs</span>
                                    </div>
                                    <p class="text-[11px] text-indigo-600/80 dark:text-indigo-400/80 mt-1 font-medium">Volume penjualan target per bulan</p>
                                </div>
                            </div>

                            {{-- 3. Potensi Omzet / Bulan --}}
                            <div class="bg-emerald-50/80 dark:bg-emerald-950/30 p-5 rounded-2xl border border-emerald-200/90 dark:border-emerald-900/50 shadow-xs flex flex-col justify-between transition-all hover:border-emerald-300 dark:hover:border-emerald-800">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-emerald-900 dark:text-emerald-300 uppercase tracking-wider">Potensi Omzet / Bulan</span>
                                    <div class="group relative cursor-pointer inline-flex items-center">
                                        <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400 hover:text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <div class="absolute bottom-full right-0 mb-2 w-52 p-2.5 bg-slate-900 text-white text-[11px] rounded-xl shadow-xl hidden group-hover:block z-20 text-center font-normal">
                                            Total perkiraan perputaran pendapatan kotor dari penjualan menu ini sebulan.
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-2xl sm:text-3xl font-black text-emerald-700 dark:text-emerald-400 tracking-tight truncate">
                                        Rp {{ number_format($proj['potensiOmzet'], 0, ',', '.') }}
                                    </div>
                                    <p class="text-[11px] text-emerald-600/80 dark:text-emerald-500/80 mt-1 font-medium">Total penjualan kotor sebulan</p>
                                </div>
                            </div>

                            {{-- 4. Total Biaya Produksi (Bahan) / Bulan --}}
                            <div class="bg-rose-50/80 dark:bg-rose-950/30 p-5 rounded-2xl border border-rose-200/90 dark:border-rose-900/50 shadow-xs flex flex-col justify-between transition-all hover:border-rose-300 dark:hover:border-rose-800">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-rose-900 dark:text-rose-300 uppercase tracking-wider">Total Biaya Produksi / Bulan</span>
                                    <div class="group relative cursor-pointer inline-flex items-center">
                                        <svg class="w-4 h-4 text-rose-400 dark:text-rose-400 hover:text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <div class="absolute bottom-full right-0 mb-2 w-52 p-2.5 bg-slate-900 text-white text-[11px] rounded-xl shadow-xl hidden group-hover:block z-20 text-center font-normal">
                                            Total modal bahan baku yang dibutuhkan untuk memproduksi target porsi di atas.
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-2xl sm:text-3xl font-black text-rose-700 dark:text-rose-400 tracking-tight truncate">
                                        Rp {{ number_format($proj['totalBiayaProduksi'], 0, ',', '.') }}
                                    </div>
                                    <p class="text-[11px] text-rose-600/80 dark:text-rose-500/80 mt-1 font-medium">Bahan: Rp {{ number_format($proj['variableCost'], 0, ',', '.') }} / porsi</p>
                                </div>
                            </div>

                            {{-- 5. Total Biaya Operasional / Bulan (Otomatis Mengikuti Alokasi Operasional per Porsi) --}}
                            <div class="bg-amber-50/80 dark:bg-amber-950/30 p-5 rounded-2xl border border-amber-200/90 dark:border-amber-900/50 shadow-xs flex flex-col justify-between transition-all hover:border-amber-300 dark:hover:border-amber-800">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-amber-900 dark:text-amber-300 uppercase tracking-wider">Total Biaya Operasional / Bulan</span>
                                    <div class="group relative cursor-pointer inline-flex items-center">
                                        <svg class="w-4 h-4 text-amber-500 dark:text-amber-400 hover:text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <div class="absolute bottom-full right-0 mb-2 w-52 p-2.5 bg-slate-900 text-white text-[11px] rounded-xl shadow-xl hidden group-hover:block z-20 text-center font-normal">
                                            Akumulasi biaya operasional porsi (listrik, air, gas) untuk seluruh target porsi sebulan.
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-2xl sm:text-3xl font-black text-amber-800 dark:text-amber-300 tracking-tight truncate">
                                        Rp {{ number_format($proj['totalBiayaTetap'], 0, ',', '.') }}
                                    </div>
                                    <p class="text-[11px] text-amber-700/80 dark:text-amber-400/80 mt-1 font-medium">
                                        @if($proj['opCostPerUnit'] > 0)
                                            {{ number_format($proj['totalUnitsMonth'], 0, ',', '.') }} porsi &times; Rp {{ number_format($proj['opCostPerUnit'], 0, ',', '.') }}
                                        @else
                                            Belum ada biaya ops per porsi
                                        @endif
                                    </p>
                                </div>
                            </div>

                            {{-- 6. Proyeksi Laba Bersih / Bulan --}}
                            <div class="bg-teal-50/80 dark:bg-teal-950/30 p-5 rounded-2xl border border-teal-200/90 dark:border-teal-900/50 shadow-xs flex flex-col justify-between transition-all hover:border-teal-300 dark:hover:border-teal-800">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-teal-900 dark:text-teal-300 uppercase tracking-wider">Proyeksi Laba Bersih / Bulan</span>
                                    <div class="group relative cursor-pointer inline-flex items-center">
                                        <svg class="w-4 h-4 text-teal-500 dark:text-teal-400 hover:text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <div class="absolute bottom-full right-0 mb-2 w-52 p-2.5 bg-slate-900 text-white text-[11px] rounded-xl shadow-xl hidden group-hover:block z-20 text-center font-normal">
                                            Estimasi laba bersih yang terealisasi setelah omzet dipotong biaya bahan dan biaya tetap.
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-2xl sm:text-3xl font-black text-teal-700 dark:text-teal-300 tracking-tight truncate">
                                        Rp {{ number_format($proj['proyeksiLabaBersih'], 0, ',', '.') }}
                                    </div>
                                    <p class="text-[11px] text-teal-600/80 dark:text-teal-400/80 mt-1 font-medium">Target laba bersih tercapai</p>
                                </div>
                            </div>

                        </div>

                        @if(($proj['unitMargin'] ?? 0) <= 0)
                        <div class="p-3.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <span>Harga jual saat ini lebih kecil atau sama dengan HPP modal bahan baku. Silakan naikkan harga jual atau pilih tier rekomendasi di atas untuk mengaktifkan kalkulasi target.</span>
                        </div>
                        @endif

                    </div>
                </div>

                {{-- ACTION BUTTON: SIMPAN RESEP & TETAPKAN HPP --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 p-4 sm:p-6 transition-colors">
                    <button type="button" wire:click="saveCalculation" wire:loading.attr="disabled" wire:target="saveCalculation" class="w-full sm:max-w-md mx-auto py-3.5 px-6 bg-slate-900 hover:bg-slate-800 active:bg-slate-950 dark:bg-emerald-600 dark:hover:bg-emerald-700 disabled:opacity-85 disabled:cursor-not-allowed text-white text-sm sm:text-base font-bold rounded-xl shadow-md transition-all duration-200 flex justify-center items-center min-h-[52px]">
                        <div wire:loading.remove wire:target="saveCalculation" class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            <span>Simpan Resep & Tetapkan HPP Menu</span>
                        </div>
                        <div wire:loading.flex wire:target="saveCalculation" class="items-center justify-center gap-3">
                            <svg class="animate-spin h-5 w-5 text-white shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Menyimpan Resep & HPP Menu...</span>
                        </div>
                    </button>
                </div>
            </div>
            @endif

        </main>
    </div>

    {{-- MODAL QUICK EDIT HPP & HARGA JUAL --}}
    @if($showEditModal && $editingProduct)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closeModal"></div>
            <div class="inline-block bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all max-w-md w-full border border-slate-200 dark:border-slate-700 p-5 sm:p-6 my-8">
                
                <div class="flex justify-between items-center pb-3 border-b border-slate-200 dark:border-slate-700 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Ubah Modal & Harga Jual</h3>
                        <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold mt-0.5">{{ $editingProduct->name }}</p>
                    </div>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                @php
                    $calcHpp = is_numeric($harga_beli) ? (float)$harga_beli : 0;
                    $calcPrice = is_numeric($price) ? (float)$price : 0;
                    $calcProfit = max(0, $calcPrice - $calcHpp);
                    $calcMargin = $calcPrice > 0 ? ($calcProfit / $calcPrice) * 100 : 0;
                @endphp

                <form wire:submit.prevent="updateHpp" class="space-y-4">
                    {{-- Input HPP Modal dengan Auto Format --}}
                    <div x-data="{
                        rawHpp: @entangle('harga_beli').live,
                        displayHpp: '',
                        formatHpp(val) {
                            let clean = String(val).replace(/\D/g, '');
                            this.displayHpp = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                            this.rawHpp = clean ? parseInt(clean, 10) : 0;
                        },
                        init() {
                            if (this.rawHpp && this.rawHpp > 0) {
                                this.displayHpp = new Intl.NumberFormat('id-ID').format(this.rawHpp);
                            }
                            this.$watch('rawHpp', (newVal) => {
                                if (newVal !== undefined && newVal !== null) {
                                    let clean = String(newVal).replace(/\D/g, '');
                                    let formatted = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                    if (this.displayHpp !== formatted) {
                                        this.displayHpp = formatted;
                                    }
                                }
                            });
                        }
                    }">
                        <label class="block text-xs font-semibold text-rose-600 dark:text-rose-400 mb-1.5 uppercase">Harga Modal / HPP (Rp):</label>
                        <input type="text" 
                               x-model="displayHpp" 
                               @input="formatHpp($event.target.value)" 
                               class="w-full px-3 py-2 text-xs sm:text-sm font-semibold border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500"
                               placeholder="0">
                        @error('harga_beli') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Input Harga Jual dengan Auto Format --}}
                    <div x-data="{
                        rawPrice: @entangle('price').live,
                        displayPrice: '',
                        formatPrice(val) {
                            let clean = String(val).replace(/\D/g, '');
                            this.displayPrice = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                            this.rawPrice = clean ? parseInt(clean, 10) : 0;
                        },
                        init() {
                            if (this.rawPrice && this.rawPrice > 0) {
                                this.displayPrice = new Intl.NumberFormat('id-ID').format(this.rawPrice);
                            }
                            this.$watch('rawPrice', (newVal) => {
                                if (newVal !== undefined && newVal !== null) {
                                    let clean = String(newVal).replace(/\D/g, '');
                                    let formatted = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
                                    if (this.displayPrice !== formatted) {
                                        this.displayPrice = formatted;
                                    }
                                }
                            });
                        }
                    }">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">Harga Jual Kasir (Rp):</label>
                        <input type="text" 
                               x-model="displayPrice" 
                               @input="formatPrice($event.target.value)" 
                               class="w-full px-3 py-2 text-xs sm:text-sm font-semibold border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500"
                               placeholder="0">
                        @error('price') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Margin Calculator Preview --}}
                    <div class="bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 space-y-1.5">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-600 dark:text-slate-400 font-medium">Estimasi Profit / Porsi:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($calcProfit, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-600 dark:text-slate-400 font-medium">Estimasi Margin:</span>
                            <span class="font-bold {{ $calcMargin >= 50 ? 'text-emerald-600 dark:text-emerald-400' : ($calcMargin >= 35 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">{{ number_format($calcMargin, 1) }}%</span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold bg-slate-900 dark:bg-emerald-600 text-white rounded-xl hover:bg-slate-800 dark:hover:bg-emerald-700 transition-colors shadow-xs active:scale-95">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>