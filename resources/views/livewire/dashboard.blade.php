<div class="min-h-screen bg-slate-50 dark:bg-slate-950" 
     x-data="{ sidebarOpen: window.innerWidth >= 1280 }"
     @resize.window="sidebarOpen = window.innerWidth >= 1280">

    @include('livewire.includes.sidebar')

    <div class="xl:pl-64 transition-all duration-300 flex flex-col min-h-screen">
        
        @include('livewire.includes.header', [
            'title' => 'Dashboard', 
            'subtitle' => 'Ringkasan performa toko hari ini'
        ])

        <main class="p-4 sm:p-6 space-y-6 flex-1">
            
            {{-- ROW 1: STATS CARDS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
                
                {{-- Card 1: Terjual Hari Ini --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Terjual Hari Ini</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-purple-50 dark:bg-purple-900/30 rounded-lg text-purple-600 dark:text-purple-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2 mb-3">
                            <h3 class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white leading-none">
                                {{ number_format($todayItemsSold, 0, ',', '.') }}
                            </h3>
                            <span class="text-xs font-medium text-slate-400 dark:text-slate-500">Item</span>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        @if ($itemsSoldGrowth > 0)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-semibold gap-1 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                +{{ $itemsSoldGrowth }}%
                            </span>
                        @elseif ($itemsSoldGrowth < 0)
                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-semibold gap-1 bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ $itemsSoldGrowth }}%
                            </span>
                        @else
                            <span class="inline-flex items-center text-slate-500 font-medium bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-md">
                                0%
                            </span>
                        @endif
                        <span class="text-slate-400 dark:text-slate-500 ml-2">vs kemarin</span>
                    </div>
                </div>

                {{-- Card 2: Transaksi Hari Ini --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Transaksi Hari Ini</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2 mb-3">
                            <h3 class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white leading-none">
                                {{ number_format($todayTransactions, 0, ',', '.') }}
                            </h3>
                            <span class="text-xs font-medium text-slate-400 dark:text-slate-500">Trx</span>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        @if ($transactionsGrowth > 0)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-semibold gap-1 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                +{{ $transactionsGrowth }}%
                            </span>
                        @elseif ($transactionsGrowth < 0)
                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-semibold gap-1 bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ $transactionsGrowth }}%
                            </span>
                        @else
                            <span class="inline-flex items-center text-slate-500 font-medium bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-md">
                                0%
                            </span>
                        @endif
                        <span class="text-slate-400 dark:text-slate-500 ml-2">vs kemarin</span>
                    </div>
                </div>

                {{-- Card 3: Pendapatan Hari Ini --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pendapatan</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-amber-50 dark:bg-amber-900/30 rounded-lg text-amber-600 dark:text-amber-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-bold tracking-tight text-slate-800 dark:text-white leading-tight break-words">
                                Rp {{ number_format($todayRevenue, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        @if ($revenueGrowth > 0)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-semibold gap-1 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                +{{ $revenueGrowth }}%
                            </span>
                        @elseif ($revenueGrowth < 0)
                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-semibold gap-1 bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ $revenueGrowth }}%
                            </span>
                        @else
                            <span class="inline-flex items-center text-slate-500 font-medium bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-md">
                                0%
                            </span>
                        @endif
                        <span class="text-slate-400 dark:text-slate-500 ml-2">vs kemarin</span>
                    </div>
                </div>

                {{-- Card 4: Profit Bersih --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute right-0 top-0 h-full w-12 bg-gradient-to-l from-emerald-50/60 dark:from-emerald-900/10 to-transparent pointer-events-none"></div>
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3 relative z-10">
                            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Profit Bersih</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg text-emerald-600 dark:text-emerald-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3 relative z-10">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400 leading-tight break-words">
                                Rp {{ number_format($todayProfit, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60 relative z-10">
                        @if ($profitGrowth > 0)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-semibold gap-1 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                +{{ $profitGrowth }}%
                            </span>
                        @elseif ($profitGrowth < 0)
                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-semibold gap-1 bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ $profitGrowth }}%
                            </span>
                        @else
                            <span class="inline-flex items-center text-slate-500 font-medium bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-md">
                                0%
                            </span>
                        @endif
                        <span class="text-slate-400 dark:text-slate-500 ml-2">vs kemarin</span>
                    </div>
                </div>

            </div>

            {{-- ROW 2: CHARTS --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">
                
                {{-- Tren Penjualan --}}
                <div class="xl:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-4 sm:p-6 flex flex-col justify-between">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Tren Penjualan</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Statistik omset 7 hari terakhir</p>
                        </div>
                        <div class="flex items-center gap-1.5 px-3 py-1 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full text-xs font-semibold w-fit">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Data Terkini
                        </div>
                    </div>
                    
                    {{-- Wrapper tanpa wire:ignore untuk membawa data terbaru --}}
                    <div id="salesChartWrapper" 
                         data-labels='@json($chartLabels)' 
                         data-values='@json($chartData)' 
                         class="w-full h-[280px] sm:h-[320px]">
                        <div wire:ignore id="salesChart" class="w-full h-full"></div>
                    </div>
                </div>
                
                {{-- Metode Pembayaran --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-4 sm:p-6 flex flex-col justify-between">
                    <div class="mb-4">
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Metode Pembayaran</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Proporsi transaksi hari ini</p>
                    </div>

                    {{-- Wrapper tanpa wire:ignore untuk membawa data terbaru --}}
                    <div id="paymentChartWrapper" 
                         data-stats='@json($paymentMethodStats)' 
                         class="w-full flex justify-center h-[280px] sm:h-[320px] items-center">
                        <div wire:ignore id="paymentChart" class="w-full flex justify-center h-full items-center"></div>
                    </div>
                </div>
            </div>

            {{-- ROW 3: RECENT TRANSACTIONS & TOP PRODUCTS --}}
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6">
                
                {{-- Transaksi Terbaru --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm overflow-hidden flex flex-col">
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800 flex justify-between items-center">
                        <h2 class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">Transaksi Terbaru</h2>
                        <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">Lihat Semua</a>
                    </div>
                    <div class="p-4 flex-1">
                        @if ($recentTransactions && $recentTransactions->count() > 0)
                            <div class="space-y-3">
                                @foreach ($recentTransactions as $transaction)
                                    <div class="flex items-center justify-between p-3 hover:bg-slate-50 dark:hover:bg-slate-700/40 rounded-lg border border-slate-100 dark:border-slate-700/60 transition-colors gap-3">
                                        <div class="flex items-center gap-3 min-w-0 flex-1">
                                            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-300 shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm truncate">{{ $transaction->invoice_number }}</p>
                                                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                                    {{ $transaction->created_at->diffForHumans() }} • {{ $transaction->user->name }} •
                                                    <span class="font-medium">
                                                        {{ ($transaction->order_type ?? 'dine_in') === 'dine_in' ? 'Makan di Tempat' : (($transaction->order_type ?? '') === 'take_away' ? 'Bawa Pulang' : 'Pesan Antar') }}
                                                    </span>
                                                    ({{ ($transaction->order_type ?? 'dine_in') === 'dine_in' ? ($transaction->table_number ? 'Meja '.$transaction->table_number : '-') : ($transaction->customer_name ?: '-') }})
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>
                                            @if($transaction->payment_method == 'cash')
                                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 mt-1">
                                                    Tunai
                                                </span>
                                            @elseif($transaction->payment_method == 'transfer')
                                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50 mt-1">
                                                    Transfer
                                                </span>
                                            @else
                                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/50 mt-1">
                                                    QRIS
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-10 text-slate-400 text-xs sm:text-sm">
                                Belum ada transaksi hari ini
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Produk Terlaris --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm overflow-hidden flex flex-col">
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800">
                        <h2 class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">Produk Terlaris</h2>
                    </div>
                    <div class="p-4 flex-1">
                        @if ($topProducts && $topProducts->count() > 0)
                            <div class="space-y-3">
                                @foreach ($topProducts as $index => $product)
                                    <div class="flex items-center space-x-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-700/40 rounded-lg border border-slate-100 dark:border-slate-700/60 transition-colors">
                                        <div class="flex items-center justify-center w-8 h-8 {{ $index == 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400' : ($index == 1 ? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300' : ($index == 2 ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400')) }} rounded-lg text-xs font-bold shrink-0">
                                            {{ $index + 1 }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm truncate">{{ $product->name }}</p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ $product->category->name ?? '-' }}</p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">{{ $product->total_sold }}</p>
                                            <p class="text-[10px] text-slate-400 uppercase tracking-tight">Terjual</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-10 text-slate-400 text-xs sm:text-sm">
                                Belum ada data penjualan
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

{{-- SCRIPT GRAFIK APEXCHARTS --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    let salesChartInstance = null;
    let paymentChartInstance = null;

    function getChartThemeOptions(isDark) {
        return {
            textColor: isDark ? '#94a3b8' : '#64748b',
            gridColor: isDark ? '#334155' : '#e2e8f0',
            strokeColor: isDark ? '#1e293b' : '#ffffff',
            totalTextColor: isDark ? '#f8fafc' : '#0f172a'
        };
    }

    function destroySalesChart() {
        if (salesChartInstance) {
            try { salesChartInstance.destroy(); } catch (e) {}
            salesChartInstance = null;
        }
    }

    function destroyPaymentChart() {
        if (paymentChartInstance) {
            try { paymentChartInstance.destroy(); } catch (e) {}
            paymentChartInstance = null;
        }
    }

    function destroyDashboardCharts() {
        destroySalesChart();
        destroyPaymentChart();
    }

    function initDashboardCharts() {
        const salesWrapper = document.querySelector("#salesChartWrapper");
        const salesElement = document.querySelector("#salesChart");
        const paymentWrapper = document.querySelector("#paymentChartWrapper");
        const paymentElement = document.querySelector("#paymentChart");

        if (!salesElement && !paymentElement) return;

        const isDark = document.documentElement.classList.contains('dark');
        const theme = getChartThemeOptions(isDark);

        // 1. BAR CHART: TREN PENJUALAN
        if (salesElement && salesWrapper) {
            let labels = [];
            let data = [];
            try {
                labels = JSON.parse(salesWrapper.getAttribute('data-labels') || '[]');
                data = JSON.parse(salesWrapper.getAttribute('data-values') || '[]');
            } catch (e) {}

            const salesOptions = {
                series: [{ name: 'Pendapatan', data: data }],
                chart: {
                    type: 'bar',
                    height: window.innerWidth < 768 ? 240 : 300,
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    background: 'transparent',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 400,
                        animateGradually: {
                            enabled: true,
                            delay: 40
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 250
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '38%',
                    }
                },
                colors: ['#3b82f6'],
                dataLabels: { enabled: false },
                xaxis: {
                    categories: labels,
                    labels: { style: { colors: theme.textColor, fontSize: '11px', fontWeight: 500 } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    min: 0,
                    tickAmount: 5,
                    forceNiceScale: true,
                    labels: {
                        formatter: function (value) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(value / 1000) + "k";
                        },
                        style: { colors: theme.textColor, fontSize: '11px', fontWeight: 500 }
                    }
                },
                grid: {
                    borderColor: theme.gridColor,
                    strokeDashArray: 4,
                    yaxis: { lines: { show: true } },
                    xaxis: { lines: { show: false } }
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function (val) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(val);
                        }
                    }
                }
            };

            if (salesChartInstance) {
                salesChartInstance.updateOptions(salesOptions, true, true);
            } else {
                salesElement.innerHTML = "";
                salesChartInstance = new ApexCharts(salesElement, salesOptions);
                salesChartInstance.render();
            }
        }

        // 2. DONUT / PIE CHART: METODE PEMBAYARAN
        if (paymentElement && paymentWrapper) {
            let paymentData = [];
            try {
                paymentData = JSON.parse(paymentWrapper.getAttribute('data-stats') || '[]');
            } catch (e) {}

            if (paymentData && paymentData.length > 0) {
                const series = paymentData.map(item => Number(item.count));
                const labels = paymentData.map(item => item.payment_method.charAt(0).toUpperCase() + item.payment_method.slice(1));

                const donutOptions = {
                    series: series,
                    labels: labels,
                    chart: {
                        type: 'donut',
                        height: 280,
                        fontFamily: 'inherit',
                        background: 'transparent',
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 350,
                            dynamicAnimation: {
                                enabled: true,
                                speed: 200
                            }
                        }
                    },
                    colors: ['#3b82f6', '#10b981', '#6366f1', '#f59e0b', '#8b5cf6'],
                    plotOptions: {
                        pie: {
                            expandOnClick: false,
                            donut: {
                                size: '72%',
                                labels: {
                                    show: true,
                                    name: { show: true, fontSize: '12px', color: theme.textColor },
                                    value: { 
                                        show: true, 
                                        fontSize: '20px', 
                                        fontWeight: '800', 
                                        color: theme.totalTextColor 
                                    },
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        color: theme.textColor,
                                        fontSize: '12px',
                                        formatter: function (w) {
                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: { enabled: false },
                    stroke: { show: true, colors: [theme.strokeColor], width: 2 },
                    legend: { 
                        position: 'bottom', 
                        fontSize: '12px',
                        labels: { colors: theme.textColor }
                    },
                    tooltip: {
                        theme: isDark ? 'dark' : 'light'
                    }
                };

                if (paymentChartInstance) {
                    paymentChartInstance.updateOptions(donutOptions, true, true);
                } else {
                    paymentElement.innerHTML = "";
                    paymentChartInstance = new ApexCharts(paymentElement, donutOptions);
                    paymentChartInstance.render();
                }
            } else {
                destroyPaymentChart();
                paymentElement.innerHTML = '<div class="flex items-center justify-center h-full text-slate-400 text-xs sm:text-sm">Belum ada transaksi hari ini</div>';
            }
        }
    }

    // UPDATE WARNA TEMA DENGAN FORMATTER & SKALA TETAP TERKUNCI (5000k)
    function updateChartsThemeSmoothly() {
        const isDark = document.documentElement.classList.contains('dark');
        const theme = getChartThemeOptions(isDark);

        if (salesChartInstance) {
            salesChartInstance.updateOptions({
                tooltip: { 
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function (val) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(val);
                        }
                    }
                },
                xaxis: { 
                    labels: { style: { colors: theme.textColor, fontSize: '11px', fontWeight: 500 } } 
                },
                yaxis: { 
                    min: 0,
                    tickAmount: 5,
                    forceNiceScale: true,
                    labels: { 
                        formatter: function (value) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(value / 1000) + "k";
                        },
                        style: { colors: theme.textColor, fontSize: '11px', fontWeight: 500 } 
                    } 
                },
                grid: { borderColor: theme.gridColor }
            }, false, false);
        }

        if (paymentChartInstance) {
            paymentChartInstance.updateOptions({
                tooltip: { theme: isDark ? 'dark' : 'light' },
                stroke: { colors: [theme.strokeColor] },
                legend: { labels: { colors: theme.textColor } },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                name: { color: theme.textColor },
                                value: { color: theme.totalTextColor },
                                total: { color: theme.textColor }
                            }
                        }
                    }
                }
            }, false, false);
        }
    }

    function queueChartRender() {
        setTimeout(() => {
            initDashboardCharts();
        }, 30);
    }

    document.addEventListener('livewire:navigated', queueChartRender);
    document.addEventListener('DOMContentLoaded', queueChartRender);
    document.addEventListener('livewire:navigating', destroyDashboardCharts);

    if (window.Livewire) {
        Livewire.hook('morph.updated', () => {
            queueChartRender();
        });
    }

    if (!window.dashboardThemeObserverRegistered) {
        let themeTimeout = null;
        const themeObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class') {
                    clearTimeout(themeTimeout);
                    themeTimeout = setTimeout(() => {
                        updateChartsThemeSmoothly();
                    }, 10);
                }
            });
        });
        themeObserver.observe(document.documentElement, { attributes: true });
        window.dashboardThemeObserverRegistered = true;
    }
</script>