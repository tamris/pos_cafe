<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300"
     x-data="{ sidebarOpen: window.innerWidth >= 1280 }"
     @resize.window="if (window.innerWidth >= 1280) { sidebarOpen = true } else { sidebarOpen = false }">
    
    @include('livewire.includes.sidebar')

    <div class="xl:pl-64 transition-all duration-300 flex flex-col min-h-screen">
        @include('livewire.includes.header', [
            'title' => 'Laporan Ringkasan Penjualan',
            'subtitle' => 'Laporan transaksi keuangan, omset, profit, dan export Excel',
        ])

        <main class="p-4 sm:p-6 space-y-6 flex-1">
            
            {{-- FILTER PERIODE SECTION --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors p-4 sm:p-6 space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            📅 Filter Periode Laporan
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pilih tanggal awal dan akhir transaksi yang ingin diringkas</p>
                    </div>

                    {{-- Quick Date Buttons --}}
                    <div class="flex flex-wrap items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-900/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                        <button type="button" wire:click="setQuickDate('today')"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $activeQuickDate === 'today' ? 'bg-slate-900 text-white dark:bg-blue-600 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800' }}">
                            Hari Ini
                        </button>
                        <button type="button" wire:click="setQuickDate('yesterday')"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $activeQuickDate === 'yesterday' ? 'bg-slate-900 text-white dark:bg-blue-600 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800' }}">
                            Kemarin
                        </button>
                        <button type="button" wire:click="setQuickDate('this_week')"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $activeQuickDate === 'this_week' ? 'bg-slate-900 text-white dark:bg-blue-600 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800' }}">
                            Minggu Ini
                        </button>
                        <button type="button" wire:click="setQuickDate('this_month')"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $activeQuickDate === 'this_month' ? 'bg-slate-900 text-white dark:bg-blue-600 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800' }}">
                            Bulan Ini
                        </button>
                        <button type="button" wire:click="setQuickDate('last_month')"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $activeQuickDate === 'last_month' ? 'bg-slate-900 text-white dark:bg-blue-600 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800' }}">
                            Bulan Lalu
                        </button>
                    </div>
                </div>
                
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label for="dateFrom" class="text-xs font-semibold text-slate-600 dark:text-slate-300 shrink-0">Dari Tanggal:</label>
                        <input type="date" wire:model.live="dateFrom" id="dateFrom"
                            class="w-full sm:w-auto px-3 sm:px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs cursor-pointer [color-scheme:light] dark:[color-scheme:dark]">
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label for="dateTo" class="text-xs font-semibold text-slate-600 dark:text-slate-300 shrink-0">Sampai Tanggal:</label>
                        <input type="date" wire:model.live="dateTo" id="dateTo"
                            class="w-full sm:w-auto px-3 sm:px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs cursor-pointer [color-scheme:light] dark:[color-scheme:dark]">
                    </div>
                </div>
            </div>

            {{-- SUMMARY KPI CARDS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
                
                {{-- Total Pendapatan / Omset --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Omset Kotor</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-orange-50 dark:bg-orange-900/30 rounded-lg text-orange-600 dark:text-orange-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-bold tracking-tight text-orange-600 dark:text-orange-400 leading-tight truncate">
                                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
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
                        <span class="text-slate-400 dark:text-slate-500 ml-2">vs periode sebelumnya</span>
                    </div>
                </div>

                {{-- Total Profit Netto --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Profit Bersih</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg text-emerald-600 dark:text-emerald-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400 leading-tight truncate">
                                Rp {{ number_format($totalProfit, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60">
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
                        <span class="text-slate-400 dark:text-slate-500 ml-2">vs periode sebelumnya</span>
                    </div>
                </div>

                {{-- Total Transaksi --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Transaksi</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-purple-50 dark:bg-purple-900/30 rounded-lg text-purple-600 dark:text-purple-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-bold tracking-tight text-slate-800 dark:text-white leading-tight truncate">
                                {{ number_format($totalTransactions, 0, ',', '.') }} <span class="text-xs font-normal text-slate-400 dark:text-slate-500">Nota</span>
                            </h3>
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
                        <span class="text-slate-400 dark:text-slate-500 ml-2">vs periode sebelumnya</span>
                    </div>
                </div>

                {{-- Margin Keuntungan --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-cyan-600 dark:text-cyan-400">Margin Keuntungan</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-cyan-50 dark:bg-cyan-900/30 rounded-lg text-cyan-600 dark:text-cyan-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-bold tracking-tight text-cyan-600 dark:text-cyan-400 leading-tight truncate">
                                {{ $profitMargin }}%
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        @if ($marginGrowth > 0)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-semibold gap-1 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                +{{ $marginGrowth }}%
                            </span>
                        @elseif ($marginGrowth < 0)
                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-semibold gap-1 bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ $marginGrowth }}%
                            </span>
                        @else
                            <span class="inline-flex items-center text-slate-500 font-medium bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-md">
                                0%
                            </span>
                        @endif
                        <span class="text-slate-400 dark:text-slate-500 ml-2">vs periode sebelumnya</span>
                    </div>
                </div>
            </div>

            {{-- TABEL LAPORAN PENJUALAN --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors overflow-hidden">
                
                {{-- Table Header & Export CTA --}}
                <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Ringkasan Transaksi Penjualan</h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Daftar transaksi nota kasir lengkap dengan informasi tipe pesanan, meja, & profit</p>
                    </div>
                    <button wire:click="exportExcel" class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm gap-2 active:scale-95 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Ekspor Laporan Excel
                    </button>
                </div>

                {{-- Table Responsive Container --}}
                <div class="overflow-x-auto scrollbar-thin">
                    <table class="w-full text-left border-collapse min-w-[760px]">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-[11px] uppercase text-slate-500 dark:text-slate-400 font-semibold">
                            <tr>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700">Invoice</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700">Tanggal</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700">Kasir</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700">Tipe Pesanan / Info</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700 text-right">Profit Netto</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700 text-right">Total Tagihan</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700 text-center">Metode Bayar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-xs sm:text-sm">
                            @forelse($transactions as $transaction)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                                    <td class="px-5 sm:px-6 py-3.5 font-bold text-slate-700 dark:text-slate-300 font-mono">
                                        {{ $transaction->invoice_number }}
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5">
                                        <p class="font-medium text-slate-900 dark:text-white">{{ $transaction->created_at->format('d M Y') }}</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $transaction->created_at->format('H:i') }} WIB</p>
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5 text-slate-600 dark:text-slate-300">
                                        {{ $transaction->user->name ?? 'Admin' }}
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5">
                                        <p class="font-semibold text-slate-900 dark:text-white block text-xs">
                                            {{ ($transaction->order_type ?? 'dine_in') === 'dine_in' ? 'Makan di Tempat' : (($transaction->order_type ?? '') === 'take_away' ? 'Bawa Pulang' : 'Pesan Antar') }}
                                        </p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                            @if(($transaction->order_type ?? 'dine_in') === 'dine_in')
                                                {{ $transaction->table_number ? 'Meja: '.$transaction->table_number : '-' }}
                                            @else
                                                {{ $transaction->customer_name ? 'Pelanggan: '.$transaction->customer_name : '-' }}
                                            @endif
                                        </p>
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5 font-bold text-emerald-600 dark:text-emerald-400 text-right">
                                        + Rp {{ number_format($transaction->details->sum('profit'), 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5 font-bold text-slate-900 dark:text-white text-right">
                                        Rp {{ number_format($transaction->total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5 text-center">
                                        @if($transaction->payment_method == 'cash')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                                Tunai
                                            </span>
                                        @elseif($transaction->payment_method == 'transfer')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                Transfer
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                                QRIS
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <p class="text-slate-500 dark:text-slate-400 font-medium text-xs sm:text-sm">Tidak ada data transaksi pada periode ini</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Area --}}
                <div class="px-5 sm:px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    {{ $transactions->links() }}
                </div>
            </div>
        </main>
    </div>
</div>