<div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    {{-- 1. Sidebar --}}
    @include('livewire.includes.sidebar')

    {{-- 2. Main Content Wrapper --}}
    <div class="lg:pl-64">
        {{-- Header --}}
        @include('livewire.includes.header', ['title' => 'Manajemen Pengguna', 'subtitle' => 'Kelola hak akses administrator dan kasir'])

        {{-- Content --}}
        <main class="p-6">
            
            {{-- Flash Messages --}}
            @if (session()->has('success'))
                <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 animate-fade-in-down">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-green-800 dark:text-green-300 font-medium text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 animate-fade-in-down">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-red-800 dark:text-red-300 font-medium text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Main Card --}}
            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 card-shadow transition-colors">
                
                {{-- Card Header --}}
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Daftar Pengguna</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Total {{ $users->total() }} pengguna terdaftar</p>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            {{-- Tombol Tambah --}}
                            <button wire:click="openModal" class="bg-slate-900 dark:bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors flex items-center space-x-2 whitespace-nowrap font-medium shadow-lg shadow-slate-200 dark:shadow-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>Tambah Pengguna</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Jabatan / Peran</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Terdaftar</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach($users as $user)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        {{-- Avatar Inisial --}}
                                        <div class="w-9 h-9 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-700 dark:text-slate-300 font-bold text-sm border border-slate-300 dark:border-slate-600">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div class="text-sm font-medium text-slate-900 dark:text-white">{{ $user->name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->role === 'admin')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 border border-green-200 dark:border-green-800">
                                            Administrator (Pemilik)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                            Kasir (Staf)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                    {{ $user->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2 text-right">
                                    <button wire:click="edit({{ $user->id }})" class="inline-flex items-center px-3 py-1.5 border border-slate-300 dark:border-slate-600 text-xs font-medium rounded-lg text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                        <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit
                                    </button>
                                    
                                    @if(auth()->id() !== $user->id)
                                        <button onclick="confirmDeleteUser({{ $user->id }})" class="inline-flex items-center px-3 py-1.5 border border-red-300 dark:border-red-800 text-xs font-medium rounded-lg text-red-700 dark:text-red-400 bg-white dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Hapus
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    {{ $users->links() }}
                </div>
            </div>
        </main>
    </div>

    {{-- Modal Form --}}
    @if($isOpen)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            
            {{-- Backdrop --}}
            <div class="fixed inset-0 transition-opacity bg-slate-900 bg-opacity-50 backdrop-blur-sm" wire:click="closeModal"></div>

            {{-- Modal Content --}}
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}">
                    
                    {{-- Modal Header --}}
                    <div class="bg-white dark:bg-slate-800 px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                            {{ $isEdit ? 'Ubah Data Pengguna' : 'Tambah Pengguna Baru' }}
                        </h3>
                        <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="bg-white dark:bg-slate-800 px-6 py-6 space-y-4">
                        
                        {{-- Nama --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="name" class="block w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            @error('name') <span class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Email Login <span class="text-red-500">*</span></label>
                            <input type="email" wire:model="email" class="block w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            @error('email') <span class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Password --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Password</label>
                            <input type="password" wire:model="password" placeholder="{{ $isEdit ? 'Kosongkan jika tidak ingin mengubah' : '' }}" class="block w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            @error('password') <span class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Role --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Jabatan (Role) <span class="text-red-500">*</span></label>
                            <select wire:model="role" class="block w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                                <option value="kasir">Kasir (Staf)</option>
                                <option value="admin">Administrator (Pemilik)</option>
                            </select>
                            @error('role') <span class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    {{-- Modal Footer --}}
                    <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-4 flex justify-end space-x-3 border-t border-slate-200 dark:border-slate-700">
                        <button type="button" wire:click="closeModal" class="px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 transition-colors font-medium text-sm">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2.5 bg-slate-900 dark:bg-blue-600 text-white rounded-lg hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors font-medium text-sm">
                            Simpan Pengguna
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
    // SweetAlert untuk konfirmasi hapus User
    function confirmDeleteUser(userId) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Akun ini akan dihapus dan tidak bisa login lagi!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#1e293b',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#1e293b'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('delete', userId);
            }
        });
    }
</script>
@endpush