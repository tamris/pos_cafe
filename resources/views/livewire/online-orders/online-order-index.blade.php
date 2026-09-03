<div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-20 md:pb-6 transition-colors duration-300" wire:poll.4s="checkNewOnlineOrders">
    @include('livewire.includes.sidebar')

    <div class="main-content-layout flex flex-col min-h-screen">
        @include('livewire.includes.header', [
            'title' => 'Pesanan Online (Self-Order)', 
            'subtitle' => 'Kelola pesanan mandiri customer yang dibayar via QRIS secara real-time'
        ])

        <main class="p-4 sm:p-6 space-y-6 flex-1 flex flex-col min-h-0">

            {{-- LIVE REAL-TIME SELF-ORDER NOTIFICATION BANNER --}}
            @if($newOnlineOrderAlert)
                <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white p-4 sm:p-5 rounded-2xl shadow-xl border border-emerald-400/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 animate-bounce shrink-0">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-white/20 text-white flex items-center justify-center text-xl shrink-0 shadow-inner">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-white text-emerald-800 uppercase">
                                    Pesanan Online Baru Masuk!
                                </span>
                                <span class="text-xs font-mono font-bold text-emerald-100">{{ $newOnlineOrderAlert['invoice'] }}</span>
                            </div>
                            <div class="text-sm sm:text-base font-extrabold mt-1 text-white">
                                {{ $newOnlineOrderAlert['customer_name'] }}
                                @if(!empty($newOnlineOrderAlert['table_number']))
                                    <span class="text-amber-300 font-black">• Meja {{ $newOnlineOrderAlert['table_number'] }}</span>
                                @else
                                    <span class="text-emerald-200 font-black">• Takeaway</span>
                                @endif
                                <span class="text-emerald-100 font-normal text-xs ml-2">({{ $newOnlineOrderAlert['items_count'] }} menu • Rp {{ number_format($newOnlineOrderAlert['total'], 0, ',', '.') }})</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                        <button type="button" 
                                wire:click="dismissOnlineOrderAlert"
                                class="px-4 py-2 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-bold transition">
                            Tutup
                        </button>
                    </div>
                </div>
            @endif

            {{-- ========================================================================= --}}
            {{-- 0. STORE STATUS & ONLINE ORDER CONTROL BAR (RUSH MODE TOGGLE)             --}}
            {{-- ========================================================================= --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-lg shrink-0 {{ $isOnlineOrderActive ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400' : 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400' }}">
                        @if($isOnlineOrderActive)
                            <i class="fas fa-satellite-dish animate-pulse"></i>
                        @else
                            <i class="fas fa-pause"></i>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base font-heading">
                                Status Pesanan Online
                            </h3>
                            @if($isOnlineOrderActive)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                    Menerima Pesanan
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Dijeda Sementara (Toko Sibuk)
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            @if($isOnlineOrderActive)
                                Pelanggan dapat memesan menu via scan QR meja dan membayar via QRIS.
                            @else
                                Penerimaan pesanan online baru ditutup sementara. Pelanggan hanya dapat melihat menu.
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Switch Toggle Button --}}
                <div class="shrink-0 self-end sm:self-center">
                    <button type="button" 
                            wire:click="toggleOnlineOrderStatus"
                            class="px-4 py-2.5 rounded-xl font-black text-xs transition flex items-center gap-2 cursor-pointer shadow-xs {{ $isOnlineOrderActive ? 'bg-amber-500 hover:bg-amber-600 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                        @if($isOnlineOrderActive)
                            <i class="fas fa-pause-circle text-sm"></i>
                            <span>Jeda Pesanan Online (Toko Padat)</span>
                        @else
                            <i class="fas fa-play-circle text-sm"></i>
                            <span>Buka Kembali Pesanan Online</span>
                        @endif
                    </button>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- 1. STATS SUMMARY CARDS (MATCHING APP DESIGN SYSTEM)                       --}}
            {{-- ========================================================================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
                
                {{-- Card 1: Sedang Disiapkan --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Sedang Disiapkan</span>
                            <div class="flex items-center justify-center w-9 h-9 bg-amber-50 dark:bg-amber-950/40 rounded-xl text-amber-600 dark:text-amber-400 shrink-0">
                                <i class="fas fa-utensils text-sm"></i>
                            </div>
                        </div>
                        <div class="mb-1">
                            <h3 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-tight font-heading">
                                {{ $statsProcessing }} <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">Antrean</span>
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60 text-slate-400">
                        <span>Dalam proses racik dapur/bar</span>
                    </div>
                </div>

                {{-- Card 2: Siap Diambil / Diantar --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Siap Diambil</span>
                            <div class="flex items-center justify-center w-9 h-9 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl text-emerald-600 dark:text-emerald-400 shrink-0">
                                <i class="fas fa-bell text-sm"></i>
                            </div>
                        </div>
                        <div class="mb-1">
                            <h3 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-tight font-heading">
                                {{ $statsReady }} <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">Pesanan</span>
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60 text-slate-400">
                        <span>Menunggu diambil pelanggan</span>
                    </div>
                </div>

                {{-- Card 3: Selesai Hari Ini --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Selesai Hari Ini</span>
                            <div class="flex items-center justify-center w-9 h-9 bg-slate-100 dark:bg-slate-700 rounded-xl text-slate-700 dark:text-slate-300 shrink-0">
                                <i class="fas fa-check-double text-sm"></i>
                            </div>
                        </div>
                        <div class="mb-1">
                            <h3 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-tight font-heading">
                                {{ $statsCompleted }} <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">Struk</span>
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60 text-slate-400">
                        <span>Pesanan online selesai</span>
                    </div>
                </div>

                {{-- Card 4: Omset Online Hari Ini --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Omset QRIS Online</span>
                            <div class="flex items-center justify-center w-9 h-9 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl text-emerald-600 dark:text-emerald-400 shrink-0">
                                <i class="fas fa-qrcode text-sm"></i>
                            </div>
                        </div>
                        <div class="mb-1">
                            <h3 class="text-xl sm:text-2xl 2xl:text-3xl font-black tracking-tight text-slate-900 dark:text-white leading-tight truncate font-heading">
                                Rp {{ number_format($statsRevenueToday, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center text-xs pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-semibold gap-1 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md">
                            ✓ Lunas Digital
                        </span>
                    </div>
                </div>

            </div>

            {{-- ========================================================================= --}}
            {{-- 2. FILTER TABS & SEARCH BAR (CONTAINER CARD)                              --}}
            {{-- ========================================================================= --}}
            <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                
                {{-- Status Filter Tabs --}}
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5">
                    @foreach([
                        'active' => ['label' => 'Antrean Aktif', 'icon' => 'fa-fire', 'count' => $statsProcessing + $statsReady],
                        'processing' => ['label' => 'Disiapkan', 'icon' => 'fa-utensils', 'count' => $statsProcessing],
                        'ready' => ['label' => 'Siap Diambil', 'icon' => 'fa-bell', 'count' => $statsReady],
                        'completed' => ['label' => 'Selesai Hari Ini', 'icon' => 'fa-check', 'count' => $statsCompleted],
                        'all' => ['label' => 'Semua Pesanan', 'icon' => 'fa-list', 'count' => null]
                    ] as $key => $tab)
                        <button type="button" 
                                wire:click="setStatusFilter('{{ $key }}')"
                                class="px-4 py-2.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all flex items-center gap-2 cursor-pointer {{ $statusFilter === $key ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                            <i class="fas {{ $tab['icon'] }} text-xs {{ $statusFilter === $key ? 'text-white' : 'text-slate-400' }}"></i>
                            <span>{{ $tab['label'] }}</span>
                            @if(!is_null($tab['count']) && $tab['count'] > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $statusFilter === $key ? 'bg-white text-emerald-800' : 'bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-200' }}">
                                    {{ $tab['count'] }}
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>

                {{-- Search Box --}}
                <div class="relative w-full md:w-80 shrink-0">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           placeholder="Cari nomor invoice, meja, nama..." 
                           class="w-full pl-9 pr-8 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    @if(!empty($search))
                        <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            <i class="fas fa-times-circle text-xs"></i>
                        </button>
                    @endif
                </div>

            </div>

            {{-- ========================================================================= --}}
            {{-- 3. ORDERS GRID (CLEAN, SPACIOUS & BEAUTIFUL CARDS)                       --}}
            {{-- ========================================================================= --}}
            @if($orders->isEmpty())
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-12 text-center border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700 text-slate-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200 font-heading">Tidak Ada Pesanan Online</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        @if(!empty($search))
                            Tidak ada pesanan online yang cocok dengan pencarian "{{ $search }}".
                        @else
                            Belum ada pesanan aktif pada filter status ini.
                        @endif
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($orders as $order)
                        @php
                            $isPaid = $order->payment_status === 'paid';
                            $status = $order->status;
                        @endphp
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
                            
                            {{-- Header Card --}}
                            <div>
                                <div class="flex items-start justify-between gap-3 pb-3.5 border-b border-slate-100 dark:border-slate-700">
                                    <div class="flex items-center gap-3">
                                        @if($order->order_type === 'dine_in')
                                            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex flex-col items-center justify-center shrink-0">
                                                <span class="text-[9px] font-bold uppercase leading-none">MEJA</span>
                                                <span class="text-base font-black leading-tight font-heading">{{ $order->table_number ?? '-' }}</span>
                                            </div>
                                        @else
                                            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800 flex flex-col items-center justify-center shrink-0">
                                                <i class="fas fa-bag-shopping text-sm"></i>
                                                <span class="text-[8px] font-bold uppercase leading-none mt-0.5">TAKEAWAY</span>
                                            </div>
                                        @endif

                                        <div class="min-w-0">
                                            <h4 class="font-extrabold text-slate-900 dark:text-white text-base font-heading leading-tight truncate">
                                                {{ $order->customer_name }}
                                            </h4>
                                            <div class="flex items-center gap-1.5 text-xs text-slate-400 mt-1 font-mono">
                                                <span>{{ $order->invoice_number }}</span>
                                                <span>•</span>
                                                <span class="text-slate-500 font-sans font-medium">{{ $order->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Status Badge --}}
                                    <div class="shrink-0 text-right">
                                        @if($status === 'processing' || $status === 'pending')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse mr-0.5"></span>
                                                Disiapkan
                                            </span>
                                        @elseif($status === 'ready')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 animate-pulse">
                                                <i class="fas fa-bell text-[10px] mr-0.5"></i>
                                                Siap Diambil
                                            </span>
                                        @elseif($status === 'completed')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                                <i class="fas fa-check text-[10px] text-emerald-500 mr-0.5"></i>
                                                Selesai
                                            </span>
                                        @elseif($status === 'cancelled')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                Dibatalkan
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Order Items List --}}
                                <div class="py-3.5 space-y-2.5 max-h-48 overflow-y-auto pr-1">
                                    @foreach($order->details as $item)
                                        <div class="flex items-start justify-between text-xs sm:text-sm gap-2">
                                            <div class="flex-1 min-w-0">
                                                <span class="font-bold text-slate-900 dark:text-white">
                                                    {{ $item->quantity }}x {{ $item->product?->name ?? 'Menu' }}
                                                </span>
                                                @if(!empty($item->addons))
                                                    <div class="flex flex-wrap gap-1 mt-0.5">
                                                        @foreach($item->addons as $addon)
                                                            <span class="inline-flex items-center text-[10px] font-extrabold px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800">
                                                                + {{ $addon['name'] }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                @if(!empty($item->notes))
                                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                                                        {{ $item->notes }}
                                                    </div>
                                                @endif
                                            </div>
                                            <span class="font-bold text-slate-700 dark:text-slate-300 shrink-0 font-mono text-xs">
                                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Card Footer Total & Action Buttons --}}
                            <div class="pt-3.5 border-t border-slate-100 dark:border-slate-700 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-slate-400 font-medium">Total Tagihan:</span>
                                    <span class="text-base font-black text-slate-900 dark:text-white font-heading">
                                        Rp {{ number_format($order->total, 0, ',', '.') }}
                                    </span>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex items-center gap-2">
                                    {{-- Print Struk Dapur --}}
                                    <button type="button" 
                                            onclick="printKitchenReceipt('{{ $order->invoice_number }}')"
                                            class="inline-flex items-center px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-bold transition-all shadow-2xs active:scale-95 cursor-pointer"
                                            title="Cetak Struk Dapur">
                                        <i class="fas fa-utensils mr-1.5 text-slate-500"></i>
                                        <span>Dapur</span>
                                    </button>

                                    {{-- Print Struk Kasir --}}
                                    <button type="button" 
                                            onclick="window.open('/print-struk/' + '{{ $order->invoice_number }}', '_blank')"
                                            class="inline-flex items-center px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-bold transition-all shadow-2xs active:scale-95 cursor-pointer"
                                            title="Cetak Struk Kasir">
                                        <i class="fas fa-receipt mr-1.5 text-slate-500"></i>
                                        <span>Struk</span>
                                    </button>

                                    {{-- Main Status Progression Button --}}
                                    @if(in_array($status, ['pending', 'processing']))
                                        <button type="button" 
                                                wire:click="updateStatus({{ $order->id }}, 'ready')"
                                                class="flex-1 inline-flex items-center justify-center px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-extrabold shadow-sm transition cursor-pointer">
                                            <i class="fas fa-bell mr-1.5 text-xs"></i>
                                            <span>Pesanan Siap</span>
                                        </button>
                                    @elseif($status === 'ready')
                                        <button type="button" 
                                                wire:click="updateStatus({{ $order->id }}, 'completed')"
                                                class="flex-1 inline-flex items-center justify-center px-3.5 py-2 rounded-lg bg-[#0e382c] hover:bg-[#134e3f] active:scale-95 text-white text-xs font-extrabold shadow-sm transition cursor-pointer">
                                            <i class="fas fa-check mr-1.5 text-xs"></i>
                                            <span>Selesaikan</span>
                                        </button>
                                    @endif

                                    @if($status !== 'completed' && $status !== 'cancelled')
                                        <button type="button" 
                                                wire:click="openCancelModal({{ $order->id }})"
                                                class="inline-flex items-center justify-center w-9 h-9 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/80 rounded-lg text-xs font-bold transition-all shadow-2xs active:scale-95 cursor-pointer shrink-0"
                                                title="Batalkan Pesanan">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

                {{-- Pagination Links --}}
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            @endif

        </main>
    </div>

    {{-- MODAL VOID / CANCEL ORDER --}}
    @if($showCancelModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-3xl p-6 shadow-2xl border border-slate-200 dark:border-slate-700">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 flex items-center justify-center mx-auto mb-3 text-xl shadow-xs">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="text-base font-black text-center text-slate-900 dark:text-white font-heading">
                    Batalkan Pesanan Online?
                </h3>
                <p class="text-xs text-center text-slate-500 dark:text-slate-400 mt-1">
                    Pesanan ini akan dibatalkan dan statusnya di layar pelanggan akan berubah menjadi dibatalkan.
                </p>

                <div class="my-4">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Alasan Pembatalan</label>
                    <textarea wire:model="cancelReasonNotes" 
                              rows="2" 
                              placeholder="Contoh: Menu habis, pelanggan meminta batal, dll..."
                              class="w-full p-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-xs focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <button type="button" 
                            wire:click="closeCancelModal"
                            class="py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold">
                        Batal
                    </button>
                    <button type="button" 
                            wire:click="confirmCancelOrder"
                            class="py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black shadow-md">
                        Ya, Batalkan
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
    // -------------------------------------------------------------
    // DUAL-ENGINE AUDIO NOTIFICATION (BASE64 CHIME + WEBAUDIO SYNTH)
    // -------------------------------------------------------------
    let audioUnlocked = false;

    function unlockAudio() {
        if (!audioUnlocked) {
            audioUnlocked = true;
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                if (ctx.state === 'suspended') {
                    ctx.resume();
                }
            } catch (e) {}
        }
    }

    document.addEventListener('click', unlockAudio, { once: false });
    document.addEventListener('touchstart', unlockAudio, { once: false });

    window.addEventListener('play-online-order-sound', (event) => {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            if (ctx.state === 'suspended') {
                ctx.resume();
            }

            // High Chime 1 (Ding)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(880, ctx.currentTime); // A5
            gain1.gain.setValueAtTime(0.35, ctx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.45);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(ctx.currentTime);
            osc1.stop(ctx.currentTime + 0.45);

            // High Chime 2 (Dong)
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(1174.66, ctx.currentTime + 0.16); // D6
            gain2.gain.setValueAtTime(0.4, ctx.currentTime + 0.16);
            gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.85);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(ctx.currentTime + 0.16);
            osc2.stop(ctx.currentTime + 0.85);

        } catch (e) {
            console.log('Audio playback notice:', e);
        }
    });

    // Kitchen Printing Fallback
    function printKitchenReceipt(invoice) {
        if (!invoice) return;
        window.open('/print-kitchen/' + invoice, '_blank');
    }
</script>
@endpush
