<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300"
     x-data="{ sidebarOpen: window.innerWidth >= 1280 }"
     @resize.window="if (window.innerWidth >= 1280) { sidebarOpen = true } else { sidebarOpen = false }">
    
    @include('livewire.includes.sidebar')

    <div class="xl:pl-64 transition-all duration-300 flex flex-col min-h-screen">
        @include('livewire.includes.header', [
            'title' => 'Transaksi',
            'subtitle' => auth()->user()->role === 'admin' ? 'Riwayat seluruh transaksi cafe' : 'Riwayat transaksi saya (' . auth()->user()->name . ')',
        ])

        <main class="p-4 sm:p-6 space-y-6 flex-1">
            
            {{-- ROW 1: STATS CARDS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
                
                {{-- Card 1: Pendapatan Hari Ini --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pendapatan Hari Ini</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-orange-50 dark:bg-orange-900/30 rounded-lg text-orange-600 dark:text-orange-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-bold tracking-tight text-orange-600 dark:text-orange-400 leading-tight truncate">
                                Rp {{ number_format($todayOmset, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        @if ($todayOmsetGrowth > 0)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-semibold gap-1 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                +{{ $todayOmsetGrowth }}%
                            </span>
                        @elseif ($todayOmsetGrowth < 0)
                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-semibold gap-1 bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ $todayOmsetGrowth }}%
                            </span>
                        @else
                            <span class="inline-flex items-center text-slate-500 font-medium bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-md">
                                0%
                            </span>
                        @endif
                        <span class="text-slate-400 dark:text-slate-500 ml-2">vs kemarin</span>
                    </div>
                </div>

                {{-- Card 2: Total Pendapatan Filter --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Pendapatan</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-bold tracking-tight text-slate-800 dark:text-white leading-tight truncate">
                                Rp {{ number_format($filteredTotal, 0, ',', '.') }}
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

                {{-- Card 3: Jumlah Transaksi --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Jumlah Transaksi</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-purple-50 dark:bg-purple-900/30 rounded-lg text-purple-600 dark:text-purple-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-bold tracking-tight text-slate-800 dark:text-white leading-tight truncate">
                                {{ number_format($filteredCount, 0, ',', '.') }} <span class="text-xs font-normal text-slate-400 dark:text-slate-500">Struk</span>
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        @if ($countGrowth > 0)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-semibold gap-1 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                +{{ $countGrowth }}%
                            </span>
                        @elseif ($countGrowth < 0)
                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-semibold gap-1 bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ $countGrowth }}%
                            </span>
                        @else
                            <span class="inline-flex items-center text-slate-500 font-medium bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-md">
                                0%
                            </span>
                        @endif
                        <span class="text-slate-400 dark:text-slate-500 ml-2">vs periode sebelumnya</span>
                    </div>
                </div>

                {{-- Card 4: Rata-rata Transaksi (AOV) --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Rata-rata / Struk</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg text-emerald-600 dark:text-emerald-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400 leading-tight truncate">
                                Rp {{ number_format($averageTransaction, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        @if ($averageGrowth > 0)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-semibold gap-1 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                +{{ $averageGrowth }}%
                            </span>
                        @elseif ($averageGrowth < 0)
                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-semibold gap-1 bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ $averageGrowth }}%
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

            {{-- ROW 2: TABLE & FILTERS --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors overflow-hidden">
                
                {{-- Header Filter Box --}}
                <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-700 space-y-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Riwayat Transaksi</h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Daftar semua transaksi yang telah dilakukan</p>
                    </div>
                    
                    <div class="flex flex-col lg:flex-row gap-3">
                        {{-- Search Invoice --}}
                        <div class="flex-1 relative">
                            <input type="text" wire:model.live.debounce.300ms="search" 
                                class="w-full pl-10 pr-4 py-2 sm:py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs sm:text-sm"
                                placeholder="Cari invoice...">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5 sm:top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>

                        {{-- Date Range & Payment Filter --}}
                        <div class="flex flex-wrap sm:flex-nowrap gap-2 sm:gap-3">
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <input type="date" wire:model.live="dateFrom" class="w-full sm:w-auto px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer text-xs sm:text-sm [color-scheme:light] dark:[color-scheme:dark]">
                                <span class="text-xs text-slate-400">s/d</span>
                                <input type="date" wire:model.live="dateTo" class="w-full sm:w-auto px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer text-xs sm:text-sm [color-scheme:light] dark:[color-scheme:dark]">
                            </div>

                            <select wire:model.live="paymentMethodFilter" class="w-full sm:w-auto px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer text-xs sm:text-sm">
                                <option value="">Semua Metode</option>
                                <option value="cash">Tunai (Cash)</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Table Responsive Area --}}
                <div class="overflow-x-auto scrollbar-thin">
                    <table class="w-full text-left border-collapse min-w-[700px]">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-[11px] uppercase text-slate-500 dark:text-slate-400 font-semibold">
                            <tr>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700">Invoice</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700">Tanggal</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700">Kasir</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700">Tipe / Info</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700">Total</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700">Metode</th>
                                <th class="px-5 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700 text-right">Aksi</th>
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
                                        {{ $transaction->user->name }}
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
                                    <td class="px-5 sm:px-6 py-3.5 font-bold text-slate-900 dark:text-white">
                                        Rp {{ number_format($transaction->total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5">
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
                                    <td class="px-5 sm:px-6 py-3.5 text-right space-x-1.5 shrink-0">
                                        <a href="{{ route('print.struk', $transaction->invoice_number) }}" target="_blank"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-medium transition-colors">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                            Cetak
                                        </a>
                                        <button wire:click="viewDetail({{ $transaction->id }})"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-medium transition-colors">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <p class="text-slate-500 dark:text-slate-400 font-medium text-xs sm:text-sm">Tidak ada transaksi ditemukan</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Links --}}
                <div class="px-5 sm:px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    {{ $transactions->links() }}
                </div>
            </div>
        </main>
    </div>

    {{-- DETAIL MODAL --}}
    @if ($showDetailModal && $selectedTransaction)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closeDetailModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle max-w-lg w-full border border-slate-200 dark:border-slate-700">
                    
                    {{-- Modal Header --}}
                    <div class="bg-slate-900 dark:bg-slate-700 text-white px-5 sm:px-6 py-4 border-b border-slate-800 dark:border-slate-600 flex justify-between items-center">
                        <div>
                            <h3 class="text-base font-bold">Detail Transaksi</h3>
                            <p class="text-xs opacity-90 font-mono">{{ $selectedTransaction->invoice_number }}</p>
                        </div>
                        <button type="button" wire:click="closeDetailModal" class="text-white hover:opacity-80">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-5 sm:p-6 bg-white dark:bg-slate-800 space-y-5">
                        <div class="grid grid-cols-2 gap-3 sm:gap-4 text-xs sm:text-sm">
                            <div>
                                <p class="text-slate-500 dark:text-slate-400">Tanggal</p>
                                <p class="font-medium text-slate-900 dark:text-white mt-0.5">{{ $selectedTransaction->created_at->format('d M Y, H:i') }} WIB</p>
                            </div>
                            <div>
                                <p class="text-slate-500 dark:text-slate-400">Kasir</p>
                                <p class="font-medium text-slate-900 dark:text-white mt-0.5">{{ $selectedTransaction->user->name }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 dark:text-slate-400">Tipe Pesanan</p>
                                <p class="font-bold text-amber-600 dark:text-amber-400 uppercase text-xs mt-0.5">
                                    {{ ($selectedTransaction->order_type ?? 'dine_in') === 'dine_in' ? 'Makan di Tempat' : (($selectedTransaction->order_type ?? '') === 'take_away' ? 'Bawa Pulang' : 'Pesan Antar') }}
                                </p>
                            </div>
                            <div>
                                @if(($selectedTransaction->order_type ?? 'dine_in') === 'dine_in')
                                    <p class="text-slate-500 dark:text-slate-400">Nomor Meja</p>
                                    <p class="font-medium text-slate-900 dark:text-white mt-0.5">{{ $selectedTransaction->table_number ? 'Meja '.$selectedTransaction->table_number : '-' }}</p>
                                @else
                                    <p class="text-slate-500 dark:text-slate-400">Nama Pelanggan</p>
                                    <p class="font-medium text-slate-900 dark:text-white mt-0.5">{{ $selectedTransaction->customer_name ?: '-' }}</p>
                                @endif
                            </div>
                            <div class="col-span-2">
                                <p class="text-slate-500 dark:text-slate-400 mb-1">Metode Bayar</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                    {{ $selectedTransaction->payment_method === 'cash' ? 'Tunai' : ($selectedTransaction->payment_method === 'transfer' ? 'Transfer Bank' : 'QRIS') }}
                                </span>
                            </div>
                        </div>

                        {{-- Item List Table --}}
                        <div class="border rounded-lg border-slate-200 dark:border-slate-700 overflow-hidden">
                            <div class="bg-slate-50 dark:bg-slate-700/50 px-4 py-2 border-b border-slate-200 dark:border-slate-700 flex justify-between text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                <span>Produk</span>
                                <span>Subtotal</span>
                            </div>
                            <div class="divide-y divide-slate-100 dark:divide-slate-700 max-h-48 overflow-y-auto scrollbar-thin">
                                @foreach($selectedTransaction->details as $detail)
                                    <div class="px-4 py-3 flex justify-between items-center text-xs sm:text-sm hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                        <div class="pr-2">
                                            <p class="font-medium text-slate-900 dark:text-white">{{ $detail->product->name }}</p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $detail->quantity }} x Rp {{ number_format($detail->price, 0, ',', '.') }}</p>
                                        </div>
                                        <span class="font-bold text-slate-900 dark:text-white shrink-0">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Summary Box --}}
                        <div class="bg-slate-50 dark:bg-slate-700/30 rounded-lg p-4 space-y-2 text-xs sm:text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Subtotal</span>
                                <span class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format($selectedTransaction->subtotal, 0, ',', '.') }}</span>
                            </div>
                            @if ($selectedTransaction->discount > 0)
                                <div class="flex justify-between text-rose-600 dark:text-rose-400 font-medium">
                                    <span>Diskon</span>
                                    <span>- Rp {{ number_format(($selectedTransaction->subtotal * $selectedTransaction->discount) / 100, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            @if ($selectedTransaction->tax > 0)
                                <div class="flex justify-between text-slate-600 dark:text-slate-400 font-medium">
                                    <span>Pajak</span>
                                    <span>+ Rp {{ number_format(($selectedTransaction->subtotal * $selectedTransaction->tax) / 100, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between items-center pt-2 border-t border-slate-200 dark:border-slate-700">
                                <span class="text-sm sm:text-base font-bold text-slate-700 dark:text-slate-300">Total</span>
                                <span class="text-lg sm:text-xl font-extrabold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($selectedTransaction->total, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between pt-1">
                                <span class="text-slate-500 dark:text-slate-400">Dibayar</span>
                                <span class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format($selectedTransaction->paid, 0, ',', '.') }}</span>
                            </div>
                            @if ($selectedTransaction->change > 0)
                                <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-semibold">
                                    <span>Kembalian</span>
                                    <span>Rp {{ number_format($selectedTransaction->change, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="bg-slate-50 dark:bg-slate-700/50 px-5 sm:px-6 py-3.5 flex justify-end border-t border-slate-200 dark:border-slate-700">
                        <button type="button" wire:click="closeDetailModal"
                            class="px-4 py-2 bg-slate-900 dark:bg-blue-600 hover:bg-slate-800 dark:hover:bg-blue-700 text-white rounded-lg transition-colors font-semibold text-xs shadow-sm active:scale-95">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>