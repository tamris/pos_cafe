<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    @include('livewire.includes.sidebar')

    <div class="lg:pl-64">
        @include('livewire.includes.header', [
            'title' => 'Manajemen HPP & Profit Margin',
            'subtitle' => 'Kelola harga modal (HPP), harga jual, dan pantau persentase profit margin menu Cafe Noli',
        ])

        <main class="p-6">
            
            {{-- KPI SUMMARY CARDS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                {{-- Rata-Rata Profit Margin --}}
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-full text-amber-600 dark:text-amber-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Rata-Rata Margin</p>
                            <h3 class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ number_format($avgMargin, 1) }}%</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Rata-rata persentase margin</p>
                        </div>
                    </div>
                </div>

                {{-- Rata-Rata Profit per Item --}}
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-full text-emerald-600 dark:text-emerald-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Rata-Rata Profit/Item</p>
                            <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($avgProfitPerItem, 0, ',', '.') }}</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Keuntungan per porsi</p>
                        </div>
                    </div>
                </div>

                {{-- Alert Low Margin (< 35%) --}}
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-full text-red-600 dark:text-red-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Margin Tipis (&lt; 35%)</p>
                            <h3 class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $lowMarginCount }} <span class="text-xs font-normal text-slate-500">Menu</span></h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Perlu perhatian</p>
                        </div>
                    </div>
                </div>

                {{-- Total Varian Menu --}}
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-full text-blue-600 dark:text-blue-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Total Menu Active</p>
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">{{ $totalMenuCount }} <span class="text-xs font-normal text-slate-500">Menu</span></h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Total menu aktif</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TOP CONTROL BAR --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors mb-6 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ $showCalculator ? 'Kalkulator & Simulator HPP' : 'Daftar HPP (Harga Modal) & Pricing Menu' }}
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ $showCalculator ? 'Simulasikan harga modal dan dapatkan rekomendasi harga jual dengan AI' : 'Klik "Quick Edit" untuk mengubah HPP/Harga Modal jika terjadi perubahan harga supplier' }}
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <button wire:click="toggleCalculator" class="px-4 py-2 text-sm font-semibold bg-slate-900 dark:bg-emerald-600 text-white rounded-lg hover:bg-slate-800 dark:hover:bg-emerald-700 transition-colors shadow-sm flex items-center">
                        @if($showCalculator)
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Kembali ke Daftar
                        @else
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            Kalkulator & Simulator HPP
                        @endif
                    </button>
                    
                    @if(!$showCalculator)
                    {{-- Filter Kategori --}}
                    <select wire:model.live="categoryFilter" 
                        class="px-4 py-2 text-xs font-medium border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer w-full sm:w-auto">
                        <option value="">Semua Kategori Cafe</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>

                    {{-- Search --}}
                    <div class="relative w-full sm:w-auto">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari menu / SKU..."
                            class="w-full sm:w-64 pl-10 pr-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs">
                        <svg class="w-5 h-5 text-slate-400 dark:text-slate-500 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    @endif
                </div>
            </div>

            @if(!$showCalculator)
            {{-- TABLE SECTION --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors mb-6 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs uppercase text-slate-500 dark:text-slate-400 font-semibold">
                            <tr>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">Menu Cafe</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">Kategori</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 text-red-600 dark:text-red-400">HPP (Harga Modal)</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">Harga Jual</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400">Profit / Item</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 text-center">Profit Margin (%)</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($products as $product)
                                @php
                                    $hpp = (float) $product->harga_beli;
                                    $jual = (float) $product->price;
                                    $profit = max(0, $jual - $hpp);
                                    $margin = $jual > 0 ? ($profit / $jual) * 100 : 0;
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if ($product->image)
                                                    <img class="h-10 w-10 rounded-lg object-cover border border-slate-200 dark:border-slate-700" src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}">
                                                @else
                                                    <div class="h-10 w-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-lg text-slate-500 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                                        ☕
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $product->name }}</div>
                                                <div class="text-xs font-mono text-slate-500 dark:text-slate-400">{{ $product->sku }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                        {{ $product->category->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600 dark:text-red-400">
                                        Rp {{ number_format($hpp, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900 dark:text-white">
                                        Rp {{ number_format($jual, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                        + Rp {{ number_format($profit, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($margin >= 50)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-100 dark:border-green-800">
                                                Sangat Baik ({{ number_format($margin, 1) }}%)
                                            </span>
                                        @elseif($margin >= 35)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                Normal ({{ number_format($margin, 1) }}%)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800">
                                                Tipis ({{ number_format($margin, 1) }}%)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <button wire:click="editProductInCalculator({{ $product->id }})"
                                            class="inline-flex items-center px-3 py-1.5 border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 rounded-lg text-xs font-semibold transition-colors ml-auto shadow-2xs">
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
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded-b-xl">
                    {{ $products->links() }}
                </div>
            </div>
            @else
            {{-- CALCULATOR VIEW (FULL WIDTH SINGLE-COLUMN STACK) --}}
            <div class="w-full space-y-6">
                
                {{-- CARD 1: INPUT DATA PRODUK & KOMPONEN BIAYA --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow p-6 lg:p-8 transition-colors">
                    
                    {{-- Form Header: Pilih Menu & Nama Produk --}}
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-5 mb-6">
                        <div class="md:col-span-5">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2 flex items-center justify-between">
                                <span>Pilih Dari Menu Cafe</span>
                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">Pilih atau Ketik Baru</span>
                            </label>
                            <select wire:model.live="selected_product_id" class="w-full px-4 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow-xs">
                                <option value="">-- ✨ Buat Menu Baru (Ketik Manual) --</option>
                                @foreach($allProducts as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (Harga Jual: Rp {{ number_format($p->price, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="md:col-span-7">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Nama Produk / Menu Cafe</label>
                            <input type="text" wire:model="nama_produk" placeholder="Contoh: Kopi Susu Gula Aren, Croissant Butter, dll" class="w-full px-4 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow-xs font-medium">
                            @error('nama_produk') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Tombol AI CTA --}}
                    <div class="mb-8">
                        <button wire:click="analyzeWithAI" wire:loading.attr="disabled" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl py-3.5 w-full text-base transition-all shadow-sm hover:shadow flex justify-center items-center">
                            <span wire:loading.remove wire:target="analyzeWithAI" class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                Bantu Analisis Resep & HPP dengan AI
                            </span>
                            <span wire:loading wire:target="analyzeWithAI" class="flex items-center">
                                <svg class="animate-spin h-5 w-5 text-white mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> 
                                Menganalisis Komponen Resep Terbaik...
                            </span>
                        </button>
                    </div>

                    {{-- Tabel / List Biaya Variabel --}}
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Komponen Biaya Variabel (Bahan Baku)</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Rincian takaran resep per porsi dan estimasi harga beli distributor</p>
                            </div>
                            <button class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 flex items-center bg-emerald-50 dark:bg-emerald-900/20 px-3.5 py-2 rounded-lg border border-emerald-100 dark:border-emerald-800 transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Export .xlsx
                            </button>
                        </div>
                        
                        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                            <div class="hidden md:grid grid-cols-12 gap-4 p-4 text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900">
                                <div class="col-span-4">Nama Bahan Baku</div>
                                <div class="col-span-3">Takaran Per Porsi</div>
                                <div class="col-span-3">Harga Beli Distributor</div>
                                <div class="col-span-2 text-right pr-10">Subtotal HPP</div>
                            </div>
                            
                            <div class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach($bahan_baku as $index => $bahan)
                                <div class="p-4 grid grid-cols-1 md:grid-cols-12 gap-4 items-center hover:bg-slate-100/50 dark:hover:bg-slate-800/50 transition-colors relative">
                                    {{-- Nama Bahan --}}
                                    <div class="md:col-span-4">
                                        <label class="block md:hidden text-xs font-bold text-slate-500 mb-1">Nama Bahan</label>
                                        <input type="text" wire:model.live="bahan_baku.{{ $index }}.nama" placeholder="Contoh: Biji Kopi Espresso" class="w-full px-3.5 py-2 text-sm font-medium border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                    </div>
                                    
                                    {{-- Takaran Resep --}}
                                    <div class="md:col-span-3">
                                        <label class="block md:hidden text-xs font-bold text-slate-500 mb-1">Takaran Per Porsi</label>
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
                                    
                                    {{-- Harga Beli Distributor --}}
                                    <div class="md:col-span-3">
                                        <label class="block md:hidden text-xs font-bold text-slate-500 mb-1">Harga Beli Distributor</label>
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1.5">
                                                <div class="relative flex-1">
                                                    <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-xs font-bold text-slate-500 pointer-events-none">Rp</span>
                                                    <input type="number" wire:model.live.debounce.400ms="bahan_baku.{{ $index }}.harga_beli" wire:change="calculateSubtotal({{ $index }})" placeholder="0" class="w-full pl-8 pr-2.5 py-2 text-sm font-bold border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
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
                                    
                                    {{-- Subtotal HPP --}}
                                    <div class="md:col-span-2 text-left md:text-right pr-0 md:pr-10">
                                        <label class="block md:hidden text-xs font-bold text-slate-500 mb-1">Subtotal HPP</label>
                                        <div class="text-base font-extrabold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($bahan['subtotal'], 0, ',', '.') }}</div>
                                        <span class="text-[10px] text-slate-400 font-medium">per porsi</span>
                                    </div>
                                    
                                    {{-- Tombol Hapus --}}
                                    <button wire:click="removeIngredientRow({{ $index }})" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 bg-white dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors p-2 shadow-xs border border-slate-200 dark:border-slate-700" title="Hapus Bahan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            
                            @if(count($bahan_baku) == 0)
                            <div class="p-8 text-center text-slate-500 dark:text-slate-400 text-sm">
                                Belum ada komponen biaya. Tambahkan secara manual atau gunakan AI.
                            </div>
                            @endif
                            
                            <div class="p-4 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700">
                                <button wire:click="addIngredientRow" class="w-full py-3 border-2 border-dashed border-emerald-300 dark:border-emerald-700/50 rounded-xl text-emerald-600 dark:text-emerald-400 font-bold hover:bg-emerald-50 dark:hover:bg-emerald-900/20 hover:border-emerald-400 transition-colors flex justify-center items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    Tambah Komponen Bahan Baku
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: HASIL PERHITUNGAN & ANALISIS SENSITIVITAS --}}
                @php $calc = $this->hppCalculation; @endphp
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow p-6 lg:p-8 transition-colors">
                    
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        {{-- Sisi Kiri: Rincian HPP --}}
                        <div class="lg:col-span-5 space-y-4">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-wider">Rincian HPP per Produk</h3>
                            
                            <div class="p-5 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700 space-y-4">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-slate-600 dark:text-slate-400 font-medium">Biaya Variabel per Produk</span>
                                    <span class="font-bold text-slate-900 dark:text-white">Rp {{ number_format($calc['totalVariable'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-slate-600 dark:text-slate-400 font-medium flex items-center">
                                        Alokasi Biaya Tetap
                                        <span class="group relative cursor-pointer ml-1.5">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-xl shadow-xl hidden group-hover:block z-10 text-center">
                                                Asumsi biaya operasional (listrik, air, packaging) per porsi.
                                            </div>
                                        </span>
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <div class="relative w-32">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-xs font-bold text-slate-500 pointer-events-none">Rp</span>
                                            <input type="number" wire:model.live.debounce.400ms="alokasi_biaya_tetap" class="w-full pl-8 pr-2.5 py-1.5 text-sm font-bold text-right bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between items-center pt-3 border-t border-slate-200 dark:border-slate-700">
                                    <span class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide">Total HPP per Produk</span>
                                    <span class="text-2xl lg:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 tracking-tight">Rp {{ number_format($calc['totalHpp'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Sisi Kanan: Analisis Sensitivitas --}}
                        <div class="lg:col-span-7 space-y-4">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-wider">Analisis Sensitivitas Harga</h3>
                            
                            <div class="p-5 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700 space-y-4">
                                <div>
                                    <div class="flex justify-between text-xs sm:text-sm mb-2">
                                        <span class="text-slate-600 dark:text-slate-400 font-medium">Simulasi Kenaikan Harga Bahan Baku</span>
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">Dampak ke HPP: Rp {{ number_format($calc['simulatedHpp'], 0, ',', '.') }} (+{{ $kenaikan_persen }}%)</span>
                                    </div>
                                    <input type="range" wire:model.live="kenaikan_persen" min="0" max="100" class="w-full h-2.5 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                                </div>
                                
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                                    <div class="bg-white dark:bg-slate-800 p-3 rounded-xl border border-slate-200 dark:border-slate-700 text-center shadow-xs">
                                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">HPP Sekarang</div>
                                        <div class="text-sm sm:text-base font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($calc['totalHpp'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="bg-emerald-50 dark:bg-emerald-950/40 p-3 rounded-xl border border-emerald-200 dark:border-emerald-800/60 text-center shadow-xs">
                                        <div class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">HPP Baru (+{{ $kenaikan_persen }}%)</div>
                                        <div class="text-sm sm:text-base font-bold text-emerald-700 dark:text-emerald-300 mt-1">Rp {{ number_format($calc['simulatedHpp'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="bg-amber-50 dark:bg-amber-950/40 p-3 rounded-xl border border-amber-200 dark:border-amber-800/60 text-center shadow-xs">
                                        <div class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Harga Jual Min.</div>
                                        <div class="text-sm sm:text-base font-bold text-amber-700 dark:text-amber-300 mt-1">Rp {{ number_format($calc['simulatedHpp'] * 1.1, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="bg-blue-50 dark:bg-blue-950/40 p-3 rounded-xl border border-blue-200 dark:border-blue-800/60 text-center shadow-xs">
                                        <div class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Margin Skrg</div>
                                        <div class="text-sm sm:text-base font-bold text-blue-700 dark:text-blue-300 mt-1">
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

                {{-- CARD 3: SARAN REKOMENDASI HARGA JUAL AI --}}
                @php $tiers = $this->pricingTiers; @endphp
                @if(!empty($tiers))
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow p-6 lg:p-8 relative overflow-hidden transition-colors" x-data="{ expanded: true }">
                    <div class="flex justify-between items-center cursor-pointer mb-2" @click="expanded = !expanded">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center">
                                Saran Harga Jual <span class="ml-2.5 px-2.5 py-1 text-[10px] bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-full font-bold uppercase tracking-wider shadow-sm">✨ Didukung oleh AI</span>
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Saran untuk menu: <span class="font-bold text-slate-700 dark:text-slate-300">{{ $nama_produk ?: 'Produk Baru' }}</span></p>
                        </div>
                        <button class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 bg-slate-100 dark:bg-slate-700 rounded-xl transition-colors">
                            <svg class="w-5 h-5 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </div>
                    
                    <div x-show="expanded" x-collapse class="mt-6 space-y-4">
                        
                        {{-- Tier Kompetitif --}}
                        <div wire:click="selectTier('kompetitif')" class="w-full flex flex-col md:flex-row justify-between items-start md:items-center p-5 rounded-xl border-2 cursor-pointer transition-all {{ $selected_tier === 'kompetitif' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 shadow-sm ring-1 ring-emerald-500/30' : 'border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/40 hover:border-slate-300 dark:hover:border-slate-600' }}">
                            <div class="flex items-center gap-4 mb-3 md:mb-0">
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 {{ $selected_tier === 'kompetitif' ? 'border-emerald-500 bg-emerald-500' : 'border-slate-300 dark:border-slate-600' }}">
                                    @if($selected_tier === 'kompetitif')
                                        <div class="w-2 h-2 rounded-full bg-white"></div>
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2.5 mb-1">
                                        <span class="bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 rounded-full px-3 py-0.5 text-xs font-bold uppercase tracking-wide">Kompetitif</span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">"Harga bersaing, menarik pelanggan baru dengan margin sehat."</p>
                                </div>
                            </div>
                            <div class="text-left md:text-right w-full md:w-auto pl-9 md:pl-0 space-y-0.5">
                                <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($tiers['kompetitif']['harga'], 0, ',', '.') }}</div>
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                    Profit: <span class="text-emerald-600 dark:text-emerald-400">+Rp {{ number_format($tiers['kompetitif']['profit'], 0, ',', '.') }}</span>
                                </div>
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                    Margin: <span class="text-blue-600 dark:text-blue-400">{{ number_format($tiers['kompetitif']['margin'], 1) }}%</span>
                                </div>
                            </div>
                        </div>

                        {{-- Tier Standar --}}
                        <div wire:click="selectTier('standar')" class="w-full flex flex-col md:flex-row justify-between items-start md:items-center p-5 rounded-xl border-2 cursor-pointer transition-all {{ $selected_tier === 'standar' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 shadow-sm ring-1 ring-emerald-500/30' : 'border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/40 hover:border-slate-300 dark:hover:border-slate-600' }}">
                            <div class="flex items-center gap-4 mb-3 md:mb-0">
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 {{ $selected_tier === 'standar' ? 'border-emerald-500 bg-emerald-500' : 'border-slate-300 dark:border-slate-600' }}">
                                    @if($selected_tier === 'standar')
                                        <div class="w-2 h-2 rounded-full bg-white"></div>
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2.5 mb-1">
                                        <span class="bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded-full px-3 py-0.5 text-xs font-bold uppercase tracking-wide">Standar</span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">"Harga pasar rata-rata, keseimbangan optimal antara profit dan volume."</p>
                                </div>
                            </div>
                            <div class="text-left md:text-right w-full md:w-auto pl-9 md:pl-0 space-y-0.5">
                                <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($tiers['standar']['harga'], 0, ',', '.') }}</div>
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                    Profit: <span class="text-emerald-600 dark:text-emerald-400">+Rp {{ number_format($tiers['standar']['profit'], 0, ',', '.') }}</span>
                                </div>
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                    Margin: <span class="text-emerald-600 dark:text-emerald-400">{{ number_format($tiers['standar']['margin'], 1) }}%</span>
                                </div>
                            </div>
                        </div>

                        {{-- Tier Premium --}}
                        <div wire:click="selectTier('premium')" class="w-full flex flex-col md:flex-row justify-between items-start md:items-center p-5 rounded-xl border-2 cursor-pointer transition-all {{ $selected_tier === 'premium' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 shadow-sm ring-1 ring-emerald-500/30' : 'border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/40 hover:border-slate-300 dark:hover:border-slate-600' }}">
                            <div class="flex items-center gap-4 mb-3 md:mb-0">
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 {{ $selected_tier === 'premium' ? 'border-emerald-500 bg-emerald-500' : 'border-slate-300 dark:border-slate-600' }}">
                                    @if($selected_tier === 'premium')
                                        <div class="w-2 h-2 rounded-full bg-white"></div>
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2.5 mb-1">
                                        <span class="bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 rounded-full px-3 py-0.5 text-xs font-bold uppercase tracking-wide">Premium</span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">"Harga premium untuk kualitas unggul dan pengalaman pelanggan istimewa."</p>
                                </div>
                            </div>
                            <div class="text-left md:text-right w-full md:w-auto pl-9 md:pl-0 space-y-0.5">
                                <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($tiers['premium']['harga'], 0, ',', '.') }}</div>
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                    Profit: <span class="text-emerald-600 dark:text-emerald-400">+Rp {{ number_format($tiers['premium']['profit'], 0, ',', '.') }}</span>
                                </div>
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                    Margin: <span class="text-purple-600 dark:text-purple-400">{{ number_format($tiers['premium']['margin'], 1) }}%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-700">
                            <button wire:click="saveCalculation" class="w-full md:max-w-md mx-auto block py-3.5 px-6 bg-slate-900 hover:bg-slate-800 dark:bg-emerald-600 dark:hover:bg-emerald-700 text-white text-base font-bold rounded-xl shadow-md transition-colors flex justify-center items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Simpan Resep & Tetapkan HPP
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif

        </main>
    {{-- MODAL QUICK EDIT HPP & HARGA JUAL --}}
    @if($showEditModal && $editingProduct)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
            <div class="inline-block bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all max-w-md w-full border border-slate-200 dark:border-slate-700 p-6">
                
                <div class="flex justify-between items-center pb-3 border-b border-slate-200 dark:border-slate-700 mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Quick Edit HPP & Harga</h3>
                        <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold mt-0.5">{{ $editingProduct->name }}</p>
                    </div>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
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
                        <input type="number" wire:model.live="harga_beli" class="w-full px-3 py-2 text-sm font-semibold border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500">
                        @error('harga_beli') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Harga Jual Kasir (Rp):</label>
                        <input type="number" wire:model.live="price" class="w-full px-3 py-2 text-sm font-semibold border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500">
                        @error('price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- LIVE MARGIN CALCULATOR PREVIEW --}}
                    <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg border border-slate-200 dark:border-slate-700 space-y-1">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-600 dark:text-slate-400 font-medium">Estimasi Profit / Item:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($calcProfit, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-600 dark:text-slate-400 font-medium">Estimasi Margin (%):</span>
                            <span class="font-bold {{ $calcMargin >= 50 ? 'text-emerald-600 dark:text-emerald-400' : ($calcMargin >= 35 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">{{ number_format($calcMargin, 1) }}%</span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">Batal</button>
                        <button type="submit" class="px-4 py-2 text-xs font-medium bg-slate-900 dark:bg-blue-600 text-white rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors shadow-sm">Simpan Perubahan HPP</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
