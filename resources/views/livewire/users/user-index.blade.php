<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300"
     x-data="{ sidebarOpen: window.innerWidth >= 1280 }"
     @resize.window="sidebarOpen = window.innerWidth >= 1280">
    
    {{-- 1. Sidebar --}}
    @include('livewire.includes.sidebar')

    {{-- 2. Main Content Wrapper --}}
    <div class="xl:pl-64 transition-all duration-300 flex flex-col min-h-screen">
        
        {{-- Header --}}
        @include('livewire.includes.header', [
            'title' => 'Manajemen Pengguna', 
            'subtitle' => 'Kelola hak akses administrator dan kasir cafe'
        ])

        {{-- Content --}}
        <main class="p-4 sm:p-6 space-y-6 flex-1">
            
            {{-- Flash Messages --}}
            @if (session()->has('success'))
                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4 animate-fade-in">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-emerald-800 dark:text-emerald-300 font-medium text-xs sm:text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl p-4 animate-fade-in">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-rose-800 dark:text-rose-300 font-medium text-xs sm:text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Main Card --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors overflow-hidden">
                
                {{-- Card Header & Filter Box --}}
                <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Daftar Pengguna & Kasir</h2>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelola data login akun kasir dan status akses sistem</p>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3">
                            {{-- Filter Role --}}
                            <select wire:model.live="roleFilter"
                                class="px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white w-full sm:w-auto cursor-pointer">
                                <option value="">Semua Jabatan</option>
                                <option value="admin">Administrator (Pemilik)</option>
                                <option value="kasir">Kasir (Staf)</option>
                            </select>

                            {{-- Search Input --}}
                            <div class="relative w-full sm:w-56">
                                <input type="text" wire:model.live.debounce.300ms="search"
                                    placeholder="Cari nama / email..."
                                    class="w-full pl-10 pr-4 py-2 sm:py-2.5 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500">
                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5 sm:top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            {{-- Tombol Tambah --}}
                            <button wire:click="openModal" class="bg-slate-900 dark:bg-blue-600 text-white px-4 py-2 sm:py-2.5 rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors flex items-center justify-center space-x-2 font-semibold text-xs sm:text-sm shadow-sm active:scale-95 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>Tambah Pengguna</span>
                            </button>
                        </div>
                    </div>

                    {{-- Status Filter Tabs (Semua, Aktif, Non-aktif, Arsip) --}}
                    <div class="flex items-center gap-2 mt-5 pt-4 border-t border-slate-100 dark:border-slate-700/60 overflow-x-auto scrollbar-none text-xs">
                        <button wire:click="setStatusFilter('all')"
                            class="px-3.5 py-1.5 rounded-lg font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'all' ? 'bg-slate-900 text-white dark:bg-blue-600 shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                            <span>Semua Pengguna</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $statusFilter === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-200' }}">{{ $countAll }}</span>
                        </button>

                        <button wire:click="setStatusFilter('active')"
                            class="px-3.5 py-1.5 rounded-lg font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'active' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200/60 dark:border-emerald-800/40' }}">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Aktif (Bisa Login)</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $statusFilter === 'active' ? 'bg-white/20 text-white' : 'bg-emerald-200/70 dark:bg-emerald-800 text-emerald-800 dark:text-emerald-200' }}">{{ $countActive }}</span>
                        </button>

                        <button wire:click="setStatusFilter('inactive')"
                            class="px-3.5 py-1.5 rounded-lg font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'inactive' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-200/60 dark:border-amber-800/40' }}">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            <span>Non-Aktif (Dibekukan)</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $statusFilter === 'inactive' ? 'bg-white/20 text-white' : 'bg-amber-200/70 dark:bg-amber-800 text-amber-800 dark:text-amber-200' }}">{{ $countInactive }}</span>
                        </button>

                        <button wire:click="setStatusFilter('trashed')"
                            class="px-3.5 py-1.5 rounded-lg font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'trashed' ? 'bg-rose-700 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/30' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            <span>Arsip</span>
                            @if($countTrashed > 0)
                                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $statusFilter === 'trashed' ? 'bg-white/20 text-white' : 'bg-rose-200 dark:bg-rose-900 text-rose-800 dark:text-rose-200' }}">{{ $countTrashed }}</span>
                            @endif
                        </button>
                    </div>
                </div>

                {{-- Table Responsive Area --}}
                <div class="overflow-x-auto scrollbar-thin">
                    <table class="w-full text-left border-collapse min-w-[700px]">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-[11px] uppercase text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-5 sm:px-6 py-3.5">Nama Pengguna</th>
                                <th class="px-5 sm:px-6 py-3.5">Email Login</th>
                                <th class="px-5 sm:px-6 py-3.5">Jabatan / Role</th>
                                <th class="px-5 sm:px-6 py-3.5 text-center">Status Akses</th>
                                <th class="px-5 sm:px-6 py-3.5">Terdaftar</th>
                                <th class="px-5 sm:px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-xs sm:text-sm">
                            @forelse($users as $user)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors {{ $user->trashed() ? 'opacity-70 bg-rose-50/20 dark:bg-rose-950/10' : '' }}">
                                {{-- Nama --}}
                                <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-700 dark:text-slate-300 font-bold text-sm border border-slate-200 dark:border-slate-600 shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-slate-900 dark:text-white flex items-center gap-1.5">
                                                <span>{{ $user->name }}</span>
                                                @if(auth()->id() === $user->id)
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-semibold">Anda</span>
                                                @endif
                                                @if($user->trashed())
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 font-semibold">Diarsipkan</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Email --}}
                                <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-slate-600 dark:text-slate-300 font-mono text-xs">
                                    {{ $user->email }}
                                </td>

                                {{-- Jabatan --}}
                                <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap">
                                    @if($user->role === 'admin')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            Administrator (Pemilik)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                            Kasir (Staf)
                                        </span>
                                    @endif
                                </td>

                                {{-- Status Akses Toggle --}}
                                <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-center">
                                    @if($user->trashed())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            Diarsipkan
                                        </span>
                                    @else
                                        <button wire:click="toggleStatus({{ $user->id }})" 
                                            @if(auth()->id() === $user->id) disabled title="Akun yang sedang login tidak dapat dinonaktifkan" @else title="Klik untuk ubah status izin login kasir" @endif
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-all border {{ auth()->id() === $user->id ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }} {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700 hover:bg-slate-200' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            <span>{{ $user->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
                                            @if(auth()->id() !== $user->id)
                                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                                            @endif
                                        </button>
                                    @endif
                                </td>

                                {{-- Terdaftar --}}
                                <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-slate-500 dark:text-slate-400">
                                    {{ $user->created_at->format('d M Y') }}
                                </td>

                                {{-- Aksi --}}
                                <td class="px-5 sm:px-6 py-3.5 whitespace-nowrap text-right space-x-1.5 shrink-0">
                                    @if($user->trashed())
                                        {{-- Pulihkan --}}
                                        <button onclick="confirmRestoreUser({{ $user->id }}, '{{ $user->name }}')"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-emerald-300 dark:border-emerald-800 text-xs font-medium rounded-lg text-emerald-700 dark:text-emerald-400 bg-white dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition-colors active:scale-95">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                            Pulihkan
                                        </button>

                                        {{-- Hapus Permanen --}}
                                        <button onclick="confirmForceDeleteUser({{ $user->id }}, '{{ $user->name }}')"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-rose-300 dark:border-rose-800 text-xs font-medium rounded-lg text-rose-700 dark:text-rose-400 bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors active:scale-95">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Hapus Permanen
                                        </button>
                                    @else
                                        <button wire:click="edit({{ $user->id }})" class="inline-flex items-center px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 text-xs font-medium rounded-lg text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors active:scale-95">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </button>
                                        
                                        @if(auth()->id() !== $user->id)
                                            <button onclick="confirmDeleteUser({{ $user->id }}, '{{ $user->name }}')" class="inline-flex items-center px-2.5 py-1.5 border border-rose-300 dark:border-rose-800/60 text-xs font-medium rounded-lg text-rose-700 dark:text-rose-400 bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors active:scale-95" title="Arsipkan Pengguna">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Hapus
                                            </button>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        <p class="text-slate-500 dark:text-slate-400 font-medium text-xs sm:text-sm">
                                            @if($statusFilter === 'trashed')
                                                Arsip kosong. Tidak ada akun pengguna yang diarsipkan.
                                            @else
                                                Tidak ada data pengguna ditemukan.
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Area --}}
                <div class="px-5 sm:px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    {{ $users->links() }}
                </div>
            </div>
        </main>
    </div>

    {{-- Modal Form --}}
    @if($isOpen)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
            
            {{-- Backdrop --}}
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" wire:click="closeModal"></div>

            {{-- Modal Content --}}
            <div class="inline-block bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full my-8 border border-slate-200 dark:border-slate-700">
                <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}">
                    
                    {{-- Modal Header --}}
                    <div class="bg-slate-900 dark:bg-slate-700 text-white px-5 sm:px-6 py-4 border-b border-slate-800 dark:border-slate-600 flex justify-between items-center">
                        <h3 class="text-base font-bold">
                            {{ $isEdit ? 'Ubah Data Pengguna' : 'Tambah Pengguna Baru' }}
                        </h3>
                        <button type="button" wire:click="closeModal" class="text-white hover:opacity-80 transition-opacity p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="bg-white dark:bg-slate-800 p-5 sm:p-6 space-y-4">
                        
                        {{-- Nama --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="name" class="block w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white" placeholder="Contoh: Budi Santoso">
                            @error('name') <span class="text-rose-500 dark:text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">Email Login <span class="text-rose-500">*</span></label>
                            <input type="email" wire:model="email" class="block w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white" placeholder="budi@poscafe.com">
                            @error('email') <span class="text-rose-500 dark:text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Password --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">Password {{ $isEdit ? '(Opsional)' : '*' }}</label>
                            <input type="password" wire:model="password" placeholder="{{ $isEdit ? 'Kosongkan jika tidak ingin mengubah' : 'Minimal 6 karakter' }}" class="block w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400">
                            @error('password') <span class="text-rose-500 dark:text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Role --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase">Jabatan (Role) <span class="text-rose-500">*</span></label>
                            <select wire:model="role" class="block w-full px-3 py-2 text-xs sm:text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white cursor-pointer">
                                <option value="kasir">Kasir (Staf)</option>
                                <option value="admin">Administrator (Pemilik)</option>
                            </select>
                            @error('role') <span class="text-rose-500 dark:text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Status Akun (is_active) --}}
                        <div class="bg-slate-50 dark:bg-slate-900/60 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Status Izin Akses Login</span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">Aktifkan agar akun kasir dapat melakukan login ke sistem POS</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>
                    </div>
                    
                    {{-- Modal Footer --}}
                    <div class="bg-slate-50 dark:bg-slate-700/50 px-5 sm:px-6 py-3.5 flex justify-end space-x-2.5 border-t border-slate-200 dark:border-slate-700">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 transition-colors font-medium text-xs">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-slate-900 dark:bg-blue-600 text-white rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors font-semibold text-xs shadow-sm active:scale-95">
                            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Pengguna' }}
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
    function confirmDeleteUser(userId, userName) {
        Swal.fire({
            title: 'Arsipkan Pengguna?',
            html: `<div class="text-left text-xs space-y-2">
                    <p class="font-semibold text-sm text-slate-800 dark:text-slate-200">Pengguna: <span class="text-rose-600">${userName}</span></p>
                    <p class="text-slate-600 dark:text-slate-400">Akun kasir akan dibekukan dan diarsipkan (Soft Delete). <b>Seluruh data transaksi & riwayat shift kasir masa lalu tetap 100% aman tersimpan.</b></p>
                   </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Ya, Arsipkan',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('delete', userId);
            }
        });
    }

    function confirmRestoreUser(userId, userName) {
        Swal.fire({
            title: 'Pulihkan Pengguna?',
            text: "Akun '" + userName + "' akan dikembalikan ke daftar pengguna aktif.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Ya, Pulihkan',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('restore', userId);
            }
        });
    }

    function confirmForceDeleteUser(userId, userName) {
        Swal.fire({
            title: 'Hapus Permanen?',
            text: "Akun '" + userName + "' akan dihapus selamanya. Pastikan akun ini belum pernah melakukan transaksi / membuka shift.",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Hapus Permanen',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('forceDelete', userId);
            }
        });
    }
</script>
@endpush