<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    @include('livewire.includes.sidebar')

    <div class="lg:pl-64">
        @include('livewire.includes.header', [
            'title' => 'Manajemen HPP & Margin Keuntungan',
            'subtitle' => 'Kelola harga modal resep (HPP)',
        ])

        <main class="p-4 sm:p-6 space-y-6">
            
            {{-- KPI SUMMARY CARDS (RINGKASAN FINANSIAL CAFE) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                {{-- 1. Rata-Rata Margin --}}
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 card-shadow transition-colors flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl">
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
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 card-shadow transition-colors flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2.5 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            Per Cup/Porsi
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Rata-Rata Profit / Porsi</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-blue-600 dark:text-blue-400 mt-1">Rp {{ number_format($avgProfitPerItem, 0, ',', '.') }}</h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Keuntungan bersih per menu</p>
                    </div>
                </div>

                {{-- 3. Rata-Rata Food Cost --}}
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 card-shadow transition-colors flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2.5 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-xl">
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
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 card-shadow transition-colors flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2.5 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 rounded-xl">
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
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 card-shadow transition-colors p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                        {{ $showCalculator ? 'Kalkulator Resep & Simulasi HPP Menu' : 'Daftar Menu & Margin Keuntungan' }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ $showCalculator ? 'Hitung modal bahan per porsi dan dapatkan saran penetapan harga jual optimal dengan AI' : 'Pantau harga modal resep, dan persentase keuntungan setiap menu' }}
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <button wire:click="toggleCalculator" class="px-4 py-2.5 text-sm font-bold bg-slate-900 hover:bg-slate-800 active:bg-slate-950 dark:bg-emerald-600 dark:hover:bg-emerald-700 text-white rounded-xl transition-colors shadow-xs flex items-center justify-center">
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
                    <select wire:model.live="categoryFilter" 
                        class="px-3.5 py-2.5 text-xs font-semibold border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer w-full sm:w-auto">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>

                    {{-- Search --}}
                    <div class="relative w-full sm:w-60">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari nama menu / SKU..."
                            class="w-full pl-9 pr-3.5 py-2.5 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs font-medium">
                        <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    @endif
                </div>
            </div>

            @if(!$showCalculator)
            {{-- TABLE SECTION --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 card-shadow transition-colors overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[700px]">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 text-[11px] uppercase text-slate-500 dark:text-slate-400 font-bold tracking-wider">
                            <tr>
                                <th class="px-5 sm:px-6 py-4 border-b border-slate-200 dark:border-slate-700">Menu Cafe</th>
                                <th class="px-4 py-4 border-b border-slate-200 dark:border-slate-700">Kategori</th>
                                <th class="px-4 py-4 border-b border-slate-200 dark:border-slate-700 text-red-600 dark:text-red-400">Modal (HPP)</th>
                                <th class="px-4 py-4 border-b border-slate-200 dark:border-slate-700">Harga Jual</th>
                                <th class="px-4 py-4 border-b border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400">Profit / Porsi</th>
                                <th class="px-4 py-4 border-b border-slate-200 dark:border-slate-700 text-center">Margin (%)</th>
                                <th class="px-5 sm:px-6 py-4 border-b border-slate-200 dark:border-slate-700 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/80 dark:divide-slate-700 text-sm">
                            @forelse($products as $product)
                                @php
                                    $hpp = (float) $product->harga_beli;
                                    $jual = (float) $product->price;
                                    $profit = max(0, $jual - $hpp);
                                    $margin = $jual > 0 ? ($profit / $jual) * 100 : 0;
                                @endphp
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                                    <td class="px-5 sm:px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if ($product->image)
                                                    <img class="h-10 w-10 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-2xs" src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}">
                                                @else
                                                    <div class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-lg text-slate-500 dark:text-slate-300 border border-slate-200 dark:border-slate-600 shadow-2xs">
                                                        ☕
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 dark:text-white">{{ $product->name }}</div>
                                                <div class="text-xs font-mono text-slate-400 dark:text-slate-500">{{ $product->sku }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300 font-medium">
                                        {{ $product->category->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap font-bold text-red-600 dark:text-red-400">
                                        Rp {{ number_format($hpp, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap font-bold text-slate-900 dark:text-white">
                                        Rp {{ number_format($jual, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap font-bold text-emerald-600 dark:text-emerald-400">
                                        +Rp {{ number_format($profit, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-center">
                                        @if($margin >= 50)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                Sangat Baik ({{ number_format($margin, 1) }}%)
                                            </span>
                                        @elseif($margin >= 35)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                Normal ({{ number_format($margin, 1) }}%)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800">
                                                Tipis ({{ number_format($margin, 1) }}%)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 sm:px-6 py-4 whitespace-nowrap text-right">
                                        <button wire:click="editProductInCalculator({{ $product->id }})"
                                            class="inline-flex items-center px-3 py-1.5 border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 rounded-lg text-xs font-bold transition-colors ml-auto shadow-2xs">
                                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Hitung / Edit HPP
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <p class="text-slate-500 dark:text-slate-400 font-medium">Menu tidak ditemukan</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded-b-2xl">
                    {{ $products->links() }}
                </div>
            </div>
            @else
            {{-- CALCULATOR VIEW (RESPONSIVE FULL WIDTH STACK) --}}
            <div class="w-full space-y-6">
                
                {{-- CARD 1: INPUT DATA PRODUK & KOMPONEN BIAYA --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 card-shadow p-5 sm:p-7 lg:p-8 transition-colors">
                    
                    {{-- Form Header: Pilih Menu & Nama Produk --}}
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-5 mb-6">
                        <div class="md:col-span-5" x-data="{
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
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2 flex items-center justify-between">
                                <span>Pilih Dari Menu Cafe</span>
                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">Bisa Cari Menu</span>
                            </label>
                            
                            {{-- Trigger Button --}}
                            <div class="relative">
                                <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())" class="w-full px-4 py-2.5 text-sm text-left border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow-xs flex items-center justify-between cursor-pointer">
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
                                        {{-- Option Reset / Pilih Menu --}}
                                        <div @click="select('')" class="px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer flex items-center justify-between transition-colors">
                                            <span>-- Pilih Menu Cafe --</span>
                                            <template x-if="!selectedId">
                                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </template>
                                        </div>
                                        
                                        {{-- Filtered Menu Options --}}
                                        <template x-for="p in filteredProducts" :key="p.id">
                                            <div @click="select(p.id)" class="px-4 py-2.5 text-xs font-medium text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/50 cursor-pointer flex items-center justify-between transition-colors" :class="selectedId == p.id ? 'bg-emerald-50/70 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300 font-bold' : ''">
                                                <span x-text="p.name"></span>
                                                <template x-if="selectedId == p.id">
                                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                </template>
                                            </div>
                                        </template>
                                        
                                        {{-- Empty Search Results --}}
                                        <div x-show="filteredProducts.length === 0" class="px-4 py-6 text-xs text-center text-slate-400 dark:text-slate-500">
                                            Menu tidak ditemukan.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="md:col-span-7">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Nama Produk / Menu Cafe</label>
                            <input type="text" wire:model="nama_produk" placeholder="Contoh: Kopi Susu Gula Aren, Croissant Butter, Matcha Latte" class="w-full px-4 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow-xs font-medium">
                            @error('nama_produk') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Tombol AI CTA --}}
                    <div class="mb-8">
                        <button type="button" wire:click="analyzeWithAI" wire:loading.attr="disabled" wire:target="analyzeWithAI" class="relative overflow-hidden bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-85 disabled:cursor-not-allowed text-white font-bold rounded-xl py-3.5 px-6 w-full text-sm sm:text-base transition-all duration-200 shadow-sm hover:shadow flex justify-center items-center min-h-[52px]">
                            
                            {{-- Normal State --}}
                            <div wire:loading.remove wire:target="analyzeWithAI" class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <span>Bantu Analisis Resep & Modal Bahan dengan AI</span>
                            </div>
                            
                            {{-- Loading State --}}
                            <div wire:loading.flex wire:target="analyzeWithAI" class="items-center justify-center gap-3">
                                <svg class="animate-spin h-5 w-5 text-white shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg> 
                                <span class="font-medium tracking-wide">Menganalisis Takaran Resep Terbaik...</span>
                            </div>
                        </button>
                    </div>

                    {{-- Tabel / List Biaya Variabel --}}
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Daftar Resep & Biaya Bahan Baku</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Rincian takaran resep per porsi dan estimasi harga beli supplier</p>
                            </div>
                        </div>
                        
                        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200/80 dark:border-slate-700 overflow-hidden">
                            {{-- Desktop Table Header --}}
                            <div class="hidden md:grid grid-cols-12 gap-4 p-4 text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900">
                                <div class="col-span-4">Nama Bahan Baku</div>
                                <div class="col-span-3">Takaran per Porsi</div>
                                <div class="col-span-3">Harga Beli Supplier</div>
                                <div class="col-span-2 text-right pr-10">Modal Bahan / Porsi</div>
                            </div>
                            
                            <div class="divide-y divide-slate-200/80 dark:divide-slate-700">
                                @foreach($bahan_baku as $index => $bahan)
                                <div class="p-4 grid grid-cols-1 md:grid-cols-12 gap-3.5 md:gap-4 items-center hover:bg-slate-100/50 dark:hover:bg-slate-800/50 transition-colors relative">
                                    {{-- Nama Bahan --}}
                                    <div class="md:col-span-4">
                                        <label class="block md:hidden text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Nama Bahan Baku</label>
                                        <input type="text" wire:model.live="bahan_baku.{{ $index }}.nama" placeholder="Contoh: Biji Kopi Espresso, Susu UHT" class="w-full px-3.5 py-2 text-sm font-medium border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                    </div>
                                    
                                    {{-- Takaran Resep --}}
                                    <div class="md:col-span-3">
                                        <label class="block md:hidden text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Takaran per Porsi</label>
                                        <div class="flex gap-2">
                                            <input type="number" step="any" wire:model.live.debounce.400ms="bahan_baku.{{ $index }}.takaran" wire:change="calculateSubtotal({{ $index }})" placeholder="0" class="w-1/2 px-3 py-2 text-sm font-bold border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                            <select wire:model.live="bahan_baku.{{ $index }}.satuan_takaran" wire:change="calculateSubtotal({{ $index }})" class="w-1/2 px-3 py-2 text-sm font-medium border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 cursor-pointer">
                                                <option value="gram">Gram (gr)</option>
                                                <option value="ml">Mililiter (ml)</option>
                                                <option value="pcs">Pcs / Butir</option>
                                                <option value="sachet">Sachet</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    {{-- Harga Beli Supplier --}}
                                    <div class="md:col-span-3">
                                        <label class="block md:hidden text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Harga Beli Supplier</label>
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1.5">
                                                <div class="relative flex-1">
                                                    <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-xs font-bold text-slate-400 pointer-events-none">Rp</span>
                                                    <input type="number" wire:model.live.debounce.400ms="bahan_baku.{{ $index }}.harga_beli" wire:change="calculateSubtotal({{ $index }})" placeholder="0" class="w-full pl-8 pr-2 py-2 text-sm font-bold border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                                </div>
                                                <span class="text-xs text-slate-400 font-bold">/</span>
                                                <input type="number" step="any" wire:model.live.debounce.400ms="bahan_baku.{{ $index }}.jumlah_beli" wire:change="calculateSubtotal({{ $index }})" placeholder="1" class="w-14 px-2 py-2 text-sm font-bold text-center border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                                <select wire:model.live="bahan_baku.{{ $index }}.satuan_beli" wire:change="calculateSubtotal({{ $index }})" class="w-20 px-2 py-2 text-xs font-medium border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 cursor-pointer">
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
                                            </div>
                                            @if((float)($bahan['harga_beli'] ?? 0) > 0)
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                                    = Rp {{ number_format((float)($bahan['harga_beli'] ?? 0), 0, ',', '.') }} / {{ $bahan['jumlah_beli'] ?? 1 }} {{ $bahan['satuan_beli'] ?? 'kg' }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    {{-- Modal per Porsi --}}
                                    <div class="md:col-span-2 flex md:block items-center justify-between md:text-right pr-0 md:pr-10 pt-2 md:pt-0 border-t md:border-t-0 border-slate-200/60 dark:border-slate-700/60">
                                        <label class="block md:hidden text-xs font-bold text-slate-600 dark:text-slate-300">Modal per Porsi:</label>
                                        <div>
                                            <div class="text-base font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($bahan['subtotal'], 0, ',', '.') }}</div>
                                            <span class="text-[10px] text-slate-400 font-medium hidden md:inline">per porsi</span>
                                        </div>
                                    </div>
                                    
                                    {{-- Tombol Hapus --}}
                                    <button wire:click="removeIngredientRow({{ $index }})" class="absolute right-3 top-3 md:top-1/2 md:-translate-y-1/2 text-slate-400 hover:text-red-500 bg-white dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors p-2 shadow-2xs border border-slate-200 dark:border-slate-700" title="Hapus Bahan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            
                            @if(count($bahan_baku) == 0)
                            <div class="p-8 text-center text-slate-500 dark:text-slate-400 text-sm">
                                Belum ada komponen biaya bahan. Tambahkan secara manual atau gunakan bantuan AI.
                            </div>
                            @endif
                            
                            <div class="p-4 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700">
                                <button wire:click="addIngredientRow" class="w-full py-3 border-2 border-dashed border-emerald-300 dark:border-emerald-700/50 rounded-xl text-emerald-600 dark:text-emerald-400 font-bold hover:bg-emerald-50 dark:hover:bg-emerald-900/20 hover:border-emerald-400 transition-colors flex justify-center items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    Tambah Bahan Baku
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: HASIL PERHITUNGAN & ANALISIS SENSITIVITAS --}}
                @php $calc = $this->hppCalculation; @endphp
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 card-shadow p-5 sm:p-7 lg:p-8 transition-colors">
                    
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                        {{-- Sisi Kiri: Rincian HPP --}}
                        <div class="lg:col-span-5 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-wider">Rincian Modal (HPP) per Porsi</h3>
                            </div>
                            
                            <div class="p-5 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-3.5">
                                {{-- Biaya Variabel (Bahan Baku) --}}
                                <div class="flex justify-between items-center p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700 shadow-2xs">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">Total Biaya Bahan Baku</div>
                                            <div class="text-[11px] text-slate-400">Modal Resep Variabel</div>
                                        </div>
                                    </div>
                                    <div class="text-sm font-black text-slate-900 dark:text-white">
                                        Rp {{ number_format($calc['totalVariable'], 0, ',', '.') }}
                                    </div>
                                </div>

                                {{-- Biaya Tetap (Operasional) --}}
                                <div class="flex justify-between items-center p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700 shadow-2xs">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1">
                                                Biaya Operasional
                                                <span class="group relative cursor-pointer">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-52 p-2.5 bg-slate-900 text-white text-[11px] rounded-xl shadow-xl hidden group-hover:block z-20 text-center font-normal">
                                                        Alokasi listrik, cup kemasan, air & gas per porsi.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="text-[11px] text-slate-400">Estimasi Biaya Operasional</div>
                                        </div>
                                    </div>
                                    <div class="relative w-28">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-xs font-bold text-slate-400 pointer-events-none">Rp</span>
                                        <input type="number" wire:model.live.debounce.400ms="alokasi_biaya_tetap" class="w-full pl-8 pr-2 py-1.5 text-sm font-bold text-right bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                    </div>
                                </div>
                                
                                {{-- Total HPP Banner --}}
                                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-800/60 flex items-center justify-between">
                                    <div>
                                        <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider block">Total HPP per Porsi</span>
                                        <span class="text-[11px] text-emerald-600/80 dark:text-emerald-400/80">Bahan Baku + Operasional</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">Rp {{ number_format($calc['totalHpp'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Sisi Kanan: Analisis Sensitivitas --}}
                        <div class="lg:col-span-7 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-wider">Simulasi Sensitivitas Harga</h3>
                            </div>
                            
                            <div class="p-5 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-3.5">
                                {{-- Slider Control Card --}}
                                <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700 shadow-2xs space-y-3">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs">
                                        <span class="font-bold text-slate-700 dark:text-slate-300">
                                            Simulasi Kenaikan Bahan Baku: <span class="text-emerald-600 dark:text-emerald-400 font-black">+{{ $kenaikan_persen }}%</span>
                                        </span>
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                            Estimasi HPP Baru: <span class="font-black">Rp {{ number_format($calc['simulatedHpp'], 0, ',', '.') }}</span>
                                        </span>
                                    </div>
                                    <input type="range" wire:model.live="kenaikan_persen" min="0" max="100" class="w-full h-2.5 bg-slate-100 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                                    <div class="flex justify-between items-center text-[10px] font-bold text-slate-400 dark:text-slate-500 pt-0.5">
                                        <span>0%</span>
                                        <span>50%</span>
                                        <span>100%</span>
                                    </div>
                                </div>
                                
                                {{-- 4 Metric Cards Grid --}}
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3">
                                    <div class="bg-white dark:bg-slate-800 p-3 sm:p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700 text-center shadow-2xs">
                                        <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">HPP Saat Ini</div>
                                        <div class="text-xs sm:text-base font-black text-slate-900 dark:text-white mt-1">Rp {{ number_format($calc['totalHpp'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="bg-emerald-50/80 dark:bg-emerald-950/40 p-3 sm:p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-800/60 text-center shadow-2xs">
                                        <div class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">HPP Baru</div>
                                        <div class="text-xs sm:text-base font-black text-emerald-700 dark:text-emerald-300 mt-1">Rp {{ number_format($calc['simulatedHpp'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="bg-amber-50/80 dark:bg-amber-950/40 p-3 sm:p-3.5 rounded-xl border border-amber-200 dark:border-amber-800/60 text-center shadow-2xs">
                                        <div class="text-[10px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Harga Jual Min.</div>
                                        <div class="text-xs sm:text-base font-black text-amber-700 dark:text-amber-300 mt-1">Rp {{ number_format($calc['simulatedHpp'] * 1.1, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="bg-blue-50/80 dark:bg-blue-950/40 p-3 sm:p-3.5 rounded-xl border border-blue-200 dark:border-blue-800/60 text-center shadow-2xs">
                                        <div class="text-[10px] font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wider">Margin Baru</div>
                                        <div class="text-xs sm:text-base font-black text-blue-700 dark:text-blue-300 mt-1">
                                            @if($calc['simulatedHpp'] > 0)
                                                {{ number_format(max(0, (($price ?? 0) - $calc['simulatedHpp']) / ($price ?: 1) * 100), 1) }}%
                                            @else
                                                0%
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 3: REKOMENDASI STRATEGI HARGA JUAL AI --}}
                @php $tiers = $this->pricingTiers; @endphp
                @if(!empty($tiers))
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 card-shadow p-5 sm:p-7 lg:p-8 relative overflow-hidden transition-colors" x-data="{ expanded: true }">
                    <div class="flex justify-between items-center cursor-pointer mb-2" @click="expanded = !expanded">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center">
                                Rekomendasi Strategi Harga Jual <span class="ml-2.5 px-2.5 py-1 text-[10px] bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-full font-bold uppercase tracking-wider shadow-xs">✨ Didukung oleh AI</span>
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
                        
                        <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-700">
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
                </div>
                @endif
            </div>
            @endif

        </main>
    </div>

    {{-- MODAL QUICK EDIT HPP & HARGA JUAL --}}
    @if($showEditModal && $editingProduct)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
            <div class="inline-block bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all max-w-md w-full border border-slate-200 dark:border-slate-700 p-6">
                
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
                    <div>
                        <label class="block text-xs font-semibold text-red-600 dark:text-red-400 mb-1">Harga Modal / HPP (Rp):</label>
                        <input type="number" wire:model.live="harga_beli" class="w-full px-3.5 py-2 text-sm font-semibold border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        @error('harga_beli') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Harga Jual Kasir (Rp):</label>
                        <input type="number" wire:model.live="price" class="w-full px-3.5 py-2 text-sm font-semibold border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        @error('price') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- LIVE MARGIN CALCULATOR PREVIEW --}}
                    <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 space-y-1.5">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-600 dark:text-slate-400 font-medium">Estimasi Profit / Porsi:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($calcProfit, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-600 dark:text-slate-400 font-medium">Estimasi Margin Keuntungan:</span>
                            <span class="font-bold {{ $calcMargin >= 50 ? 'text-emerald-600 dark:text-emerald-400' : ($calcMargin >= 35 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">{{ number_format($calcMargin, 1) }}%</span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold bg-slate-900 dark:bg-emerald-600 text-white rounded-xl hover:bg-slate-800 dark:hover:bg-emerald-700 transition-colors shadow-xs">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
