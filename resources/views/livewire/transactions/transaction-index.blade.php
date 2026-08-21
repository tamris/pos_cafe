<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    @include('livewire.includes.sidebar')

    <div class="main-content-layout flex flex-col min-h-screen">
        @include('livewire.includes.header', [
            'title' => 'Transaksi',
            'subtitle' => auth()->user()->role === 'admin' ? 'Riwayat seluruh transaksi cafe' : 'Riwayat transaksi saya (' . auth()->user()->name . ')',
        ])

        <main class="p-4 sm:p-6 space-y-6 flex-1">
            
            {{-- ROW 1: STATS CARDS --}}
            @if($isAdmin)
                {{-- ADMIN: 4 CARDS ANALITIK --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
                    
                    {{-- Card 1: Pendapatan Hari Ini --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs transition-all flex flex-col justify-between">
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
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs transition-all flex flex-col justify-between">
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
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs transition-all flex flex-col justify-between">
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
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs transition-all flex flex-col justify-between">
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
            @else
                {{-- KASIR: 2 CARDS FOKUS OPERASIONAL TRANSAKSI (SMART FILTER-AWARE) --}}
                @php
                    $isTodayFilter = ($dateFrom === today()->format('Y-m-d') && $dateTo === today()->format('Y-m-d'));
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    
                    {{-- Card 1: Transaksi Selesai --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    {{ $isTodayFilter ? 'Transaksi Selesai Hari Ini' : 'Transaksi Selesai (Filter)' }}
                                </span>
                                <div class="flex items-center justify-center w-9 h-9 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl text-emerald-600 dark:text-emerald-400 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                            </div>
                            <div class="mb-3">
                                <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-tight truncate">
                                    Rp {{ number_format($filteredTotal, 0, ',', '.') }}
                                </h3>
                            </div>
                        </div>
                        <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60 justify-between">
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-semibold gap-1.5 bg-emerald-50 dark:bg-emerald-950/50 px-2.5 py-1 rounded-lg border border-emerald-200/60 dark:border-emerald-800/60">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path></svg>
                                {{ number_format($filteredCount, 0, ',', '.') }} Struk Berhasil
                            </span>
                            <span class="text-slate-400 dark:text-slate-500 font-medium">
                                {{ $isTodayFilter ? 'Hari ini, ' . now()->format('d M Y') : \Carbon\Carbon::parse($dateFrom)->format('d/m') . ' - ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>

                    {{-- Card 2: Transaksi Dibatalkan (Void) --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    {{ $isTodayFilter ? 'Transaksi Dibatalkan Hari Ini' : 'Transaksi Dibatalkan (Filter)' }}
                                </span>
                                <div class="flex items-center justify-center w-9 h-9 bg-rose-50 dark:bg-rose-950/40 rounded-xl text-rose-600 dark:text-rose-400 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                </div>
                            </div>
                            <div class="mb-3">
                                <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-tight truncate">
                                    Rp {{ number_format($cancelledTotal, 0, ',', '.') }}
                                </h3>
                            </div>
                        </div>
                        <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60 justify-between">
                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-semibold gap-1.5 bg-rose-50 dark:bg-rose-950/50 px-2.5 py-1 rounded-lg border border-rose-200/60 dark:border-rose-800/60">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                                {{ number_format($cancelledCount, 0, ',', '.') }} Struk Dibatalkan
                            </span>
                            <span class="text-slate-400 dark:text-slate-500 font-medium">
                                {{ $isTodayFilter ? 'Hari ini, ' . now()->format('d M Y') : \Carbon\Carbon::parse($dateFrom)->format('d/m') . ' - ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ROW 2: TABLE & FILTERS --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors overflow-hidden">
                
                {{-- Header Filter Box --}}
                <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-700 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Riwayat Transaksi</h2>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Daftar semua transaksi yang telah dilakukan</p>
                        </div>

                        {{-- Quick Date Pill Shortcut --}}
                        @php
                            $isTodayActive = ($dateFrom === today()->format('Y-m-d') && $dateTo === today()->format('Y-m-d'));
                            $isThisMonthActive = ($dateFrom === now()->startOfMonth()->format('Y-m-d') && $dateTo === now()->endOfMonth()->format('Y-m-d'));
                        @endphp
                        <div class="flex items-center gap-1.5 self-start sm:self-auto">
                            <button type="button" 
                                wire:click="setFilterToday"
                                class="px-3 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $isTodayActive ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                                Hari Ini
                            </button>
                            <button type="button" 
                                wire:click="setFilterThisMonth"
                                class="px-3 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $isThisMonthActive ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                                Bulan Ini
                            </button>
                        </div>
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

                            {{-- Filter Metode Pembayaran --}}
                            <div class="relative group w-full sm:w-auto">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 group-hover:text-emerald-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                </div>
                                <select wire:model.live="paymentMethodFilter" class="appearance-none w-full sm:w-44 pl-10 pr-10 py-2 sm:py-2.5 text-xs sm:text-sm font-medium border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer transition-all outline-none">
                                    <option value="">Semua Metode</option>
                                    <option value="cash">Tunai (Cash)</option>
                                    <option value="transfer">Transfer Bank</option>
                                    <option value="qris">QRIS</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"></path></svg>
                                </div>
                            </div>

                            {{-- Filter Status --}}
                            <div class="relative group w-full sm:w-auto">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 group-hover:text-emerald-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <select wire:model.live="statusFilter" class="appearance-none w-full sm:w-44 pl-10 pr-10 py-2 sm:py-2.5 text-xs sm:text-sm font-medium border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer transition-all outline-none">
                                    <option value="">Semua Status</option>
                                    <option value="completed">Selesai (Lunas)</option>
                                    <option value="pending">Bill Aktif (Open Bill)</option>
                                    <option value="cancelled">Dibatalkan (Void)</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Table Responsive Area --}}
                <div class="overflow-x-auto scrollbar-thin">
                    <table class="w-full text-left border-collapse min-w-[700px]">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 text-[11px] uppercase text-slate-500 dark:text-slate-400 font-bold">
                            <tr>
                                <th class="px-4 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700 whitespace-nowrap">Invoice</th>
                                <th class="px-4 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700 whitespace-nowrap">Tanggal</th>
                                <th class="px-4 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700 whitespace-nowrap">Kasir</th>
                                <th class="px-4 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700 whitespace-nowrap">Tipe / Info</th>
                                <th class="px-4 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700 whitespace-nowrap text-right">Total</th>
                                <th class="px-4 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700 whitespace-nowrap text-center">Metode & Status</th>
                                <th class="px-4 sm:px-6 py-3.5 border-b border-slate-200 dark:border-slate-700 whitespace-nowrap text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-xs sm:text-sm">
                            @forelse($transactions as $transaction)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors {{ $transaction->status === 'cancelled' ? 'bg-rose-50/30 dark:bg-rose-950/20' : ($transaction->status === 'pending' ? 'bg-amber-50/30 dark:bg-amber-950/20' : '') }}">
                                    <td class="px-4 sm:px-6 py-3.5 font-extrabold text-slate-900 dark:text-white font-mono whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            <span>{{ $transaction->invoice_number }}</span>
                                            @if($transaction->status === 'cancelled')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">
                                                    VOID
                                                </span>
                                            @elseif($transaction->status === 'pending')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300">
                                                    OPEN
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $transaction->created_at->format('d M Y') }}</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $transaction->created_at->format('H:i') }} WIB</p>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3.5 text-slate-700 dark:text-slate-300 font-medium whitespace-nowrap">
                                        {{ $transaction->user->name }}
                                    </td>
                                    <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
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
                                    <td class="px-4 sm:px-6 py-3.5 font-black text-slate-900 dark:text-white whitespace-nowrap text-right">
                                        @if($transaction->status === 'cancelled')
                                            <span class="line-through text-slate-400 dark:text-slate-500 font-normal">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                                        @else
                                            <span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap text-center">
                                        <div class="inline-flex items-center justify-center gap-1.5">
                                            @if($transaction->payment_method == 'cash')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                                                    Tunai
                                                </span>
                                            @elseif($transaction->payment_method == 'qris')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                                    QRIS
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800">
                                                    Transfer
                                                </span>
                                            @endif

                                            @if($transaction->status === 'cancelled')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800" title="Alasan: {{ $transaction->cancelled_reason }}">
                                                    Dibatalkan
                                                </span>
                                            @elseif($transaction->status === 'pending')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                    Bill Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                    Selesai
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3.5 text-right whitespace-nowrap shrink-0">
                                        @php
                                            $canCancelRow = false;
                                            if ($transaction->status === 'completed') {
                                                if ($isAdmin) {
                                                    $canCancelRow = true;
                                                } elseif ($transaction->user_id === auth()->id() && $transaction->shift && $transaction->shift->status === 'open') {
                                                    $canCancelRow = true;
                                                }
                                            }
                                        @endphp
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button wire:click="viewDetail({{ $transaction->id }})"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-bold transition-all shadow-2xs active:scale-95 cursor-pointer" title="Lihat Rincian Transaksi">
                                                <svg class="w-3.5 h-3.5 sm:mr-1 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                <span class="hidden sm:inline">Detail</span>
                                            </button>

                                            @if($canCancelRow)
                                                <button wire:click="openCancelModal({{ $transaction->id }})"
                                                    class="inline-flex items-center px-2.5 py-1.5 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/80 rounded-lg text-xs font-bold transition-all shadow-2xs active:scale-95 cursor-pointer" title="Batalkan Transaksi (Void)">
                                                    <svg class="w-3.5 h-3.5 sm:mr-1 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                    <span class="hidden sm:inline">Batal</span>
                                                </button>
                                            @endif
                                        </div>
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
        <div class="fixed inset-0 z-[100] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closeDetailModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle max-w-lg w-full border border-slate-200 dark:border-slate-700">
                    
                    {{-- Modal Header --}}
                    <div class="bg-slate-900 dark:bg-slate-850 text-white px-5 sm:px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold">Detail Transaksi</h3>
                                @if($selectedTransaction->status === 'cancelled')
                                    <span class="px-2 py-0.5 bg-rose-600 text-white font-bold text-[10px] rounded-md tracking-wider">
                                        VOID / DIBATALKAN
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $selectedTransaction->invoice_number }}</p>
                        </div>
                        <button type="button" wire:click="closeDetailModal" class="text-slate-400 hover:text-white transition-colors p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-5 sm:p-6 bg-white dark:bg-slate-800 space-y-4">
                        
                        {{-- Banner Jika Dibatalkan --}}
                        @if($selectedTransaction->status === 'cancelled')
                            <div class="p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/80 rounded-xl text-rose-900 dark:text-rose-200 text-xs space-y-1">
                                <div class="flex items-center gap-1.5 font-black text-rose-700 dark:text-rose-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path></svg>
                                    <span>TRANSAKSI INI TELAH DIBATALKAN (VOID)</span>
                                </div>
                                <p class="text-[11px] text-slate-600 dark:text-slate-300">
                                    Dibatalkan oleh: <span class="font-bold">{{ $selectedTransaction->cancelledBy->name ?? 'Kasir' }}</span> 
                                    • {{ $selectedTransaction->cancelled_at ? $selectedTransaction->cancelled_at->format('d/m/Y H:i') : '-' }} WIB
                                </p>
                                <p class="text-[11px] font-medium text-rose-800 dark:text-rose-300">
                                    Alasan: <span class="italic font-bold">{{ $selectedTransaction->cancelled_reason ?: 'Tidak ada alasan' }}</span>
                                </p>
                            </div>
                        @endif

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
                                <span class="text-lg sm:text-xl font-black {{ $selectedTransaction->status === 'cancelled' ? 'line-through text-slate-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    Rp {{ number_format($selectedTransaction->total, 0, ',', '.') }}
                                </span>
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
                    <div class="bg-slate-50 dark:bg-slate-900/50 px-5 sm:px-6 py-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 border-t border-slate-200 dark:border-slate-700">
                        {{-- Sisi Kiri: Tombol Void (jika diizinkan) --}}
                        <div>
                            @php
                                $canCancelModal = false;
                                if ($selectedTransaction->status === 'completed') {
                                    if ($isAdmin) {
                                        $canCancelModal = true;
                                    } elseif ($selectedTransaction->user_id === auth()->id() && $selectedTransaction->shift && $selectedTransaction->shift->status === 'open') {
                                        $canCancelModal = true;
                                    }
                                }
                            @endphp

                            @if($canCancelModal)
                                <button type="button" wire:click="openCancelModal({{ $selectedTransaction->id }})"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/80 rounded-xl transition-all font-bold text-xs shadow-2xs active:scale-95 cursor-pointer">
                                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                    <span>Batalkan (Void)</span>
                                </button>
                            @endif
                        </div>

                        {{-- Sisi Kanan: Tiket Dapur, Cetak Struk, dan Tutup --}}
                        <div class="flex items-center justify-end gap-2 flex-wrap sm:flex-nowrap">
                            @if($selectedTransaction->status !== 'cancelled')
                                <button type="button" onclick="printKitchenDirect('{{ $selectedTransaction->invoice_number }}')"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-all font-bold text-xs shadow-xs active:scale-95 cursor-pointer" title="Cetak tiket dapur untuk barista/chef">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path></svg>
                                    <span>Dapur</span>
                                </button>
                                <button type="button" onclick="printStrukDirect('{{ $selectedTransaction->invoice_number }}')"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition-all font-bold text-xs shadow-xs active:scale-95 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    <span>Cetak Struk</span>
                                </button>
                            @endif

                            <button type="button" wire:click="closeDetailModal"
                                class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl transition-all font-bold text-xs shadow-xs active:scale-95 cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL KONFIRMASI PEMBATALAN TRANSAKSI (VOID) --}}
    @if ($showCancelModal)
        <div class="fixed inset-0 z-[100] overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 dark:border-slate-700 transform transition-all animate-scale-up relative">
                
                {{-- Header --}}
                <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-rose-50 dark:bg-rose-950/40 rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Batalkan Transaksi (Void)</h3>
                            <p class="text-xs text-rose-600 dark:text-rose-400 font-mono font-bold">{{ $cancelTransactionInvoice }} • Rp {{ number_format($cancelTransactionTotal, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <button wire:click="closeCancelModal" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-lg cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Form Body --}}
                <div class="p-5 space-y-4">
                    <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs rounded-xl flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path></svg>
                        <span class="leading-relaxed">Pembatalan ini akan otomatis mengurangkan omset & kas di shift kasir yang aktif. Tindakan ini tercatat di audit rekap shift.</span>
                    </div>

                    {{-- Custom Floating Dropdown (Alpine.js) --}}
                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Pilih Alasan Pembatalan:
                        </label>

                        {{-- Trigger Button --}}
                        <button type="button" @click="open = !open"
                            class="w-full text-left px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white flex items-center justify-between text-xs sm:text-sm font-semibold transition-all hover:border-slate-400 dark:hover:border-slate-500 focus:ring-2 focus:ring-rose-500 cursor-pointer shadow-2xs">
                            
                            <div class="flex items-center gap-2.5 truncate">
                                @if($cancelReasonPreset === 'Pelanggan Membatalkan Pesanan')
                                    <div class="w-7 h-7 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                    </div>
                                    <span class="truncate">Pelanggan Membatalkan Pesanan</span>
                                @elseif($cancelReasonPreset === 'Salah Input Menu / Varian')
                                    <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"></path></svg>
                                    </div>
                                    <span class="truncate">Salah Input Menu / Varian</span>
                                @elseif($cancelReasonPreset === 'Salah Metode Pembayaran')
                                    <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"></path></svg>
                                    </div>
                                    <span class="truncate">Salah Metode Pembayaran</span>
                                @elseif($cancelReasonPreset === 'Pesanan Ganda (Double Order)')
                                    <div class="w-7 h-7 rounded-lg bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"></path></svg>
                                    </div>
                                    <span class="truncate">Pesanan Ganda (Double Order)</span>
                                @else
                                    <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path></svg>
                                    </div>
                                    <span class="truncate">Alasan Lainnya (Tulis Catatan)</span>
                                @endif
                            </div>

                            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 transition-transform duration-200 shrink-0 ml-2" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"></path></svg>
                        </button>

                        {{-- Floating Dropdown Menu (Compact & Instant) --}}
                        <div x-show="open" 
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute left-0 right-0 mt-1.5 z-50 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 py-1 divide-y divide-slate-100 dark:divide-slate-750"
                            style="display: none;">
                            
                            @php
                                $reasons = [
                                    ['val' => 'Pelanggan Membatalkan Pesanan', 'label' => 'Pelanggan Membatalkan Pesanan', 'icon' => 'cancel', 'color' => 'rose'],
                                    ['val' => 'Salah Input Menu / Varian', 'label' => 'Salah Input Menu / Varian', 'icon' => 'edit', 'color' => 'amber'],
                                    ['val' => 'Salah Metode Pembayaran', 'label' => 'Salah Metode Pembayaran', 'icon' => 'credit-card', 'color' => 'indigo'],
                                    ['val' => 'Pesanan Ganda (Double Order)', 'label' => 'Pesanan Ganda (Double Order)', 'icon' => 'layers', 'color' => 'cyan'],
                                    ['val' => 'Lainnya', 'label' => 'Alasan Lainnya (Tulis Catatan)', 'icon' => 'notes', 'color' => 'emerald'],
                                ];
                            @endphp

                            @foreach($reasons as $r)
                                @php $isSelected = ($cancelReasonPreset === $r['val']); @endphp
                                <button type="button" 
                                    @click="$wire.set('cancelReasonPreset', '{{ $r['val'] }}'); open = false;"
                                    class="w-full text-left px-3 py-2 flex items-center justify-between text-xs sm:text-sm transition-colors cursor-pointer
                                    {{ $isSelected ? 'bg-rose-50/80 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 font-bold' : 'hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200 font-medium' }}">
                                    
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-6 h-6 rounded-md flex items-center justify-center shrink-0 border
                                            {{ $r['color'] === 'rose' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20' : '' }}
                                            {{ $r['color'] === 'amber' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20' : '' }}
                                            {{ $r['color'] === 'indigo' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20' : '' }}
                                            {{ $r['color'] === 'cyan' ? 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/20' : '' }}
                                            {{ $r['color'] === 'emerald' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' : '' }}">
                                            @if($r['icon'] === 'cancel')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                            @elseif($r['icon'] === 'edit')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"></path></svg>
                                            @elseif($r['icon'] === 'credit-card')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"></path></svg>
                                            @elseif($r['icon'] === 'layers')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"></path></svg>
                                            @else
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path></svg>
                                            @endif
                                        </div>
                                        <span class="truncate">{{ $r['label'] }}</span>
                                    </div>

                                    @if($isSelected)
                                        <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0 ml-2" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path></svg>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Catatan Detail {{ $cancelReasonPreset === 'Lainnya' ? '(Wajib Diisi)' : '(Opsional)' }}:
                        </label>
                        <textarea wire:model="cancelReasonNotes" rows="2" 
                            placeholder="{{ $cancelReasonPreset === 'Lainnya' ? 'Tuliskan alasan pembatalan secara detail...' : 'Contoh: Salah klik menu iced padahal hot...' }}"
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-colors"></textarea>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="p-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2.5 rounded-b-2xl">
                    <button type="button" wire:click="closeCancelModal" class="px-4 py-2.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold hover:bg-slate-300 dark:hover:bg-slate-600 transition-all cursor-pointer active:scale-95">
                        Batal
                    </button>
                    <button type="button" wire:click="confirmCancelTransaction" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center gap-1.5 cursor-pointer active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                        <span>Konfirmasi Batalkan</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
    <script>
        async function printStrukDirect(invoice) {
            if (!invoice) return;

            // 1. PRIORITAS UTAMA: DIRECT WEB BLUETOOTH / USB
            if (window.posBluetooth && window.posBluetooth.isConnected) {
                try {
                    await window.posBluetooth.printInvoice(invoice);
                    return;
                } catch (err) {
                    console.warn('Bluetooth/USB print gagal, beralih ke fallback...', err);
                }
            }

            // 2. FALLBACK ANDROID: RAWBT
            const isAndroid = /Android/i.test(navigator.userAgent);
            if (isAndroid) {
                fetch('/rawbt-struk/' + invoice)
                    .then(res => res.json())
                    .then(data => {
                        if (data.rawbt) {
                            window.location.href = "rawbt:base64," + data.rawbt + "#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";
                        } else {
                            window.open('/print-struk/' + invoice, '_blank');
                        }
                    })
                    .catch(() => {
                        window.open('/print-struk/' + invoice, '_blank');
                    });
                return;
            }

            // 3. FALLBACK DESKTOP: BROWSER PRINT TAB
            window.open('/print-struk/' + invoice, '_blank');
        }

        async function printKitchenDirect(invoice) {
            if (!invoice) return;

            // 1. PRIORITAS UTAMA: DIRECT WEB BLUETOOTH / USB
            if (window.posBluetooth && window.posBluetooth.isConnected) {
                try {
                    await window.posBluetooth.printKitchen(invoice);
                    return;
                } catch (err) {
                    console.warn('Bluetooth/USB print tiket dapur gagal, beralih ke fallback...', err);
                }
            }

            // 2. FALLBACK ANDROID: RAWBT KITCHEN
            const isAndroid = /Android/i.test(navigator.userAgent);
            if (isAndroid) {
                fetch('/rawbt-kitchen/' + invoice)
                    .then(res => res.json())
                    .then(data => {
                        if (data.rawbt) {
                            window.location.href = "rawbt:base64," + data.rawbt + "#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";
                        } else {
                            window.open('/print-kitchen/' + invoice, '_blank');
                        }
                    })
                    .catch(() => {
                        window.open('/print-kitchen/' + invoice, '_blank');
                    });
                return;
            }

            // 3. FALLBACK DESKTOP: BROWSER PRINT TAB
            window.open('/print-kitchen/' + invoice, '_blank');
        }
    </script>
    @endpush
</div>