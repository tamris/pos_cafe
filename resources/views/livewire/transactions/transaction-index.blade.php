<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300"
     x-data="{ sidebarOpen: window.innerWidth >= 1280 }"
     @resize.window="sidebarOpen = window.innerWidth >= 1280">
    
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
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pendapatan Hari Ini</span>
                            <div class="flex items-center justify-center w-9 h-9 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl text-emerald-600 dark:text-emerald-400 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-tight truncate">
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
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Pendapatan</span>
                            <div class="flex items-center justify-center w-9 h-9 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl text-emerald-600 dark:text-emerald-400 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-tight truncate">
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
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Jumlah Transaksi</span>
                            <div class="flex items-center justify-center w-9 h-9 bg-slate-100 dark:bg-slate-700 rounded-xl text-slate-700 dark:text-slate-300 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-tight truncate">
                                {{ number_format($filteredCount, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">Struk</span>
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
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
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
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Rata-rata / Struk</span>
                            <div class="flex items-center justify-center w-9 h-9 bg-slate-100 dark:bg-slate-700 rounded-xl text-slate-700 dark:text-slate-300 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-tight truncate">
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
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors overflow-hidden">
                
                {{-- Header Filter Box --}}
                <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-700 space-y-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Riwayat Transaksi</h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Daftar semua transaksi yang telah dilakukan</p>
                    </div>
                    
                    <div class="flex flex-col lg:flex-row gap-3">
                        {{-- Search Invoice --}}
                        <div class="flex-1 relative">
                            <input type="text" wire:model.live.debounce.300ms="search" 
                                class="w-full pl-10 pr-4 py-2 sm:py-2.5 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs sm:text-sm"
                                placeholder="Cari nomor invoice / kasir...">
                            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3.5 top-3" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path></svg>
                        </div>

                        {{-- Date Range & Payment Filter --}}
                        <div class="flex flex-wrap sm:flex-nowrap gap-2 sm:gap-3">
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <input type="date" wire:model.live="dateFrom" class="w-full sm:w-auto px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer text-xs sm:text-sm [color-scheme:light] dark:[color-scheme:dark]">
                                <span class="text-xs text-slate-400">s/d</span>
                                <input type="date" wire:model.live="dateTo" class="w-full sm:w-auto px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer text-xs sm:text-sm [color-scheme:light] dark:[color-scheme:dark]">
                            </div>

                            <select wire:model.live="paymentMethodFilter" class="w-full sm:w-auto px-3 sm:px-4 py-2 sm:py-2.5 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer text-xs sm:text-sm font-medium">
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
                        <thead class="bg-slate-50 dark:bg-slate-900/50 text-[11px] uppercase text-slate-500 dark:text-slate-400 font-bold">
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
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                                    <td class="px-5 sm:px-6 py-3.5 font-extrabold text-slate-900 dark:text-white font-mono">
                                        {{ $transaction->invoice_number }}
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5">
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $transaction->created_at->format('d M Y') }}</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $transaction->created_at->format('H:i') }} WIB</p>
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5 text-slate-700 dark:text-slate-300 font-medium">
                                        {{ $transaction->user->name }}
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
                                    <td class="px-5 sm:px-6 py-3.5 font-black text-slate-900 dark:text-white">
                                        Rp {{ number_format($transaction->total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 sm:px-6 py-3.5">
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
                                    <td class="px-5 sm:px-6 py-3.5 text-right space-x-1.5 shrink-0">
                                        <a href="{{ route('print.struk', $transaction->invoice_number) }}" target="_blank"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-bold transition-all shadow-2xs active:scale-95">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.077-.32-2.146-.32-3.149 0-4.418 3.582-8 8-8s8 3.582 8 8c0 1.003-.08 2.072-.32 3.149m-15.36 0A10.024 10.024 0 0012 21.75c3.27 0 6.182-1.564 8.04-3.921m-15.36 0c.24 1.077.32 2.146.32 3.149M6.72 13.829h10.56M6.72 13.829H3.75A2.25 2.25 0 001.5 16.079v3.421a2.25 2.25 0 002.25 2.25h16.5a2.25 2.25 0 002.25-2.25v-3.421a2.25 2.25 0 00-2.25-2.25H17.28"></path></svg>
                                            <span>Cetak</span>
                                        </a>
                                        <button wire:click="viewDetail({{ $transaction->id }})"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-bold transition-all shadow-2xs active:scale-95">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            <span>Detail</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path></svg>
                                            </div>
                                            <p class="text-slate-600 dark:text-slate-300 font-bold text-xs sm:text-sm">Tidak ada transaksi ditemukan</p>
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

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle max-w-lg w-full border border-slate-200 dark:border-slate-700">
                    
                    {{-- Modal Header --}}
                    <div class="bg-slate-900 dark:bg-slate-850 text-white px-5 sm:px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                        <div>
                            <h3 class="text-base font-bold">Detail Transaksi</h3>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $selectedTransaction->invoice_number }}</p>
                        </div>
                        <button type="button" wire:click="closeDetailModal" class="text-slate-400 hover:text-white transition-colors p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-5 sm:p-6 bg-white dark:bg-slate-800 space-y-5">
                        <div class="grid grid-cols-2 gap-3 sm:gap-4 text-xs sm:text-sm">
                            <div>
                                <p class="text-slate-400 font-medium">Tanggal</p>
                                <p class="font-bold text-slate-900 dark:text-white mt-0.5">{{ $selectedTransaction->created_at->format('d M Y, H:i') }} WIB</p>
                            </div>
                            <div>
                                <p class="text-slate-400 font-medium">Kasir</p>
                                <p class="font-bold text-slate-900 dark:text-white mt-0.5">{{ $selectedTransaction->user->name }}</p>
                            </div>
                            <div>
                                <p class="text-slate-400 font-medium">Tipe Pesanan</p>
                                <p class="font-bold text-slate-900 dark:text-white mt-0.5">
                                    {{ ($selectedTransaction->order_type ?? 'dine_in') === 'dine_in' ? 'Dine In' : (($selectedTransaction->order_type ?? '') === 'take_away' ? 'Take Away' : 'Delivery') }}
                                </p>
                            </div>
                            <div>
                                @if(($selectedTransaction->order_type ?? 'dine_in') === 'dine_in')
                                    <p class="text-slate-400 font-medium">Nomor Meja</p>
                                    <p class="font-bold text-slate-900 dark:text-white mt-0.5">{{ $selectedTransaction->table_number ? 'Meja '.$selectedTransaction->table_number : '-' }}</p>
                                @else
                                    <p class="text-slate-400 font-medium">Nama Pelanggan</p>
                                    <p class="font-bold text-slate-900 dark:text-white mt-0.5">{{ $selectedTransaction->customer_name ?: '-' }}</p>
                                @endif
                            </div>
                            <div class="col-span-2">
                                <p class="text-slate-400 font-medium mb-1">Metode Bayar</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-600">
                                    {{ $selectedTransaction->payment_method === 'cash' ? 'Tunai' : ($selectedTransaction->payment_method === 'transfer' ? 'Transfer Bank' : 'QRIS') }}
                                </span>
                            </div>
                        </div>

                        {{-- Item List Table --}}
                        <div class="border rounded-xl border-slate-200 dark:border-slate-700 overflow-hidden">
                            <div class="bg-slate-50 dark:bg-slate-900/50 px-4 py-2.5 border-b border-slate-200 dark:border-slate-700 flex justify-between text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                <span>Produk</span>
                                <span>Subtotal</span>
                            </div>
                            <div class="divide-y divide-slate-100 dark:divide-slate-700 max-h-48 overflow-y-auto scrollbar-thin">
                                @foreach($selectedTransaction->details as $detail)
                                    <div class="px-4 py-3 flex justify-between items-center text-xs sm:text-sm hover:bg-slate-50/80 dark:hover:bg-slate-700/30">
                                        <div class="pr-2">
                                            <p class="font-bold text-slate-900 dark:text-white">{{ $detail->product->name }}</p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $detail->quantity }} x Rp {{ number_format($detail->price, 0, ',', '.') }}</p>
                                        </div>
                                        <span class="font-black text-slate-900 dark:text-white shrink-0">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Summary Box --}}
                        <div class="bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700 rounded-xl p-4 space-y-2 text-xs sm:text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400 font-medium">Subtotal</span>
                                <span class="font-bold text-slate-900 dark:text-white">Rp {{ number_format($selectedTransaction->subtotal, 0, ',', '.') }}</span>
                            </div>
                            @if ($selectedTransaction->discount > 0)
                                <div class="flex justify-between text-rose-600 dark:text-rose-400 font-bold">
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
                                <span class="text-sm sm:text-base font-bold text-slate-800 dark:text-slate-200">Total</span>
                                <span class="text-lg sm:text-xl font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($selectedTransaction->total, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between pt-1">
                                <span class="text-slate-500 dark:text-slate-400 font-medium">Dibayar</span>
                                <span class="font-bold text-slate-900 dark:text-white">Rp {{ number_format($selectedTransaction->paid, 0, ',', '.') }}</span>
                            </div>
                            @if ($selectedTransaction->change > 0)
                                <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-bold">
                                    <span>Kembalian</span>
                                    <span>Rp {{ number_format($selectedTransaction->change, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="bg-slate-50 dark:bg-slate-900/50 px-5 sm:px-6 py-4 flex justify-end border-t border-slate-200 dark:border-slate-700">
                        <button type="button" wire:click="closeDetailModal"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition-all font-bold text-xs shadow-xs active:scale-95">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>