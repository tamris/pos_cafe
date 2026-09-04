<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    @include('livewire.includes.sidebar')

    <div class="main-content-layout flex flex-col min-h-screen">
        @include('livewire.includes.header', [
            'title' => 'Add-ons & Topping', 
            'subtitle' => 'Kelola opsi tambahan, takaran rasa, dan topping menu cafe'
        ])

        <main class="p-4 sm:p-6 space-y-6 flex-1">
            
            {{-- Flash Message Success --}}
            @if (session()->has('message'))
                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4 animate-fade-in">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-emerald-800 dark:text-emerald-300 font-medium text-xs sm:text-sm">{{ session('message') }}</p>
                    </div>
                </div>
            @endif

            {{-- Flash Message Error --}}
            @if (session()->has('error'))
                <div class="bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl p-4 animate-fade-in">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <p class="text-rose-800 dark:text-rose-300 font-medium text-xs sm:text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Main Table Card --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors overflow-hidden">
                
                {{-- Header Filter & Action Box --}}
                <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Daftar Add-on & Topping</h2>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelola data modifier menu, keterkaitan kategori, HPP, serta harga jual</p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3">
                            {{-- Filter Kategori --}}
                            <div class="relative group w-full sm:w-auto">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 group-hover:text-blue-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                                </div>
                                <select wire:model.live="categoryFilter" 
                                    class="appearance-none w-full sm:w-48 pl-10 pr-10 py-2 sm:py-2.5 text-xs sm:text-sm font-medium border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer transition-all outline-none">
                                    <option value="">Semua Kategori</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"></path></svg>
                                </div>
                            </div>

                            {{-- Search Input --}}
                            <div class="relative w-full sm:w-60">
                                <input type="text" wire:model.live.debounce.300ms="search"
                                    placeholder="Cari add-on..."
                                    class="w-full pl-10 pr-4 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500">
                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5 sm:top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            {{-- Tombol Tambah Add-on (Admin Only) --}}
                            @if(auth()->user()->role === 'admin')
                            <button wire:click="openModal"
                                class="bg-slate-900 dark:bg-blue-600 text-white px-4 py-2 sm:py-2.5 rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors flex items-center justify-center space-x-2 font-semibold text-xs sm:text-sm w-full sm:w-auto shadow-sm active:scale-95 shrink-0 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>Tambah Add-on</span>
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- Status Filter Tabs (Semua, Aktif, Non-aktif, Arsip) --}}
                    <div class="flex items-center gap-2 mt-5 pt-4 border-t border-slate-100 dark:border-slate-700/60 overflow-x-auto scrollbar-none text-xs">
                        <button wire:click="setStatusFilter('all')"
                            class="px-3.5 py-1.5 rounded-lg font-medium transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer {{ $statusFilter === 'all' ? 'bg-slate-900 text-white dark:bg-blue-600 shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                            <span>Semua Add-on</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $statusFilter === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-200' }}">{{ $counts['all'] }}</span>
                        </button>

                        <button wire:click="setStatusFilter('active')"
                            class="px-3.5 py-1.5 rounded-lg font-medium transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer {{ $statusFilter === 'active' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200/60 dark:border-emerald-800/40' }}">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Aktif di POS</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $statusFilter === 'active' ? 'bg-white/20 text-white' : 'bg-emerald-200/70 dark:bg-emerald-800 text-emerald-800 dark:text-emerald-200' }}">{{ $counts['active'] }}</span>
                        </button>

                        <button wire:click="setStatusFilter('inactive')"
                            class="px-3.5 py-1.5 rounded-lg font-medium transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer {{ $statusFilter === 'inactive' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-200/60 dark:border-amber-800/40' }}">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            <span>Non-Aktif</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $statusFilter === 'inactive' ? 'bg-white/20 text-white' : 'bg-amber-200/70 dark:bg-amber-800 text-amber-800 dark:text-amber-200' }}">{{ $counts['inactive'] }}</span>
                        </button>

                        <button wire:click="setStatusFilter('trashed')"
                            class="px-3.5 py-1.5 rounded-lg font-medium transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer {{ $statusFilter === 'trashed' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200/60 dark:border-rose-800/40' }}">
                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            <span>Tong Sampah</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $statusFilter === 'trashed' ? 'bg-white/20 text-white' : 'bg-rose-200/70 dark:bg-rose-800 text-rose-800 dark:text-rose-200' }}">{{ $counts['trashed'] }}</span>
                        </button>
                    </div>
                </div>

                {{-- Table Data --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 uppercase tracking-wider text-[11px] font-semibold border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-5 sm:px-6 py-3.5">Nama Add-on</th>
                                <th class="px-5 sm:px-6 py-3.5">Kategori Menu Tertaut</th>
                                <th class="px-5 sm:px-6 py-3.5 text-right">Harga Jual</th>
                                <th class="px-5 sm:px-6 py-3.5 text-right">HPP / Modal</th>
                                <th class="px-5 sm:px-6 py-3.5 text-center">Status POS</th>
                                <th class="px-5 sm:px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-xs sm:text-sm">
                            @forelse ($addons as $addon)
                                @php
                                    $profitNominal = $addon->price - $addon->harga_beli;
                                    $profitPercent = $addon->price > 0 ? round(($profitNominal / $addon->price) * 100) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors group {{ $addon->trashed() ? 'opacity-70 bg-rose-50/20 dark:bg-rose-950/10' : '' }}">
                                    {{-- Nama Add-on & Info --}}
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60 flex items-center justify-center font-bold text-sm shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-bold text-slate-900 dark:text-white truncate max-w-[200px] flex items-center gap-1.5">
                                                    <span>{{ $addon->name }}</span>
                                                    @if($addon->trashed())
                                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 font-semibold">Terhapus</span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-[220px]">
                                                    Margin: <span class="font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($profitNominal, 0, ',', '.') }} ({{ $profitPercent }}%)</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kategori Tertaut --}}
                                    <td class="px-5 sm:px-6 py-3.5">
                                        @if($addon->categories->count() > 0)
                                            <div class="flex flex-wrap gap-1 max-w-xs">
                                                @foreach($addon->categories as $cat)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                                        {{ $cat->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500 text-xs italic">
                                                Semua Menu (Bebas)
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Harga Jual --}}
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-right font-bold text-emerald-600 dark:text-emerald-400">
                                        + Rp {{ number_format($addon->price, 0, ',', '.') }}
                                    </td>

                                    {{-- HPP / Modal --}}
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-right text-slate-500 dark:text-slate-400 font-medium font-mono text-xs">
                                        Rp {{ number_format($addon->harga_beli, 0, ',', '.') }}
                                    </td>

                                    {{-- Status POS Toggle --}}
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-center">
                                        @if($addon->trashed())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                Diarsipkan
                                            </span>
                                        @else
                                            <button wire:click="toggleStatus({{ $addon->id }})" 
                                                title="Klik untuk ubah status ketersediaan di Kasir POS"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-all cursor-pointer border {{ $addon->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700 hover:bg-slate-200' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $addon->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                                <span>{{ $addon->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
                                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                                            </button>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-right space-x-1.5 shrink-0">
                                        @if(auth()->user()->role === 'admin')
                                            @if($addon->trashed())
                                                {{-- Pulihkan (Restore) --}}
                                                <button onclick="confirmRestoreAddon({{ $addon->id }}, '{{ $addon->name }}')"
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-emerald-300 dark:border-emerald-800 text-xs font-medium rounded-lg text-emerald-700 dark:text-emerald-400 bg-white dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition-colors active:scale-95 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                    Pulihkan
                                                </button>

                                                {{-- Hapus Permanen --}}
                                                <button onclick="confirmForceDeleteAddon({{ $addon->id }}, '{{ $addon->name }}')"
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-rose-300 dark:border-rose-800 text-xs font-medium rounded-lg text-rose-700 dark:text-rose-400 bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors active:scale-95 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Hapus Permanen
                                                </button>
                                            @else
                                                {{-- Edit --}}
                                                <button wire:click="edit({{ $addon->id }})"
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 text-xs font-medium rounded-lg text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors active:scale-95 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    Edit
                                                </button>

                                                {{-- Hapus / Arsipkan --}}
                                                <button onclick="confirmDeleteAddon({{ $addon->id }}, '{{ $addon->name }}')"
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-rose-300 dark:border-rose-800 text-xs font-medium rounded-lg text-rose-700 dark:text-rose-400 bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors active:scale-95 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Hapus
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                        <div class="flex flex-col items-center justify-center space-y-3">
                                            <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center">
                                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                            </div>
                                            <div class="text-sm font-medium">Tidak ada data add-on / modifier ditemukan</div>
                                            <div class="text-xs text-slate-400">Coba ubah kata kunci pencarian atau filter kategori Anda</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($addons->hasPages())
                    <div class="p-4 sm:p-6 border-t border-slate-200 dark:border-slate-700">
                        {{ $addons->links() }}
                    </div>
                @endif
            </div>
        </main>
    </div>

    {{-- MODAL TAMBAH / EDIT ADD-ON --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closeModal"></div>
                
                <div class="inline-block bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full p-5 sm:p-6 border border-slate-200 dark:border-slate-700 my-8">
                    <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}">
                        {{-- Modal Header --}}
                        <div class="flex justify-between items-center pb-3 border-b border-slate-200 dark:border-slate-700 mb-4">
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">
                                {{ $isEdit ? 'Ubah Data Add-on' : 'Tambah Add-on Baru' }}
                            </h3>
                            <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        {{-- Modal Body --}}
                        <div class="space-y-4">
                            {{-- Nama Add-on --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Add-on / Modifier <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="name" placeholder="Contoh: Extra Shot, Caramel Syrup, Oat Milk"
                                    class="w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500">
                                @error('name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Grid Harga Jual & HPP --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                {{-- Harga Jual --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Harga Jual Tambahan (Rp) <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-xs text-slate-400 font-bold">Rp</span>
                                        <input type="text" wire:model.live="formattedPrice" placeholder="0"
                                            class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-bold focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500">
                                    </div>
                                    @error('price') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- HPP / Harga Beli --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">HPP / Modal Bahan (Rp) <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-xs text-slate-400 font-bold">Rp</span>
                                        <input type="text" wire:model.live="formattedHargaBeli" placeholder="0"
                                            class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-bold focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500">
                                    </div>
                                    @error('harga_beli') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Margin Estimation Box --}}
                            @php
                                $estProfit = $price - $harga_beli;
                                $estMargin = $price > 0 ? round(($estProfit / $price) * 100) : 0;
                            @endphp
                            <div class="bg-slate-50 dark:bg-slate-900/60 p-3 rounded-xl border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between text-xs">
                                <span class="text-slate-500 dark:text-slate-400 font-medium">Estimasi Margin Laba:</span>
                                <span class="font-extrabold {{ $estProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600' }}">
                                    Rp {{ number_format($estProfit, 0, ',', '.') }} ({{ $estMargin }}%)
                                </span>
                            </div>

                            {{-- Checklist Kategori Terkait --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Tautkan ke Kategori Menu:
                                    </label>
                                    <div class="space-x-2 text-[11px]">
                                        <button type="button" wire:click="selectAllCategories" class="text-blue-600 dark:text-blue-400 hover:underline font-bold cursor-pointer">Pilih Semua</button>
                                        <span class="text-slate-300">|</span>
                                        <button type="button" wire:click="clearAllCategories" class="text-slate-400 hover:underline cursor-pointer">Hapus Pilihan</button>
                                    </div>
                                </div>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mb-2">Pilih kategori menu yang dapat menambahkan add-on ini. Jika kosong, add-on tersedia di semua menu.</p>
                                
                                <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto p-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/50 dark:bg-slate-900/40 scrollbar-thin">
                                    @foreach($categories as $cat)
                                        <label class="flex items-center space-x-2 text-xs p-1.5 rounded-lg hover:bg-white dark:hover:bg-slate-800 transition cursor-pointer">
                                            <input type="checkbox" wire:model="selectedCategories" value="{{ $cat->id }}"
                                                class="rounded border-slate-300 dark:border-slate-600 text-slate-900 dark:text-blue-600 focus:ring-blue-500">
                                            <span class="text-slate-700 dark:text-slate-300 font-medium truncate">{{ $cat->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Toggle Status Aktif --}}
                            <div class="flex items-center space-x-2 pt-2">
                                <input type="checkbox" id="is_active_modal" wire:model="is_active"
                                    class="rounded border-slate-300 dark:border-slate-600 text-slate-900 dark:text-blue-600 focus:ring-blue-500">
                                <label for="is_active_modal" class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
                                    Aktifkan Add-on (Langsung tersedia di POS Kasir & Self-Order)
                                </label>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="mt-6 flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-700">
                            <button type="button" wire:click="closeModal"
                                class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg font-semibold text-xs transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-slate-900 dark:bg-blue-600 text-white rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors font-semibold text-xs shadow-sm active:scale-95 flex items-center justify-center min-w-[110px] cursor-pointer"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Add-on' }}</span>
                                <span wire:loading>
                                    <svg class="animate-spin h-4 w-4 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function confirmDeleteAddon(id, name) {
        Swal.fire({
            title: 'Arsipkan Add-on?',
            text: "Add-on '" + name + "' akan dipindahkan ke Tong Sampah.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#475569', 
            confirmButtonText: 'Ya, Arsipkan!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('delete', id);
            }
        });
    }

    function confirmRestoreAddon(id, name) {
        Swal.fire({
            title: 'Pulihkan Add-on?',
            text: "Add-on '" + name + "' akan dipulihkan dan aktif kembali di sistem.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Ya, Pulihkan!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('restore', id);
            }
        });
    }

    function confirmForceDeleteAddon(id, name) {
        Swal.fire({
            title: 'Hapus Permanen?',
            text: "Add-on '" + name + "' akan dihapus selamanya dari database dan tidak dapat dipulihkan!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Ya, Hapus Permanen!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('forceDelete', id);
            }
        });
    }
</script>
@endpush
