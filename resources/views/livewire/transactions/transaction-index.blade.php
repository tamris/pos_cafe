<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    @include('livewire.includes.sidebar')

    <div class="lg:pl-64">
        @include('livewire.includes.header', [
            'title' => 'Transaksi',
            'subtitle' => 'Riwayat semua transaksi',
        ])

        <main class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-full text-orange-600 dark:text-orange-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Pendapatan Hari Ini</p>
                            <h3 class="text-2xl font-bold text-orange-600 dark:text-orange-400">Rp {{ number_format($todayOmset, 0, ',', '.') }}</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Real-time hari ini</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-full text-blue-600 dark:text-blue-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Total Pendapatan</p>
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Rp {{ number_format($filteredTotal, 0, ',', '.') }}</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Sesuai hasil pencarian</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-full text-purple-600 dark:text-purple-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Transaksi</p>
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $filteredCount }}</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Data ditemukan</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                
                <div class="p-6 border-b border-slate-200 dark:border-slate-700 space-y-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Riwayat Transaksi</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Daftar semua transaksi yang telah dilakukan</p>
                    </div>
                    
                    <div class="flex flex-col md:flex-row gap-3">
                        <div class="flex-1 relative">
                            <input type="text" wire:model.live.debounce.300ms="search" 
                                class="w-full pl-10 pr-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500"
                                placeholder="Cari invoice...">
                            <svg class="w-5 h-5 text-slate-400 dark:text-slate-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <div class="flex gap-3">
                            <input type="date" wire:model.live="dateFrom" class="px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer [color-scheme:light] dark:[color-scheme:dark]">
                            <input type="date" wire:model.live="dateTo" class="px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer [color-scheme:light] dark:[color-scheme:dark]">
                            <select wire:model.live="paymentMethodFilter" class="px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer">
                                <option value="">Semua Metode</option>
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs uppercase text-slate-500 dark:text-slate-400 font-semibold">
                            <tr>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">Invoice</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">Tanggal</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">Kasir</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">Tipe / Info</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">Total</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">Metode</th>
                                <th class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 text-right">Aksi</th>
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
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $transaction->created_at->format('H:i') }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                        {{ $transaction->user->name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-xs font-semibold text-slate-900 dark:text-white capitalize block">{{ str_replace('_', ' ', $transaction->order_type ?? 'dine_in') }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            @if(($transaction->order_type ?? 'dine_in') === 'dine_in')
                                                {{ $transaction->table_number ? 'Meja: '.$transaction->table_number : '-' }}
                                            @else
                                                {{ $transaction->customer_name ? 'Cust: '.$transaction->customer_name : '-' }}
                                            @endif
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-800 dark:text-white">
                                        Rp {{ number_format($transaction->total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($transaction->payment_method == 'cash')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 capitalize">
                                                {{ $transaction->payment_method }}
                                            </span>
                                        @elseif($transaction->payment_method == 'transfer')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-100 dark:border-green-800 capitalize">
                                                {{ $transaction->payment_method }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800 capitalize">
                                                {{ $transaction->payment_method }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <a href="{{ route('print.struk', $transaction->invoice_number) }}" target="_blank"
                                            class="inline-flex items-center px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-medium transition-colors">
                                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                            Cetak
                                        </a>
                                        <button wire:click="viewDetail({{ $transaction->id }})"
                                            class="inline-flex items-center px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-medium transition-colors">
                                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <p class="text-slate-500 dark:text-slate-400 font-medium">Tidak ada transaksi ditemukan</p>
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

    @if ($showDetailModal && $selectedTransaction)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900 bg-opacity-50 backdrop-blur-sm" wire:click="closeDetailModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-slate-800 px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Detail Transaksi</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $selectedTransaction->invoice_number }}</p>
                        </div>
                        <button type="button" wire:click="closeDetailModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                    </div>

                    <div class="p-6 bg-white dark:bg-slate-800 space-y-6">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-slate-500 dark:text-slate-400">Tanggal</p>
                                <p class="font-medium text-slate-900 dark:text-white">{{ $selectedTransaction->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 dark:text-slate-400">Kasir</p>
                                <p class="font-medium text-slate-900 dark:text-white">{{ $selectedTransaction->user->name }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500 dark:text-slate-400">Tipe Pesanan</p>
                                <p class="font-bold text-amber-600 dark:text-amber-400 uppercase text-xs">{{ str_replace('_', ' ', $selectedTransaction->order_type ?? 'dine_in') }}</p>
                            </div>
                            <div>
                                @if(($selectedTransaction->order_type ?? 'dine_in') === 'dine_in')
                                    <p class="text-slate-500 dark:text-slate-400">Nomor Meja</p>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $selectedTransaction->table_number ?: '-' }}</p>
                                @else
                                    <p class="text-slate-500 dark:text-slate-400">Nama Pelanggan</p>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $selectedTransaction->customer_name ?: '-' }}</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-slate-500 dark:text-slate-400">Metode Bayar</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-600 capitalize">
                                    {{ $selectedTransaction->payment_method }}
                                </span>
                            </div>
                        </div>

                        <div class="border rounded-lg border-slate-200 dark:border-slate-700 overflow-hidden">
                            <div class="bg-slate-50 dark:bg-slate-700/50 px-4 py-2 border-b border-slate-200 dark:border-slate-700 flex justify-between text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                <span>Produk</span>
                                <span>Total</span>
                            </div>
                            <div class="divide-y divide-slate-100 dark:divide-slate-700 max-h-48 overflow-y-auto">
                                @foreach($selectedTransaction->details as $detail)
                                    <div class="px-4 py-3 flex justify-between text-sm hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-white">{{ $detail->product->name }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $detail->quantity }} x Rp {{ number_format($detail->price, 0, ',', '.') }}</p>
                                        </div>
                                        <span class="font-medium text-slate-900 dark:text-white">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-700/30 rounded-lg p-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">Subtotal</span>
                                <span class="font-medium text-slate-900 dark:text-white">Rp {{ number_format($selectedTransaction->subtotal, 0, ',', '.') }}</span>
                            </div>
                            @if ($selectedTransaction->discount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500 dark:text-slate-400">Diskon</span>
                                    <span class="font-medium text-red-600 dark:text-red-400">- Rp {{ number_format($selectedTransaction->discount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            @if ($selectedTransaction->tax > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500 dark:text-slate-400">Pajak</span>
                                    <span class="font-medium text-slate-900 dark:text-white">+ Rp {{ number_format($selectedTransaction->tax, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between items-center pt-2 border-t border-slate-200 dark:border-slate-700">
                                <span class="text-base font-bold text-slate-700 dark:text-slate-300">Total</span>
                                <span class="text-xl font-extrabold text-slate-900 dark:text-white">Rp {{ number_format($selectedTransaction->total, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm pt-1">
                                <span class="text-slate-500 dark:text-slate-400">Dibayar</span>
                                <span class="font-medium text-slate-900 dark:text-white">Rp {{ number_format($selectedTransaction->paid, 0, ',', '.') }}</span>
                            </div>
                            @if ($selectedTransaction->change > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500 dark:text-slate-400">Kembalian</span>
                                    <span class="font-medium text-green-600 dark:text-green-400">Rp {{ number_format($selectedTransaction->change, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-4 flex justify-end border-t border-slate-200 dark:border-slate-700">
                        <button type="button" wire:click="closeDetailModal"
                            class="px-4 py-2.5 bg-slate-900 dark:bg-blue-600 text-white rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors font-medium shadow-lg">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>