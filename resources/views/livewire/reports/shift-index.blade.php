<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300"
     x-data="{ sidebarOpen: window.innerWidth >= 1280 }"
     @resize.window="if (window.innerWidth >= 1280) { sidebarOpen = true } else { sidebarOpen = false }">
    
    @include('livewire.includes.sidebar')

    <div class="xl:pl-64 transition-all duration-300 flex flex-col min-h-screen">
        @include('livewire.includes.header', [
            'title' => 'Laporan Shift Kasir',
            'subtitle' => 'Rekap buka-tutup shift, rekonsiliasi laci kasir, dan deteksi selisih kas',
        ])

        <main class="p-4 sm:p-6 space-y-6 flex-1">
            
            {{-- FILTER & CONTROLS SECTION --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-4 sm:p-6 space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            🕒 Filter Laporan Shift
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Filter riwayat shift berdasarkan tanggal, kasir, dan status shift</p>
                    </div>

                    {{-- Quick Date Buttons --}}
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button type="button" wire:click="setQuickDate('today')" class="px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Hari Ini</button>
                        <button type="button" wire:click="setQuickDate('yesterday')" class="px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Kemarin</button>
                        <button type="button" wire:click="setQuickDate('this_week')" class="px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Minggu Ini</button>
                        <button type="button" wire:click="setQuickDate('this_month')" class="px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Bulan Ini</button>
                        <button type="button" wire:click="setQuickDate('last_month')" class="px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Bulan Lalu</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    {{-- Date From --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Dari Tanggal:</label>
                        <input type="date" wire:model.live="dateFrom"
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs cursor-pointer [color-scheme:light] dark:[color-scheme:dark]">
                    </div>

                    {{-- Date To --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Sampai Tanggal:</label>
                        <input type="date" wire:model.live="dateTo"
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs cursor-pointer [color-scheme:light] dark:[color-scheme:dark]">
                    </div>

                    {{-- Filter Kasir --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Pilih Kasir:</label>
                        <select wire:model.live="selectedUserId"
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs">
                            <option value="">Semua Kasir</option>
                            @foreach ($cashiers as $cashier)
                                <option value="{{ $cashier->id }}">{{ $cashier->name }} ({{ ucfirst($cashier->role ?? 'Kasir') }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Status --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Status Shift:</label>
                        <select wire:model.live="selectedStatus"
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs">
                            <option value="">Semua Status</option>
                            <option value="open">🟢 Shift Aktif (Belum Ditutup)</option>
                            <option value="closed">🔒 Shift Selesai (Ditutup)</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- SUMMARY KPI CARDS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
                
                {{-- Card 1: Total Shift --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Shift Kerja</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-purple-50 dark:bg-purple-900/30 rounded-lg text-purple-600 dark:text-purple-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-1">
                            <h3 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white leading-tight">
                                {{ number_format($totalShifts, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                    <div class="text-xs text-slate-400 dark:text-slate-500 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        Shift tercatat pada periode ini
                    </div>
                </div>

                {{-- Card 2: Total Tunai Shift --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Penjualan Tunai</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg text-emerald-600 dark:text-emerald-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-1">
                            <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400 leading-tight truncate">
                                Rp {{ number_format($totalCashSales, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                    <div class="text-xs text-slate-400 dark:text-slate-500 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        Uang cash masuk ke laci kasir
                    </div>
                </div>

                {{-- Card 3: Total Non-Tunai --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Penjualan Non-Tunai</span>
                            <div class="flex items-center justify-center w-10 h-10 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-1">
                            <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-blue-600 dark:text-blue-400 leading-tight truncate">
                                Rp {{ number_format($totalNonCashSales, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                    <div class="text-xs text-slate-400 dark:text-slate-500 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        Total QRIS & Transfer Bank
                    </div>
                </div>

                {{-- Card 4: Total Selisih Kas --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Selisih Kas (Variance)</span>
                            <div class="flex items-center justify-center w-10 h-10 {{ $totalDifference < 0 ? 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400' }} rounded-lg shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                        </div>
                        <div class="mb-1">
                            <h3 class="text-xl sm:text-2xl font-bold tracking-tight {{ $totalDifference < 0 ? 'text-rose-600 dark:text-rose-400' : ($totalDifference > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-800 dark:text-white') }} leading-tight truncate">
                                @if ($totalDifference > 0)
                                    +Rp {{ number_format($totalDifference, 0, ',', '.') }}
                                @elseif ($totalDifference < 0)
                                    -Rp {{ number_format(abs($totalDifference), 0, ',', '.') }}
                                @else
                                    Rp 0 (Seimbang)
                                @endif
                            </h3>
                        </div>
                    </div>
                    <div class="text-xs text-slate-400 dark:text-slate-500 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        {{ $totalDifference < 0 ? '⚠️ Terdeteksi kekurangan kas fisik' : ($totalDifference > 0 ? '💡 Kelebihan uang kas fisik' : '✅ Semua rekap kas seimbang') }}
                    </div>
                </div>
            </div>

            {{-- TABEL RIWAYAT SHIFT KASIR --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-200/80 dark:border-slate-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        📋 Riwayat Shift & Rekap Kasir
                    </h3>
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        Menampilkan {{ $shifts->count() }} dari {{ $shifts->total() }} riwayat shift
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-700 dark:text-slate-200 uppercase font-semibold border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3.5">ID / Status</th>
                                <th class="px-4 py-3.5">Kasir</th>
                                <th class="px-4 py-3.5">Waktu Shift</th>
                                <th class="px-4 py-3.5 text-right">Modal Awal</th>
                                <th class="px-4 py-3.5 text-right">Penjualan Tunai</th>
                                <th class="px-4 py-3.5 text-right">Non-Tunai</th>
                                <th class="px-4 py-3.5 text-right">Total Omset</th>
                                <th class="px-4 py-3.5 text-right">Kas Akhir & Selisih</th>
                                <th class="px-4 py-3.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse ($shifts as $shift)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                    {{-- ID & Status --}}
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="font-bold text-slate-900 dark:text-white">
                                            #SFT-{{ str_pad($shift->id, 5, '0', STR_PAD_LEFT) }}
                                        </div>
                                        <div class="mt-1">
                                            @if ($shift->status === 'open')
                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 animate-pulse">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Shift Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                                    🔒 Ditutup
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Kasir --}}
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ substr($shift->user->name ?? 'K', 0, 1) }}
                                            </div>
                                            <span>{{ $shift->user->name ?? '-' }}</span>
                                        </div>
                                    </td>

                                    {{-- Waktu Shift --}}
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="text-slate-800 dark:text-slate-200">
                                            <span class="font-medium">{{ $shift->start_time ? $shift->start_time->format('d M Y') : '-' }}</span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            {{ $shift->start_time ? $shift->start_time->format('H:i') : '-' }} 
                                            s/d 
                                            {{ $shift->end_time ? $shift->end_time->format('H:i') : '(Aktif)' }}
                                            @if ($shift->end_time)
                                                <span class="text-slate-400">({{ $shift->start_time->diffInHours($shift->end_time) }}j {{ $shift->start_time->diffInMinutes($shift->end_time) % 60 }}m)</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Modal Awal --}}
                                    <td class="px-4 py-3.5 whitespace-nowrap text-right font-medium text-slate-700 dark:text-slate-300">
                                        Rp {{ number_format($shift->starting_cash, 0, ',', '.') }}
                                    </td>

                                    {{-- Penjualan Tunai --}}
                                    <td class="px-4 py-3.5 whitespace-nowrap text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                        Rp {{ number_format($shift->cash_sales, 0, ',', '.') }}
                                    </td>

                                    {{-- Non-Tunai --}}
                                    <td class="px-4 py-3.5 whitespace-nowrap text-right text-slate-600 dark:text-slate-300">
                                        Rp {{ number_format($shift->qris_sales + $shift->transfer_sales, 0, ',', '.') }}
                                    </td>

                                    {{-- Total Omset --}}
                                    <td class="px-4 py-3.5 whitespace-nowrap text-right font-bold text-slate-900 dark:text-white">
                                        Rp {{ number_format($shift->total_sales, 0, ',', '.') }}
                                        <div class="text-[10px] text-slate-400 font-normal">
                                            {{ $shift->total_transactions }} Transaksi
                                        </div>
                                    </td>

                                    {{-- Kas Akhir & Selisih --}}
                                    <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                        @if ($shift->status === 'closed')
                                            <div class="font-bold text-slate-900 dark:text-white">
                                                Fisik: Rp {{ number_format($shift->actual_cash, 0, ',', '.') }}
                                            </div>
                                            <div class="mt-0.5">
                                                @php $diff = (float) $shift->difference; @endphp
                                                @if ($diff == 0)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                                        ✅ Pas (Rp 0)
                                                    </span>
                                                @elseif ($diff < 0)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">
                                                        ⚠️ Kurang Rp {{ number_format(abs($diff), 0, ',', '.') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                        💡 Lebih Rp {{ number_format($diff, 0, ',', '.') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Menunggu Tutup Shift</span>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            {{-- Tombol Detail --}}
                                            <button type="button" wire:click="openDetailModal({{ $shift->id }})"
                                                class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-semibold transition"
                                                title="Lihat Rincian Shift">
                                                🔍 Detail
                                            </button>

                                            {{-- Tombol Cetak Struk Rekap --}}
                                            <a href="{{ route('print.shift', $shift->id) }}" target="_blank"
                                                class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold shadow-xs transition flex items-center gap-1"
                                                title="Cetak Struk Rekap Shift">
                                                🖨️ Cetak
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <span class="text-4xl mb-2">🕒</span>
                                            <p class="font-medium">Belum ada riwayat shift kasir pada periode ini.</p>
                                            <p class="text-xs mt-1">Gunakan tombol Buka Shift di menu POS untuk memulai shift kasir.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($shifts->hasPages())
                    <div class="p-4 border-t border-slate-200/80 dark:border-slate-700/80">
                        {{ $shifts->links() }}
                    </div>
                @endif
            </div>
        </main>
    </div>

    {{-- MODAL DETAIL RINCIAN SHIFT --}}
    @if ($showDetailModal && $selectedShift)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden transform transition-all">
                {{-- Modal Header --}}
                <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                            🕒
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                Detail Shift #SFT-{{ str_pad($selectedShift->id, 5, '0', STR_PAD_LEFT) }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Kasir: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $selectedShift->user->name ?? '-' }}</span>
                            </p>
                        </div>
                    </div>
                    <button wire:click="closeDetailModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto scrollbar-thin">
                    {{-- Waktu & Durasi --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-3 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs">
                        <div>
                            <span class="text-slate-400 block">Waktu Buka Shift:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100">{{ $selectedShift->start_time ? $selectedShift->start_time->format('d/m/Y H:i') : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Waktu Tutup Shift:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100">{{ $selectedShift->end_time ? $selectedShift->end_time->format('d/m/Y H:i') : 'Masih Aktif' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Total Transaksi:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100">{{ $selectedShift->total_transactions }} Nota</span>
                        </div>
                    </div>

                    {{-- Rekap Keuangan & Laci Kas --}}
                    <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4 space-y-2.5">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            💵 Rekapitulasi Kas & Pembayaran
                        </h4>
                        <div class="space-y-1.5 text-xs text-slate-700 dark:text-slate-300">
                            <div class="flex justify-between">
                                <span>Modal Kas Awal Laci:</span>
                                <span class="font-semibold">Rp {{ number_format($selectedShift->starting_cash, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>(+) Total Penjualan Tunai:</span>
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($selectedShift->cash_sales, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between pt-1 border-t border-slate-200 dark:border-slate-700 font-bold">
                                <span>(=) Kas Diharapkan di Laci:</span>
                                <span>Rp {{ number_format($selectedShift->expected_cash, 0, ',', '.') }}</span>
                            </div>
                            @if ($selectedShift->status === 'closed')
                                <div class="flex justify-between font-bold">
                                    <span>Uang Fisik Dihitung:</span>
                                    <span>Rp {{ number_format($selectedShift->actual_cash ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between pt-1 border-t border-slate-200 dark:border-slate-700 font-extrabold text-sm">
                                    <span>Status Selisih Kas:</span>
                                    @php $diff = (float) $selectedShift->difference; @endphp
                                    @if ($diff == 0)
                                        <span class="text-emerald-600 dark:text-emerald-400">✅ Pas (Rp 0)</span>
                                    @elseif ($diff < 0)
                                        <span class="text-rose-600 dark:text-rose-400">⚠️ Kurang Rp {{ number_format(abs($diff), 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-blue-600 dark:text-blue-400">💡 Lebih Rp {{ number_format($diff, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Non-Tunai Breakdown --}}
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/40 rounded-xl">
                            <span class="text-blue-600 dark:text-blue-400 font-medium block">Penjualan QRIS:</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-white">Rp {{ number_format($selectedShift->qris_sales, 0, ',', '.') }}</span>
                        </div>
                        <div class="p-3 bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40 rounded-xl">
                            <span class="text-indigo-600 dark:text-indigo-400 font-medium block">Penjualan Transfer:</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-white">Rp {{ number_format($selectedShift->transfer_sales, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Catatan Shift --}}
                    @if (!empty($selectedShift->notes))
                        <div class="p-3 bg-slate-100 dark:bg-slate-700/50 rounded-xl text-xs text-slate-700 dark:text-slate-300">
                            <span class="font-bold block mb-1">Catatan Penutupan Shift:</span>
                            <p class="italic">{{ $selectedShift->notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="p-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                    <a href="{{ route('print.shift', $selectedShift->id) }}" target="_blank"
                        class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        🖨️ Cetak Struk Rekap Shift
                    </a>
                    <button wire:click="closeDetailModal"
                        class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-semibold transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
