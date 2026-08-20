<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    @include('livewire.includes.sidebar')

    <div class="main-content-layout flex flex-col min-h-screen">
        @include('livewire.includes.header', [
            'title' => 'Laporan Ringkasan Penjualan',
            'subtitle' => 'Laporan transaksi keuangan, omset, profit, dan export Excel',
        ])

        <main class="p-4 sm:p-6 space-y-6 flex-1">
            
            {{-- FILTER PERIODE SECTION --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors p-4 sm:p-6 space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.253M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"></path></svg>
                            <span>Filter Periode Laporan</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pilih tanggal awal dan akhir transaksi yang ingin diringkas</p>
                    </div>

                    {{-- Quick Date Buttons --}}
                    <div class="flex flex-wrap items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-900/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                        <button type="button" wire:click="setQuickDate('today')"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $activeQuickDate === 'today' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800' }}">
                            Hari Ini
                        </button>
                        <button type="button" wire:click="setQuickDate('yesterday')"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $activeQuickDate === 'yesterday' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800' }}">
                            Kemarin
                        </button>
                        <button type="button" wire:click="setQuickDate('this_week')"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $activeQuickDate === 'this_week' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800' }}">
                            Minggu Ini
                        </button>
                        <button type="button" wire:click="setQuickDate('this_month')"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $activeQuickDate === 'this_month' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800' }}">
                            Bulan Ini
                        </button>
                        <button type="button" wire:click="setQuickDate('last_month')"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $activeQuickDate === 'last_month' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800' }}">
                            Bulan Lalu
                        </button>
                    </div>
                </div>
                
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label for="dateFrom" class="text-xs font-bold text-slate-700 dark:text-slate-300 shrink-0 uppercase tracking-wider">Dari Tanggal:</label>
                        <input type="date" wire:model.live="dateFrom" id="dateFrom"
                            class="w-full sm:w-auto px-3 sm:px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs cursor-pointer [color-scheme:light] dark:[color-scheme:dark]">
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label for="dateTo" class="text-xs font-bold text-slate-700 dark:text-slate-300 shrink-0 uppercase tracking-wider">Sampai Tanggal:</label>
                        <input type="date" wire:model.live="dateTo" id="dateTo"
                            class="w-full sm:w-auto px-3 sm:px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs cursor-pointer [color-scheme:light] dark:[color-scheme:dark]">
                    </div>
                </div>
            </div>

            {{-- SUMMARY KPI CARDS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
                
                {{-- Total Pendapatan / Omset --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Omset Kotor</span>
                            <div class="flex items-center justify-center w-9 h-9 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl text-emerald-600 dark:text-emerald-400 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-tight truncate">
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
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Profit Bersih</span>
                            <div class="flex items-center justify-center w-9 h-9 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl text-emerald-600 dark:text-emerald-400 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-black tracking-tight text-emerald-600 dark:text-emerald-400 leading-tight truncate">
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
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Transaksi</span>
                            <div class="flex items-center justify-center w-9 h-9 bg-slate-100 dark:bg-slate-700 rounded-xl text-slate-700 dark:text-slate-300 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-tight truncate">
                                {{ number_format($totalTransactions, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">Nota</span>
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
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Margin Keuntungan</span>
                            <div class="flex items-center justify-center w-9 h-9 bg-slate-100 dark:bg-slate-700 rounded-xl text-slate-700 dark:text-slate-300 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-tight truncate">
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
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors overflow-hidden">
                
                {{-- Table Header & Export CTA --}}
                <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Ringkasan Transaksi Penjualan</h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Daftar transaksi nota kasir lengkap dengan informasi tipe pesanan, meja, & profit</p>
                    </div>
                    <button wire:click="exportExcel" class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs gap-2 active:scale-95 shrink-0 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"></path></svg>
                        <span>Ekspor Laporan Excel</span>
                    </button>
                </div>

                {{-- Table Responsive Container --}}
                <div class="overflow-x-auto scrollbar-thin">
                    <table class="w-full text-left border-collapse min-w-[760px]">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 text-[11px] uppercase text-slate-500 dark:text-slate-400 font-bold">
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
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                                    <td class="px-5 sm:px-6 py-3.5 font-extrabold text-slate-900 dark:text-white font-mono">
                                        {{ $transaction->invoice_number }}
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5">
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $transaction->created_at->format('d M Y') }}</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $transaction->created_at->format('H:i') }} WIB</p>
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5 text-slate-700 dark:text-slate-300 font-medium">
                                        {{ $transaction->user->name ?? 'Admin' }}
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5">
                                        <p class="font-bold text-slate-900 dark:text-white block text-xs">
                                            {{ ($transaction->order_type ?? 'dine_in') === 'dine_in' ? 'Dine In' : (($transaction->order_type ?? '') === 'take_away' ? 'Take Away' : 'Delivery') }}
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
                                    <td class="px-5 sm:px-6 py-3.5 font-black text-slate-900 dark:text-white text-right">
                                        Rp {{ number_format($transaction->total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5 text-center">
                                        @if($transaction->payment_method == 'cash')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                                Tunai
                                            </span>
                                        @elseif($transaction->payment_method == 'transfer')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                                Transfer
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                QRIS
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path></svg>
                                            </div>
                                            <p class="text-slate-600 dark:text-slate-300 font-bold text-xs sm:text-sm">Tidak ada data transaksi pada periode ini</p>
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