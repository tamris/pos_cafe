<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    @include('livewire.includes.sidebar')

    <div class="lg:pl-64">
        @include('livewire.includes.header', [
            'title' => 'Penjualan Menu Hari Ini',
            'subtitle' => 'Monitor porsi terjual & performa omset Cafe Noli realtime',
        ])

        <main class="p-6">
            
            {{-- SUMMARY KPI CARDS HARI INI --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                {{-- Total Cup/Porsi Terjual --}}
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-full text-orange-600 dark:text-orange-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Terjual Hari Ini</p>
                            <h3 class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($totalCupsToday, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">Porsi</span></h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Realtime hari ini</p>
                        </div>
                    </div>
                </div>

                {{-- Total Omset Kotor --}}
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-full text-emerald-600 dark:text-emerald-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Omset Kotor Hari Ini</p>
                            <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalRevenueToday, 0, ',', '.') }}</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Pendapatan kotor</p>
                        </div>
                    </div>
                </div>

                {{-- Best Seller Hari Ini --}}
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-full text-amber-600 dark:text-amber-400 shrink-0">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Top Best Seller</p>
                            <h3 class="text-base font-bold text-slate-800 dark:text-white truncate" title="{{ $topSellerName }}">{{ $topSellerName }}</h3>
                            <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold mt-1">{{ $topSellerQty }} porsi terjual</p>
                        </div>
                    </div>
                </div>

                {{-- Varian Terjual --}}
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-full text-purple-600 dark:text-purple-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Varian Laku</p>
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $uniqueMenuSoldToday }} <span class="text-xs font-normal text-slate-500">Menu</span></h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Variasi menu terjual</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABLE SECTION --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                
                {{-- Header & Filter --}}
                <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Performa Penjualan Per Menu</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Diurutkan berdasarkan porsi terbanyak yang terjual hari ini</p>
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
                                placeholder="Cari menu..."
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
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">Nama Menu Cafe</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">Kategori</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">Harga Jual</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 text-center">Terjual Hari Ini</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 text-right">Omset Hari Ini</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 text-center">Total All-Time</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($products as $product)
                                @php
                                    $soldToday = $product->sold_today ?? 0;
                                    $revenueToday = $product->revenue_today ?? 0;
                                    $soldAllTime = $product->sold_all_time ?? 0;
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800 dark:text-white">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($soldToday > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                🔥 {{ $soldToday }} Porsi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600">
                                                0 Porsi
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-emerald-600 dark:text-emerald-400 text-right">
                                        Rp {{ number_format($revenueToday, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-slate-600 dark:text-slate-300">
                                        {{ number_format($soldAllTime, 0, ',', '.') }} Porsi
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <button wire:click="openHistoryModal({{ $product->id }})"
                                            class="inline-flex items-center px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-medium transition-colors ml-auto">
                                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            Rincian Pesanan
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

    {{-- MODAL DETAIL TRANSAKSI HARI INI --}}
    @if($showHistoryModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
            <div class="inline-block bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all max-w-2xl w-full border border-slate-200 dark:border-slate-700 p-6">
                
                <div class="flex justify-between items-center pb-4 border-b border-slate-200 dark:border-slate-700 mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Rincian Penjualan Hari Ini</h3>
                        <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold mt-0.5">{{ $selectedProduct->name ?? '' }}</p>
                    </div>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="overflow-y-auto max-h-[400px]">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs uppercase text-slate-500 dark:text-slate-400 font-semibold">
                            <tr>
                                <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">Waktu</th>
                                <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">Invoice</th>
                                <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">Tipe / Info</th>
                                <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 text-center">Jumlah</th>
                                <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">Catatan Barista/Dapur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($itemHistory as $detail)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300 font-mono">
                                        {{ $detail->created_at->format('H:i') }} WIB
                                    </td>
                                    <td class="px-4 py-3 font-mono font-bold text-slate-700 dark:text-slate-300">
                                        {{ $detail->transaction->invoice_number ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-semibold text-slate-900 dark:text-white block capitalize">
                                            {{ str_replace('_', ' ', $detail->transaction->order_type ?? 'dine_in') }}
                                        </span>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                            @if(($detail->transaction->order_type ?? 'dine_in') === 'dine_in')
                                                {{ $detail->transaction->table_number ? 'Meja: '.$detail->transaction->table_number : '-' }}
                                            @else
                                                {{ $detail->transaction->customer_name ? 'Cust: '.$detail->transaction->customer_name : '-' }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-900 dark:text-white">
                                        {{ $detail->quantity }} Qty
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($detail->notes)
                                            <span class="text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 px-2 py-0.5 rounded font-mono text-[11px]">
                                                📝 {{ $detail->notes }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic text-[11px]">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-400 dark:text-slate-500">Belum ada transaksi untuk menu ini hari ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                    <button wire:click="closeModal" class="px-4 py-2 bg-slate-900 dark:bg-blue-600 text-white rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors text-xs font-medium">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>