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

            {{-- TABLE SECTION --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                
                <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Daftar HPP (Harga Modal) & Pricing Menu</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Klik "Quick Edit" untuk mengubah HPP/Harga Modal jika terjadi perubahan harga supplier</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                        
                        {{-- Filter Kategori --}}
                        <select wire:model.live="categoryFilter" 
                            class="px-4 py-2.5 text-xs font-medium border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer w-full sm:w-auto">
                            <option value="">Semua Kategori Cafe</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>

                        {{-- Search --}}
                        <div class="relative w-full sm:w-auto">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                placeholder="Cari menu / SKU..."
                                class="w-full sm:w-64 pl-10 pr-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs">
                            <svg class="w-5 h-5 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Table --}}
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
                                        <button wire:click="openEditModal({{ $product->id }})"
                                            class="inline-flex items-center px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-medium transition-colors ml-auto">
                                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Quick Edit HPP
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
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    {{ $products->links() }}
                </div>
            </div>
        </main>
    </div>

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
