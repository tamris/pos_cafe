<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    @include('livewire.includes.sidebar')

    <div class="main-content-layout flex flex-col min-h-screen">
        @include('livewire.includes.header', [
            'title' => 'Performa Penjualan Menu',
            'subtitle' => 'Monitor porsi terjual & performa omset cafe',
        ])

        <main class="p-4 sm:p-6 space-y-6 flex-1">
            
            {{-- SUMMARY KPI CARDS HARI INI --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
                
                {{-- Card 1: Total Cup/Porsi Terjual --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Terjual {{ $periodLabel }}</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-orange-50 dark:bg-orange-900/30 rounded-lg text-orange-600 dark:text-orange-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-bold tracking-tight text-orange-600 dark:text-orange-400 leading-tight truncate">
                                {{ number_format($totalCupsToday, 0, ',', '.') }} <span class="text-xs font-normal text-slate-400 dark:text-slate-500">Porsi</span>
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        @if ($cupsGrowth > 0)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-semibold gap-1 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                +{{ $cupsGrowth }}%
                            </span>
                        @elseif ($cupsGrowth < 0)
                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-semibold gap-1 bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ $cupsGrowth }}%
                            </span>
                        @else
                            <span class="inline-flex items-center text-slate-500 font-medium bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-md">
                                0%
                            </span>
                        @endif
                        <span class="text-slate-400 dark:text-slate-500 ml-2">vs sebelumnya</span>
                    </div>
                </div>

                {{-- Card 3: Best Seller Hari Ini --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Top Best Seller</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-amber-50 dark:bg-amber-900/30 rounded-lg text-amber-600 dark:text-amber-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-base sm:text-lg 2xl:text-xl font-bold tracking-tight text-slate-800 dark:text-white leading-tight truncate" title="{{ $topSellerName }}">
                                {{ $topSellerName }}
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        <span class="inline-flex items-center text-amber-700 dark:text-amber-300 font-semibold gap-1 bg-amber-50 dark:bg-amber-900/30 px-2 py-0.5 rounded-md">
                            🔥 {{ $topSellerQty }} Porsi
                        </span>
                        <span class="text-slate-400 dark:text-slate-500 ml-2">Ranking 1 {{ $periodLabel }}</span>
                    </div>
                </div>

                {{-- Card 4: Varian Terjual --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Varian Laku</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-purple-50 dark:bg-purple-900/30 rounded-lg text-purple-600 dark:text-purple-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-bold tracking-tight text-slate-800 dark:text-white leading-tight truncate">
                                {{ $uniqueMenuSoldToday }} <span class="text-xs font-normal text-slate-400 dark:text-slate-500">Menu</span>
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        @if ($uniqueMenuGrowth > 0)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-semibold gap-1 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                +{{ $uniqueMenuGrowth }}%
                            </span>
                        @elseif ($uniqueMenuGrowth < 0)
                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-semibold gap-1 bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ $uniqueMenuGrowth }}%
                            </span>
                        @else
                            <span class="inline-flex items-center text-slate-500 font-medium bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-md">
                                0%
                            </span>
                        @endif
                        <span class="text-slate-400 dark:text-slate-500 ml-2">vs sebelumnya</span>
                    </div>
                </div>

                {{-- Card 4: Kategori Favorit --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kategori Favorit</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-base sm:text-lg 2xl:text-xl font-bold tracking-tight text-slate-800 dark:text-white leading-tight truncate" title="{{ $topCategoryName }}">
                                {{ $topCategoryName }}
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        @if ($categoryGrowth > 0)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-semibold gap-1 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                +{{ $categoryGrowth }}%
                            </span>
                        @elseif ($categoryGrowth < 0)
                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-semibold gap-1 bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ $categoryGrowth }}%
                            </span>
                        @else
                            <span class="inline-flex items-center text-slate-500 font-medium bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-md">
                                0%
                            </span>
                        @endif
                        <span class="text-slate-400 dark:text-slate-500 ml-2">({{ $topCategoryQty }} porsi)</span>
                    </div>
                </div>
            </div>

            {{-- TABLE SECTION --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors overflow-hidden">
                
                {{-- Header & Filter --}}
                <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Performa Penjualan Per Menu</h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Diurutkan berdasarkan porsi terbanyak yang terjual</p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-4">
                        {{-- Filter Periode --}}
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 group-hover:text-emerald-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <select wire:model.live="filterPeriod" class="appearance-none w-full sm:w-44 pl-10 pr-10 py-2 sm:py-2.5 text-xs sm:text-sm font-medium border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer transition-all outline-none">
                                <option value="today">Hari Ini</option>
                                <option value="yesterday">Kemarin</option>
                                <option value="this_week">Minggu Ini</option>
                                <option value="this_month">Bulan Ini</option>
                                <option value="last_month">Bulan Lalu</option>
                                <option value="all_time">Semua Waktu</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"></path></svg>
                            </div>
                        </div>

                        {{-- Filter Kategori --}}
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 group-hover:text-emerald-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </div>
                            <select wire:model.live="categoryFilter" class="appearance-none w-full sm:w-56 pl-10 pr-10 py-2 sm:py-2.5 text-xs sm:text-sm font-medium border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer transition-all outline-none">
                                <option value="">Semua Kategori Cafe</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Table Area --}}
                <div class="overflow-x-auto scrollbar-thin">
                    <table class="w-full text-left border-collapse min-w-[760px]">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-[11px] uppercase text-slate-500 dark:text-slate-400 font-semibold">
                            <tr>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700">Nama Menu Cafe</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700">Kategori</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700">Harga Jual</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700 text-center">Terjual {{ $periodLabel }}</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700 text-right">Omset {{ $periodLabel }}</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700 text-center">Total All-Time</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-xs sm:text-sm">
                            @forelse($products as $product)
                                @php
                                    $soldToday = $product->sold_period ?? 0;
                                    $revenueToday = $product->revenue_period ?? 0;
                                    $soldAllTime = $product->sold_all_time ?? 0;
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="shrink-0 h-10 w-10">
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
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-slate-600 dark:text-slate-300">
                                        {{ $product->category->name ?? '-' }}
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap font-bold text-slate-800 dark:text-white">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-center">
                                        @if($soldToday > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                🔥 {{ $soldToday }} Porsi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600">
                                                0 Porsi
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap font-bold text-emerald-600 dark:text-emerald-400 text-right">
                                        Rp {{ number_format($revenueToday, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-center font-medium text-slate-600 dark:text-slate-300">
                                        {{ number_format($soldAllTime, 0, ',', '.') }} Porsi
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-right">
                                        <button wire:click="openHistoryModal({{ $product->id }})"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-medium transition-colors ml-auto active:scale-95">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            Rincian
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

                {{-- Pagination Area --}}
                <div class="px-5 sm:px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    {{ $products->links() }}
                </div>
            </div>
        </main>
    </div>

    {{-- MODAL DETAIL TRANSAKSI HARI INI --}}
    @if($showHistoryModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closeModal"></div>
            
            <div class="inline-block bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all max-w-2xl w-full border border-slate-200 dark:border-slate-700 p-4 sm:p-6 my-8">
                
                {{-- Modal Header --}}
                <div class="flex justify-between items-center pb-4 border-b border-slate-200 dark:border-slate-700 mb-4">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Rincian Penjualan {{ $periodLabel }}</h3>
                        <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold mt-0.5">{{ $selectedProduct->name ?? '' }}</p>
                    </div>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                {{-- Modal Table --}}
                <div class="overflow-y-auto max-h-[380px] scrollbar-thin border rounded-lg border-slate-200 dark:border-slate-700">
                    <table class="w-full text-xs text-left border-collapse min-w-[500px]">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-[11px] uppercase text-slate-500 dark:text-slate-400 font-semibold sticky top-0">
                            <tr>
                                <th class="px-3.5 py-2.5 border-b border-slate-200 dark:border-slate-700">Waktu</th>
                                <th class="px-3.5 py-2.5 border-b border-slate-200 dark:border-slate-700">Invoice</th>
                                <th class="px-3.5 py-2.5 border-b border-slate-200 dark:border-slate-700">Tipe / Info</th>
                                <th class="px-3.5 py-2.5 border-b border-slate-200 dark:border-slate-700 text-center">Jumlah</th>
                                <th class="px-3.5 py-2.5 border-b border-slate-200 dark:border-slate-700">Catatan Barista/Dapur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($itemHistory as $detail)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                                    <td class="px-3.5 py-2.5 text-slate-600 dark:text-slate-300 font-mono">
                                        {{ $detail->created_at->format('H:i') }} WIB
                                    </td>
                                    <td class="px-3.5 py-2.5 font-mono font-bold text-slate-700 dark:text-slate-300">
                                        {{ $detail->transaction->invoice_number ?? '-' }}
                                    </td>
                                    <td class="px-3.5 py-2.5">
                                        <span class="font-semibold text-slate-900 dark:text-white block text-xs">
                                            {{ ($detail->transaction->order_type ?? 'dine_in') === 'dine_in' ? 'Makan di Tempat' : (($detail->transaction->order_type ?? '') === 'take_away' ? 'Bawa Pulang' : 'Pesan Antar') }}
                                        </span>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                            @if(($detail->transaction->order_type ?? 'dine_in') === 'dine_in')
                                                {{ $detail->transaction->table_number ? 'Meja: '.$detail->transaction->table_number : '-' }}
                                            @else
                                                {{ $detail->transaction->customer_name ? 'Pelanggan: '.$detail->transaction->customer_name : '-' }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-3.5 py-2.5 text-center font-bold text-slate-900 dark:text-white">
                                        {{ $detail->quantity }} Porsi
                                    </td>
                                    <td class="px-3.5 py-2.5">
                                        @if($detail->notes)
                                            <span class="text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 px-2 py-0.5 rounded font-mono text-[10px]">
                                                📝 {{ $detail->notes }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic text-[11px]">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-400 dark:text-slate-500">
                                        Belum ada transaksi untuk menu ini pada periode ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Modal Footer --}}
                <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                    <button wire:click="closeModal" class="px-4 py-2 bg-slate-900 dark:bg-blue-600 text-white rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors text-xs font-semibold shadow-sm active:scale-95">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>