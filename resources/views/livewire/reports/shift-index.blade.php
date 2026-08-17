<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300"
     x-data="{ sidebarOpen: window.innerWidth >= 1280 }"
     @resize.window="sidebarOpen = window.innerWidth >= 1280">

    @include('livewire.includes.sidebar')

    <div class="xl:pl-64 transition-all duration-300 flex flex-col min-h-screen">
        @include('livewire.includes.header', [
            'title' => $isAdmin ? 'Laporan Shift Kasir' : 'Riwayat Shift Saya',
            'subtitle' => $isAdmin ? 'Rekap buka-tutup shift, rekonsiliasi laci kasir, dan deteksi selisih kas' : 'Rekapitulasi riwayat shift kerja, omset penjualan, dan rekonsiliasi kas Anda',
        ])

        <main class="p-4 sm:p-6 space-y-6 flex-1">
            
            {{-- ========================================================================= --}}
            {{-- TAMPILAN KHUSUS KASIR (FOKUS OPERASIONAL SHIFT BERJALAN & LOG TERAKHIR)  --}}
            {{-- ========================================================================= --}}
            @if (!$isAdmin)
                
                {{-- 1. CARD STATUS SHIFT BERJALAN --}}
                @if ($activeShift)
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-4 sm:p-6 space-y-4">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        ⚡ Status Sesi Shift Aktif
                                    </h2>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Shift Berjalan
                                    </span>
                                    <span class="font-mono text-xs font-bold text-slate-500 dark:text-slate-400">
                                        #SFT-{{ str_pad($activeShift->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    Dimulai sejak {{ $activeShift->start_time ? $activeShift->start_time->format('d M Y, H:i') : '-' }} WIB 
                                    ({{ $activeShift->start_time ? $activeShift->start_time->diffForHumans() : '' }})
                                </p>
                            </div>

                            {{-- Tombol Aksi Shift Aktif --}}
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('print.shift', $activeShift->id) }}" target="_blank"
                                    class="inline-flex items-center px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-medium transition-colors active:scale-95">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    Ringkasan Sementara
                                </a>

                                <button type="button" wire:click="openEndShiftModal"
                                    class="inline-flex items-center px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors active:scale-95 gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Tutup Shift
                                </button>
                            </div>
                        </div>

                        {{-- 4 Info Kas Realtime --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 pt-1">
                            
                            {{-- Card 1: Modal Awal --}}
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-all flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Modal Kas Awal</span>
                                        <div class="flex items-center justify-center w-10 h-10 bg-purple-50 dark:bg-purple-900/30 rounded-lg text-purple-600 dark:text-purple-400 shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        </div>
                                    </div>
                                    <div class="mb-1">
                                        <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white leading-tight truncate">
                                            Rp {{ number_format($activeShift->starting_cash, 0, ',', '.') }}
                                        </h3>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-400 dark:text-slate-500 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                                    Uang kembalian awal di laci
                                </div>
                            </div>

                            {{-- Card 2: Penjualan Tunai --}}
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-all flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Penjualan Tunai</span>
                                        <div class="flex items-center justify-center w-10 h-10 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg text-emerald-600 dark:text-emerald-400 shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                    </div>
                                    <div class="mb-1">
                                        <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400 leading-tight truncate">
                                            Rp {{ number_format($activeShift->cash_sales, 0, ',', '.') }}
                                        </h3>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-400 dark:text-slate-500 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                                    {{ $activeShift->transactions->where('payment_method', 'cash')->count() }} transaksi tunai
                                </div>
                            </div>

                            {{-- Card 3: Non-Tunai --}}
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-all flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Penjualan Non-Tunai</span>
                                        <div class="flex items-center justify-center w-10 h-10 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400 shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                        </div>
                                    </div>
                                    <div class="mb-1">
                                        <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-blue-600 dark:text-blue-400 leading-tight truncate">
                                            Rp {{ number_format($activeShift->qris_sales + $activeShift->transfer_sales, 0, ',', '.') }}
                                        </h3>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-400 dark:text-slate-500 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                                    Total QRIS & Transfer Bank
                                </div>
                            </div>

                            {{-- Card 4: Kas Seharusnya di Laci --}}
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-all flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Kas di Laci</span>
                                        <div class="flex items-center justify-center w-10 h-10 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-lg shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                    </div>
                                    <div class="mb-1">
                                        <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-amber-600 dark:text-amber-400 leading-tight truncate">
                                            Rp {{ number_format($activeShift->expected_cash, 0, ',', '.') }}
                                        </h3>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-400 dark:text-slate-500 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                                    Modal Awal + Total Tunai Masuk
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- EMPTY STATE BELUM BUKA SHIFT --}}
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-6 sm:p-8 text-center">
                        <div class="max-w-md mx-auto space-y-3">
                            <span class="text-4xl block">☕</span>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                                Tidak Ada Shift Kasir yang Aktif
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                Buka shift baru dan masukkan modal kas awal untuk mulai melayani transaksi penjualan di POS kasir.
                            </p>
                            <div class="pt-2">
                                <button type="button" wire:click="openStartShiftModal"
                                    class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors active:scale-95 gap-1.5">
                                    ⚡ Buka Shift Kasir Baru
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- 2. TABEL RIWAYAT SHIFT TERAKHIR SAYA (MAX 5 LOG DATA) --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                                Riwayat Shift Terakhir Saya
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                Daftar 5 log sesi shift terakhir dan rekonsiliasi kas Anda
                            </p>
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">
                            Menampilkan {{ $shifts->count() }} dari {{ $shifts->total() }} riwayat shift
                        </div>
                    </div>

                    <div class="overflow-x-auto scrollbar-thin">
                        <table class="w-full text-left border-collapse min-w-[850px]">
                            <thead class="bg-slate-50 dark:bg-slate-700/50 text-[11px] uppercase text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="px-5 sm:px-6 py-3.5">ID / Status</th>
                                    <th class="px-5 sm:px-6 py-3.5">Waktu Shift</th>
                                    <th class="px-5 sm:px-6 py-3.5 text-right">Modal Awal</th>
                                    <th class="px-5 sm:px-6 py-3.5 text-right">Penjualan Tunai</th>
                                    <th class="px-5 sm:px-6 py-3.5 text-right">Non-Tunai</th>
                                    <th class="px-5 sm:px-6 py-3.5 text-right">Total Omset</th>
                                    <th class="px-5 sm:px-6 py-3.5 text-right">Kas Akhir & Selisih</th>
                                    <th class="px-5 sm:px-6 py-3.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-xs sm:text-sm">
                                @forelse ($shifts as $shift)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                                        
                                        {{-- ID & Status --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap">
                                            <div class="font-bold text-slate-900 dark:text-white font-mono">
                                                #SFT-{{ str_pad($shift->id, 5, '0', STR_PAD_LEFT) }}
                                            </div>
                                            <div class="mt-1">
                                                @if ($shift->status === 'open')
                                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                        Shift Aktif
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                                        🔒 Ditutup
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Waktu Shift --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap">
                                            <p class="font-medium text-slate-900 dark:text-white">
                                                {{ $shift->start_time ? $shift->start_time->format('d M Y') : '-' }}
                                            </p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                {{ $shift->start_time ? $shift->start_time->format('H:i') : '-' }} 
                                                s/d 
                                                {{ $shift->end_time ? $shift->end_time->format('H:i') : '(Aktif)' }}
                                                @if ($shift->end_time && $shift->start_time)
                                                    @php
                                                        $totalMinutes = (int) $shift->start_time->diffInMinutes($shift->end_time);
                                                        $hours = intdiv($totalMinutes, 60);
                                                        $minutes = $totalMinutes % 60;
                                                    @endphp
                                                    <span class="text-slate-400">
                                                        ({{ $hours > 0 ? $hours.'j ' : '' }}{{ $minutes }}m)
                                                    </span>
                                                @endif
                                            </p>
                                        </td>

                                        {{-- Modal Awal --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap text-right font-medium text-slate-700 dark:text-slate-300">
                                            Rp {{ number_format($shift->starting_cash, 0, ',', '.') }}
                                        </td>

                                        {{-- Penjualan Tunai --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap text-right font-bold text-emerald-600 dark:text-emerald-400">
                                            Rp {{ number_format($shift->cash_sales, 0, ',', '.') }}
                                        </td>

                                        {{-- Non-Tunai --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap text-right text-slate-600 dark:text-slate-300">
                                            Rp {{ number_format($shift->qris_sales + $shift->transfer_sales, 0, ',', '.') }}
                                        </td>

                                        {{-- Total Omset --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap text-right">
                                            <div class="font-bold text-slate-900 dark:text-white">
                                                Rp {{ number_format($shift->total_sales, 0, ',', '.') }}
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-normal">
                                                {{ $shift->total_transactions }} Transaksi
                                            </div>
                                        </td>

                                        {{-- Kas Akhir & Selisih --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap text-right">
                                            @if ($shift->status === 'closed')
                                                <div class="font-bold text-slate-900 dark:text-white">
                                                    Fisik: Rp {{ number_format($shift->actual_cash, 0, ',', '.') }}
                                                </div>
                                                <div class="mt-1">
                                                    @php $diff = (float) $shift->difference; @endphp
                                                    @if ($diff == 0)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                            ✅ Pas (Rp 0)
                                                        </span>
                                                    @elseif ($diff < 0)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                            ⚠️ Kurang Rp {{ number_format(abs($diff), 0, ',', '.') }}
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                            💡 Lebih Rp {{ number_format($diff, 0, ',', '.') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-xs text-slate-400 italic">Menunggu Tutup Shift</span>
                                            @endif
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap text-right space-x-1.5 shrink-0">
                                            <button type="button" wire:click="openDetailModal({{ $shift->id }})"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-medium transition-colors active:scale-95">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                Detail
                                            </button>

                                            <a href="{{ route('print.shift', $shift->id) }}" target="_blank"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-medium transition-colors active:scale-95">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                Cetak
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <span class="text-4xl mb-2">🕒</span>
                                                <p class="font-medium text-slate-600 dark:text-slate-300 text-sm">Belum ada riwayat shift yang tersimpan.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($shifts->hasPages())
                        <div class="px-5 sm:px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                            {{ $shifts->links() }}
                        </div>
                    @endif
                </div>

            {{-- ========================================================================= --}}
            {{-- TAMPILAN KHUSUS ADMIN (AUDIT & ANALYTICS DASHBOARD - 100% STYLE USER)    --}}
            {{-- ========================================================================= --}}
            @else
                
                {{-- FILTER & CONTROLS SECTION --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-4 sm:p-6 space-y-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                🕒 Filter Laporan Shift
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Filter riwayat shift berdasarkan tanggal, kasir, dan status shift
                            </p>
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
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-all flex flex-col justify-between">
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
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-all flex flex-col justify-between">
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
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-all flex flex-col justify-between">
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
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Selisih Kas</span>
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

                {{-- TABEL RIWAYAT SHIFT KASIR (ADMIN) --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                                Riwayat Shift & Rekap Kasir
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                Daftar log sesi kasir, modal awal, serta selisih fisik laci
                            </p>
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">
                            Menampilkan {{ $shifts->count() }} dari {{ $shifts->total() }} riwayat shift
                        </div>
                    </div>

                    <div class="overflow-x-auto scrollbar-thin">
                        <table class="w-full text-left border-collapse min-w-[950px]">
                            <thead class="bg-slate-50 dark:bg-slate-700/50 text-[11px] uppercase text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="px-5 sm:px-6 py-3.5">ID / Status</th>
                                    <th class="px-5 sm:px-6 py-3.5">Kasir</th>
                                    <th class="px-5 sm:px-6 py-3.5">Waktu Shift</th>
                                    <th class="px-5 sm:px-6 py-3.5 text-right">Modal Awal</th>
                                    <th class="px-5 sm:px-6 py-3.5 text-right">Penjualan Tunai</th>
                                    <th class="px-5 sm:px-6 py-3.5 text-right">Non-Tunai</th>
                                    <th class="px-5 sm:px-6 py-3.5 text-right">Total Omset</th>
                                    <th class="px-5 sm:px-6 py-3.5 text-right">Kas Akhir & Selisih</th>
                                    <th class="px-5 sm:px-6 py-3.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-xs sm:text-sm">
                                @forelse ($shifts as $shift)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                                        
                                        {{-- ID & Status --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap">
                                            <div class="font-bold text-slate-900 dark:text-white font-mono">
                                                #SFT-{{ str_pad($shift->id, 5, '0', STR_PAD_LEFT) }}
                                            </div>
                                            <div class="mt-1">
                                                @if ($shift->status === 'open')
                                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                        Shift Aktif
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                                        🔒 Ditutup
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Kasir --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap">
                                            <div class="font-semibold text-slate-900 dark:text-white flex items-center space-x-2.5">
                                                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 flex items-center justify-center font-bold text-xs shrink-0">
                                                    {{ substr($shift->user->name ?? 'K', 0, 1) }}
                                                </div>
                                                <span>{{ $shift->user->name ?? '-' }}</span>
                                            </div>
                                        </td>

                                        {{-- Waktu Shift --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap">
                                            <p class="font-medium text-slate-900 dark:text-white">
                                                {{ $shift->start_time ? $shift->start_time->format('d M Y') : '-' }}
                                            </p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                {{ $shift->start_time ? $shift->start_time->format('H:i') : '-' }} 
                                                s/d 
                                                {{ $shift->end_time ? $shift->end_time->format('H:i') : '(Aktif)' }}
                                                @if ($shift->end_time && $shift->start_time)
                                                    @php
                                                        $totalMinutes = (int) $shift->start_time->diffInMinutes($shift->end_time);
                                                        $hours = intdiv($totalMinutes, 60);
                                                        $minutes = $totalMinutes % 60;
                                                    @endphp
                                                    <span class="text-slate-400">
                                                        ({{ $hours > 0 ? $hours.'j ' : '' }}{{ $minutes }}m)
                                                    </span>
                                                @endif
                                            </p>
                                        </td>

                                        {{-- Modal Awal --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap text-right font-medium text-slate-700 dark:text-slate-300">
                                            Rp {{ number_format($shift->starting_cash, 0, ',', '.') }}
                                        </td>

                                        {{-- Penjualan Tunai --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap text-right font-bold text-emerald-600 dark:text-emerald-400">
                                            Rp {{ number_format($shift->cash_sales, 0, ',', '.') }}
                                        </td>

                                        {{-- Non-Tunai --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap text-right text-slate-600 dark:text-slate-300">
                                            Rp {{ number_format($shift->qris_sales + $shift->transfer_sales, 0, ',', '.') }}
                                        </td>

                                        {{-- Total Omset --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap text-right">
                                            <div class="font-bold text-slate-900 dark:text-white">
                                                Rp {{ number_format($shift->total_sales, 0, ',', '.') }}
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-normal">
                                                {{ $shift->total_transactions }} Transaksi
                                            </div>
                                        </td>

                                        {{-- Kas Akhir & Selisih --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap text-right">
                                            @if ($shift->status === 'closed')
                                                <div class="font-bold text-slate-900 dark:text-white">
                                                    Fisik: Rp {{ number_format($shift->actual_cash, 0, ',', '.') }}
                                                </div>
                                                <div class="mt-1">
                                                    @php $diff = (float) $shift->difference; @endphp
                                                    @if ($diff == 0)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                            ✅ Pas (Rp 0)
                                                        </span>
                                                    @elseif ($diff < 0)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                            ⚠️ Kurang Rp {{ number_format(abs($diff), 0, ',', '.') }}
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                            💡 Lebih Rp {{ number_format($diff, 0, ',', '.') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-xs text-slate-400 italic">Menunggu Tutup Shift</span>
                                            @endif
                                        </td>

                                        {{-- Aksi (Clean Outline Style) --}}
                                        <td class="px-5 sm:px-6 py-4 whitespace-nowrap text-right space-x-1.5 shrink-0">
                                            <button type="button" wire:click="openDetailModal({{ $shift->id }})"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-medium transition-colors active:scale-95">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                Detail
                                            </button>

                                            <a href="{{ route('print.shift', $shift->id) }}" target="_blank"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-medium transition-colors active:scale-95">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                Cetak
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <span class="text-4xl mb-2">🕒</span>
                                                <p class="font-medium text-slate-600 dark:text-slate-300 text-sm">Belum ada riwayat shift kasir pada periode ini.</p>
                                                <p class="text-xs text-slate-400 mt-1">Gunakan tombol Buka Shift di menu POS untuk memulai shift kasir.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if ($shifts->hasPages())
                        <div class="px-5 sm:px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                            {{ $shifts->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </main>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL BUKA SHIFT KASIR (STYLE KONSISTEN)                                  --}}
    {{-- ========================================================================= --}}
    @if ($showStartShiftModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closeStartShiftModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle max-w-md w-full border border-slate-200 dark:border-slate-700">
                    
                    {{-- Header Modal --}}
                    <div class="bg-slate-900 dark:bg-slate-700 text-white px-5 sm:px-6 py-4 border-b border-slate-800 dark:border-slate-600 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold">Buka Shift Kasir</h3>
                            <p class="text-xs opacity-90">Kasir: <span class="font-semibold">{{ auth()->user()->name }}</span></p>
                        </div>
                        <button type="button" wire:click="closeStartShiftModal" class="text-white hover:opacity-80 transition-opacity p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    {{-- Body Modal --}}
                    <div class="p-5 sm:p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Modal Kas Awal di Laci (Uang Kembalian):
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-bold text-slate-400">Rp</span>
                                <input type="text" wire:model.live="formattedStartingCash" placeholder="0" autofocus
                                    class="w-full pl-10 pr-4 py-2 text-base font-bold border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            </div>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Masukkan uang kas fisik di laci saat memulai shift.</p>
                        </div>

                        {{-- Quick Nominal Presets --}}
                        <div>
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block mb-1.5">Pilihan Cepat Nominal:</span>
                            <div class="grid grid-cols-4 gap-2">
                                <button type="button" wire:click="setStartingCashPreset(50000)" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-xs font-bold rounded-lg transition text-slate-700 dark:text-slate-200">
                                    50 Rb
                                </button>
                                <button type="button" wire:click="setStartingCashPreset(100000)" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-xs font-bold rounded-lg transition text-slate-700 dark:text-slate-200">
                                    100 Rb
                                </button>
                                <button type="button" wire:click="setStartingCashPreset(200000)" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-xs font-bold rounded-lg transition text-slate-700 dark:text-slate-200">
                                    200 Rb
                                </button>
                                <button type="button" wire:click="setStartingCashPreset(500000)" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-xs font-bold rounded-lg transition text-slate-700 dark:text-slate-200">
                                    500 Rb
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-700/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
                        <button type="button" wire:click="closeStartShiftModal" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-semibold transition-colors">
                            Batal
                        </button>
                        <button type="button" wire:click="startShift" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors flex items-center gap-1.5">
                            ⚡ Mulai Buka Shift
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL TUTUP SHIFT & REKAP KASIR (STYLE KONSISTEN)                         --}}
    {{-- ========================================================================= --}}
    @if ($showEndShiftModal && $activeShift)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closeEndShiftModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle max-w-lg w-full border border-slate-200 dark:border-slate-700">
                    
                    {{-- Header Modal --}}
                    <div class="bg-slate-900 dark:bg-slate-700 text-white px-5 sm:px-6 py-4 border-b border-slate-800 dark:border-slate-600 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold">Tutup Shift & Rekonsiliasi Kas</h3>
                            <p class="text-xs opacity-90">
                                Shift #SFT-{{ str_pad($activeShift->id, 5, '0', STR_PAD_LEFT) }} • Kasir: <span class="font-semibold">{{ $activeShift->user->name ?? auth()->user()->name }}</span>
                            </p>
                        </div>
                        <button type="button" wire:click="closeEndShiftModal" class="text-white hover:opacity-80 transition-opacity p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    {{-- Body Modal --}}
                    <div class="p-5 sm:p-6 space-y-4 max-h-[75vh] overflow-y-auto scrollbar-thin">
                        {{-- Rekap Ringkas Sistem --}}
                        <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4 space-y-2 text-xs">
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span>Modal Kas Awal Laci:</span>
                                <span class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format($activeShift->starting_cash, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span>(+) Total Penjualan Tunai:</span>
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($activeShift->cash_sales, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-slate-200 dark:border-slate-700 font-bold text-sm">
                                <span class="text-slate-700 dark:text-slate-300">(=) Kas Diharapkan di Laci:</span>
                                <span class="text-amber-600 dark:text-amber-400">Rp {{ number_format($activeShift->expected_cash, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-slate-500 pt-1 text-[11px]">
                                <span>Penjualan Non-Tunai (QRIS & Transfer):</span>
                                <span>Rp {{ number_format($activeShift->qris_sales + $activeShift->transfer_sales, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Input Uang Fisik Aktual --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Hitung Uang Fisik Aktual di Laci Kasir:
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-bold text-slate-400">Rp</span>
                                <input type="text" wire:model.live="formattedActualCash" placeholder="0" autofocus
                                    class="w-full pl-10 pr-4 py-2 text-base font-bold border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            </div>
                        </div>

                        {{-- Status Selisih Real-time Preview --}}
                        <div class="p-3.5 rounded-xl border {{ $shiftDifference == 0 ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300' : ($shiftDifference < 0 ? 'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300' : 'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300') }}">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider">Hasil Selisih Kas:</span>
                                <span class="text-sm font-extrabold">
                                    @if ($shiftDifference == 0)
                                        ✅ Pas (Rp 0)
                                    @elseif ($shiftDifference < 0)
                                        ⚠️ Kurang -Rp {{ number_format(abs($shiftDifference), 0, ',', '.') }}
                                    @else
                                        💡 Lebih +Rp {{ number_format($shiftDifference, 0, ',', '.') }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- Catatan Penutupan Shift --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                                Catatan Shift (Opsional):
                            </label>
                            <textarea wire:model="shiftNotes" rows="2" placeholder="Contoh: Titipan uang receh aman, selisih pas..."
                                class="w-full px-3 py-2 text-xs border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white"></textarea>
                        </div>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-700/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
                        <button type="button" wire:click="closeEndShiftModal" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-semibold transition-colors">
                            Batal
                        </button>
                        <button type="button" wire:click="endShift" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors flex items-center gap-1.5">
                            🔒 Tutup Shift & Cetak Rekap
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL DETAIL RINCIAN SHIFT (STYLE ASLI 100%)                              --}}
    {{-- ========================================================================= --}}
    @if ($showDetailModal && $selectedShift)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closeDetailModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle max-w-2xl w-full border border-slate-200 dark:border-slate-700">
                    
                    {{-- Modal Header --}}
                    <div class="bg-slate-900 dark:bg-slate-700 text-white px-5 sm:px-6 py-4 border-b border-slate-800 dark:border-slate-600 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold">
                                Detail Shift #SFT-{{ str_pad($selectedShift->id, 5, '0', STR_PAD_LEFT) }}
                            </h3>
                            <p class="text-xs opacity-90">
                                Kasir: <span class="font-semibold">{{ $selectedShift->user->name ?? '-' }}</span>
                            </p>
                        </div>
                        <button type="button" wire:click="closeDetailModal" class="text-white hover:opacity-80 transition-opacity p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-5 sm:p-6 space-y-5 max-h-[75vh] overflow-y-auto scrollbar-thin">
                        {{-- Waktu & Durasi --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-3.5 bg-slate-50 dark:bg-slate-900/60 rounded-xl text-xs border border-slate-200 dark:border-slate-700">
                            <div>
                                <span class="text-slate-400 block">Waktu Buka Shift:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-100 mt-0.5 block">{{ $selectedShift->start_time ? $selectedShift->start_time->format('d/m/Y H:i') : '-' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Waktu Tutup Shift:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-100 mt-0.5 block">{{ $selectedShift->end_time ? $selectedShift->end_time->format('d/m/Y H:i') : 'Masih Aktif' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Total Transaksi:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-100 mt-0.5 block">{{ $selectedShift->total_transactions }} Nota</span>
                            </div>
                        </div>

                        {{-- Rekap Keuangan & Laci Kas --}}
                        <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4 space-y-2.5">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                💵 Rekapitulasi Kas & Pembayaran
                            </h4>
                            <div class="space-y-1.5 text-xs sm:text-sm text-slate-700 dark:text-slate-300">
                                <div class="flex justify-between">
                                    <span class="text-slate-500 dark:text-slate-400">Modal Kas Awal Laci:</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format($selectedShift->starting_cash, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500 dark:text-slate-400">(+) Total Penjualan Tunai:</span>
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($selectedShift->cash_sales, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-slate-200 dark:border-slate-700 font-bold">
                                    <span class="text-slate-700 dark:text-slate-300">(=) Kas Diharapkan di Laci:</span>
                                    <span class="text-slate-900 dark:text-white">Rp {{ number_format($selectedShift->expected_cash, 0, ',', '.') }}</span>
                                </div>
                                @if ($selectedShift->status === 'closed')
                                    <div class="flex justify-between font-bold">
                                        <span class="text-slate-700 dark:text-slate-300">Uang Fisik Dihitung:</span>
                                        <span class="text-slate-900 dark:text-white">Rp {{ number_format($selectedShift->actual_cash ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center pt-2 border-t border-slate-200 dark:border-slate-700 font-extrabold text-xs sm:text-sm">
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
                            <div class="p-3.5 bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/40 rounded-xl">
                                <span class="text-blue-600 dark:text-blue-400 font-medium block">Penjualan QRIS:</span>
                                <span class="text-sm sm:text-base font-bold text-slate-800 dark:text-white mt-0.5 block">Rp {{ number_format($selectedShift->qris_sales, 0, ',', '.') }}</span>
                            </div>
                            <div class="p-3.5 bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40 rounded-xl">
                                <span class="text-indigo-600 dark:text-indigo-400 font-medium block">Penjualan Transfer:</span>
                                <span class="text-sm sm:text-base font-bold text-slate-800 dark:text-white mt-0.5 block">Rp {{ number_format($selectedShift->transfer_sales, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Catatan Shift --}}
                        @if (!empty($selectedShift->notes))
                            <div class="p-3.5 bg-slate-50 dark:bg-slate-700/40 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300">
                                <span class="font-bold block mb-1">Catatan Penutupan Shift:</span>
                                <p class="italic">{{ $selectedShift->notes }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Modal Footer --}}
                    <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-700/50 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                        <a href="{{ route('print.shift', $selectedShift->id) }}" target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-slate-900 dark:bg-blue-600 hover:bg-slate-800 dark:hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors gap-1.5 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Cetak Struk Rekap
                        </a>
                        <button type="button" wire:click="closeDetailModal"
                            class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-semibold transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>