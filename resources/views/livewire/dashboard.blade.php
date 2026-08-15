<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    @include('livewire.includes.sidebar')

    <div class="lg:pl-64 transition-all duration-300">
        
        @include('livewire.includes.header', [
            'title' => 'Dashboard', 
            'subtitle' => 'Ringkasan performa toko hari ini'
        ])

        <main class="p-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                
                <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-100 dark:border-slate-700 card-shadow transition-all">
                    <div class="flex items-center space-x-4 mb-3">
                        <div class="flex items-center justify-center w-14 h-14 bg-purple-50 dark:bg-purple-900/30 rounded-full text-purple-600 dark:text-purple-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-white mb-1">{{ number_format($todayItemsSold, 0, ',', '.') }} <span class="text-xs font-normal text-slate-400 dark:text-slate-500">Item</span></h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Terjual Hari Ini</p>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        @if ($itemsSoldGrowth > 0)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-bold gap-1 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                +{{ $itemsSoldGrowth }}%
                            </span>
                        @elseif ($itemsSoldGrowth < 0)
                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-bold gap-1 bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ $itemsSoldGrowth }}%
                            </span>
                        @else
                            <span class="inline-flex items-center text-slate-500 font-medium gap-1 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full">
                                0%
                            </span>
                        @endif
                        <span class="text-slate-400 dark:text-slate-500 ml-2">vs kemarin</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-100 dark:border-slate-700 card-shadow transition-all">
                    <div class="flex items-center space-x-4 mb-3">
                        <div class="flex items-center justify-center w-14 h-14 bg-blue-50 dark:bg-blue-900/30 rounded-full text-blue-600 dark:text-blue-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-white mb-1">{{ $todayTransactions }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Transaksi Hari Ini</p>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        @if ($transactionsGrowth > 0)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-bold gap-1 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                +{{ $transactionsGrowth }}%
                            </span>
                        @elseif ($transactionsGrowth < 0)
                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-bold gap-1 bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ $transactionsGrowth }}%
                            </span>
                        @else
                            <span class="inline-flex items-center text-slate-500 font-medium gap-1 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full">
                                0%
                            </span>
                        @endif
                        <span class="text-slate-400 dark:text-slate-500 ml-2">vs kemarin</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-100 dark:border-slate-700 card-shadow transition-all">
                    <div class="flex items-center space-x-4 mb-3">
                        <div class="flex items-center justify-center w-14 h-14 bg-orange-50 dark:bg-orange-900/30 rounded-full text-orange-600 dark:text-orange-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-white mb-1">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Pendapatan Hari Ini</p>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        @if ($revenueGrowth > 0)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-bold gap-1 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                +{{ $revenueGrowth }}%
                            </span>
                        @elseif ($revenueGrowth < 0)
                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-bold gap-1 bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ $revenueGrowth }}%
                            </span>
                        @else
                            <span class="inline-flex items-center text-slate-500 font-medium gap-1 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full">
                                0%
                            </span>
                        @endif
                        <span class="text-slate-400 dark:text-slate-500 ml-2">vs kemarin</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-100 dark:border-slate-700 card-shadow transition-all relative overflow-hidden">
                    <div class="absolute right-0 top-0 h-full w-16 bg-gradient-to-l from-green-50 dark:from-emerald-900/20 to-transparent opacity-50"></div>
                    <div class="flex items-center space-x-4 relative z-10 mb-3">
                        <div class="flex items-center justify-center w-14 h-14 bg-emerald-100 dark:bg-emerald-900/30 rounded-full text-emerald-600 dark:text-emerald-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-emerald-700 dark:text-emerald-400 mb-1">Rp {{ number_format($todayProfit, 0, ',', '.') }}</h3>
                            <p class="text-sm text-emerald-600 dark:text-emerald-500 font-medium">Profit Bersih Hari Ini</p>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60 relative z-10">
                        @if ($profitGrowth > 0)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-bold gap-1 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                +{{ $profitGrowth }}%
                            </span>
                        @elseif ($profitGrowth < 0)
                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-bold gap-1 bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ $profitGrowth }}%
                            </span>
                        @else
                            <span class="inline-flex items-center text-slate-500 font-medium gap-1 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full">
                                0%
                            </span>
                        @endif
                        <span class="text-slate-400 dark:text-slate-500 ml-2">vs kemarin</span>
                    </div>
                </div>
            </div>



            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Tren Penjualan</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Statistik omset 7 hari terakhir</p>
                        </div>
                        <div class="flex items-center gap-2 px-3 py-1 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-xs font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            Data Terkini
                        </div>
                    </div>
                    <div id="salesChart" class="w-full h-[300px]"></div>
                </div>
                
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow p-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Metode Pembayaran</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Proporsi transaksi hari ini</p>
                    </div>
                    <div id="paymentChart" class="w-full flex justify-center h-[300px] items-center"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                        <h2 class="font-bold text-slate-900 dark:text-white">Transaksi Terbaru</h2>
                        <a href="{{ route('transactions.index') }}" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">Lihat Semua</a>
                    </div>
                    <div class="p-4">
                        @if ($recentTransactions->count() > 0)
                            <div class="space-y-3">
                                @foreach ($recentTransactions as $transaction)
                                    <div class="flex items-center justify-between p-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-lg border border-slate-100 dark:border-slate-700 transition-colors group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-300 group-hover:bg-white dark:group-hover:bg-slate-600 transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 dark:text-white text-sm">{{ $transaction->invoice_number }}</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{ $transaction->created_at->diffForHumans() }} • {{ $transaction->user->name }} •
                                                    <span class="font-medium">
                                                        {{ ($transaction->order_type ?? 'dine_in') === 'dine_in' ? 'Makan di Tempat' : (($transaction->order_type ?? '') === 'take_away' ? 'Bawa Pulang' : 'Pesan Antar') }}
                                                    </span>
                                                    ({{ ($transaction->order_type ?? 'dine_in') === 'dine_in' ? ($transaction->table_number ? 'Meja '.$transaction->table_number : '-') : ($transaction->customer_name ?: '-') }})
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-slate-900 dark:text-white text-sm">Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>
                                            @if($transaction->payment_method == 'cash')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                                Tunai
                                            </span>
                                            @elseif($transaction->payment_method == 'transfer')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-100 dark:border-green-800">
                                                    Transfer
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800">
                                                    QRIS
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-10">
                                <p class="text-slate-400 text-sm">Belum ada transaksi hari ini</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <h2 class="font-bold text-slate-900 dark:text-white">Produk Terlaris</h2>
                    </div>
                    <div class="p-4">
                        @if ($topProducts->count() > 0)
                            <div class="space-y-3">
                                @foreach ($topProducts as $index => $product)
                                    <div class="flex items-center space-x-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-lg border border-slate-100 dark:border-slate-700 transition-colors">
                                        <div class="flex items-center justify-center w-8 h-8 {{ $index == 0 ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-400' : ($index == 1 ? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300' : ($index == 2 ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400')) }} rounded-full text-xs font-bold">
                                            {{ $index + 1 }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-bold text-slate-900 dark:text-white text-sm truncate">{{ $product->name }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $product->category->name }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-slate-900 dark:text-white">{{ $product->total_sold }}</p>
                                            <p class="text-[10px] text-slate-400 uppercase">Terjual</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-10">
                                <p class="text-slate-400 text-sm">Belum ada data penjualan</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

{{-- SCRIPT GRAFIK (SUDAH SUPPORT DARK MODE OTOMATIS DARI APEXCHARTS CONFIG) --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    function loadSalesChart() {
        var chartElement = document.querySelector("#salesChart");
        if (chartElement && chartElement.innerHTML === "") {
            
            const labels = @json($chartLabels ?? []);
            const data = @json($chartData ?? []);
            const isDark = document.documentElement.classList.contains('dark');

            var options = {
                series: [{ name: 'Pendapatan', data: data }],
                chart: {
                    type: 'bar',
                    height: window.innerWidth < 768 ? 200 : 300,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    background: 'transparent'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '45%',
                    }
                },
                theme: {
                    mode: isDark ? 'dark' : 'light'
                },
                colors: ['#3b82f6'],
                dataLabels: { enabled: false },
                xaxis: {
                    categories: labels,
                    labels: { style: { colors: isDark ? '#94a3b8' : '#64748b', fontSize: '12px' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(value / 1000);
                        },
                        style: { colors: isDark ? '#94a3b8' : '#64748b', fontSize: '12px' }
                    },
                    min: 0,
                    tickAmount: 5
                },
                grid: {
                    borderColor: isDark ? '#334155' : '#e2e8f0',
                    strokeDashArray: 4,
                }
            };

            var chart = new ApexCharts(chartElement, options);
            chart.render();
        }

        var paymentChartElement = document.querySelector("#paymentChart");
        if (paymentChartElement && paymentChartElement.innerHTML === "") {
            const paymentData = @json($paymentMethodStats ?? []);
            
            if (paymentData.length > 0) {
                const series = paymentData.map(item => item.count);
                const labels = paymentData.map(item => item.payment_method.charAt(0).toUpperCase() + item.payment_method.slice(1));
                const isDark = document.documentElement.classList.contains('dark');
                
                var donutOptions = {
                    series: series,
                    labels: labels,
                    chart: {
                        type: 'donut',
                        height: 300,
                        fontFamily: 'Inter, sans-serif',
                        background: 'transparent'
                    },
                    theme: {
                        mode: isDark ? 'dark' : 'light'
                    },
                    colors: ['#3b82f6', '#10b981', '#6366f1', '#f59e0b', '#8b5cf6'],
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    name: { show: true },
                                    value: { show: true }
                                }
                            }
                        }
                    },
                    dataLabels: { enabled: false },
                    stroke: { show: true, colors: isDark ? ['#1e293b'] : ['#ffffff'], width: 2 },
                    legend: { position: 'bottom' }
                };

                var donutChart = new ApexCharts(paymentChartElement, donutOptions);
                donutChart.render();
            } else {
                paymentChartElement.innerHTML = '<div class="flex items-center justify-center h-full text-slate-400 text-sm">Belum ada transaksi hari ini</div>';
            }
        }
    }

    document.addEventListener('livewire:initialized', () => { loadSalesChart(); });
    document.addEventListener('livewire:navigated', () => { loadSalesChart(); });
</script>