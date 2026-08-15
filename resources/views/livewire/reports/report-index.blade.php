<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    @include('livewire.includes.sidebar')

    <div class="lg:pl-64">
        @include('livewire.includes.header', [
            'title' => 'Laporan Ringkasan Penjualan',
            'subtitle' => 'Laporan transaksi keuangan, omset, profit, dan export Excel',
        ])

        <main class="p-6">
            
            {{-- FILTER PERIODE SECTION --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow mb-6 transition-colors p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        📅 Filter Periode Laporan
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pilih tanggal awal dan akhir transaksi yang ingin diringkas</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="dateFrom" class="text-xs font-semibold text-slate-600 dark:text-slate-300">Dari:</label>
                        <input type="date" wire:model.live="dateFrom"
                            class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs cursor-pointer [color-scheme:light] dark:[color-scheme:dark]">
                    </div>

                    <div class="flex items-center gap-2">
                        <label for="dateTo" class="text-xs font-semibold text-slate-600 dark:text-slate-300">Sampai:</label>
                        <input type="date" wire:model.live="dateTo"
                            class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs cursor-pointer [color-scheme:light] dark:[color-scheme:dark]">
                    </div>
                </div>
            </div>

            {{-- SUMMARY KPI CARDS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                {{-- Total Pendapatan / Omset --}}
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-full text-orange-600 dark:text-orange-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Total Omset Kotor</p>
                            <h3 class="text-2xl font-bold text-orange-600 dark:text-orange-400">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Periode terpilih</p>
                        </div>
                    </div>
                </div>

                {{-- Total Profit Netto --}}
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-full text-emerald-600 dark:text-emerald-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Total Profit Bersih</p>
                            <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalProfit, 0, ',', '.') }}</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Keuntungan bersih</p>
                        </div>
                    </div>
                </div>

                {{-- Total Transaksi --}}
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-full text-purple-600 dark:text-purple-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 022 2h2a2 2 0 022-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Total Transaksi</p>
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($totalTransactions, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">Nota</span></h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Jumlah transaksi</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABEL LAPORAN PENJUALAN --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Ringkasan Transaksi Penjualan</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Daftar transaksi nota kasir lengkap dengan informasi tipe pesanan, meja, & profit</p>
                    </div>
                    <button wire:click="exportExcel" class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-medium transition-colors shadow-sm gap-2 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Ekspor Laporan Excel
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs uppercase text-slate-500 dark:text-slate-400 font-semibold">
                            <tr>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">Invoice</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">Tanggal</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">Kasir</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">Tipe Pesanan / Info</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 text-right">Profit Netto</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 text-right">Total Tagihan</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 text-center">Metode Bayar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($transactions as $transaction)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-700 dark:text-slate-300 font-mono text-sm">
                                        {{ $transaction->invoice_number }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $transaction->created_at->format('d M Y') }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $transaction->created_at->format('H:i') }} WIB</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                        {{ $transaction->user->name ?? 'Admin' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-slate-900 dark:text-white">
                                            {{ ($transaction->order_type ?? 'dine_in') === 'dine_in' ? 'Makan di Tempat' : (($transaction->order_type ?? '') === 'take_away' ? 'Bawa Pulang' : 'Pesan Antar') }}
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            @if(($transaction->order_type ?? 'dine_in') === 'dine_in')
                                                {{ $transaction->table_number ? 'Meja: '.$transaction->table_number : '-' }}
                                            @else
                                                {{ $transaction->customer_name ? 'Pelanggan: '.$transaction->customer_name : '-' }}
                                            @endif
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-emerald-600 dark:text-emerald-400 text-right text-sm">
                                        + Rp {{ number_format($transaction->details->sum('profit'), 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-800 dark:text-white text-right text-sm">
                                        Rp {{ number_format($transaction->total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
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
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <p class="text-slate-500 dark:text-slate-400 font-medium">Tidak ada data transaksi pada periode ini</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    {{ $transactions->links() }}
                </div>
            </div>
        </main>
    </div>
</div>