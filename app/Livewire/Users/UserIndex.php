<?php

namespace App\Livewire\Users;

use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Manajemen Pengguna - POS Cafe')]
class UserIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $statusFilter = 'all'; // 'all', 'active', 'inactive', 'trashed'

    public $name = '';
    public $email = '';
    public $password = '';
    public $pin = '';
    public $role = 'kasir';
    public $is_active = true;
    public $userId = null;
    public $isOpen = false;
    public $isEdit = false;

    // Dedicated Quick PIN Modal State
    public $showPinModal = false;
    public $pinUserId = null;
    public $pinUserName = '';
    public $newPin = '';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingRoleFilter() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }

    public function setStatusFilter($filter)
    {
        $this->statusFilter = $filter;
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->pin = '';
        $this->role = 'kasir';
        $this->is_active = true;
        $this->userId = null;
        $this->isEdit = false;
        $this->resetValidation();
    }

    public function generateRandomPin()
    {
        $this->pin = $this->generateUniqueRandomPin();
    }

    public function generateRandomNewPin()
    {
        $this->newPin = $this->generateUniqueRandomPin();
    }

    protected function generateUniqueRandomPin()
    {
        do {
            $pin = str_pad((string) mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (User::where('pin', $pin)->exists());

        return $pin;
    }

    public function openModal()
    {
        $this->resetFields();
        $this->pin = $this->generateUniqueRandomPin();
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetFields();
    }

    public function openPinModal($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $this->pinUserId = $user->id;
        $this->pinUserName = $user->name;
        $this->newPin = $user->pin ?? '';
        $this->resetValidation();
        $this->showPinModal = true;
    }

    public function closePinModal()
    {
        $this->showPinModal = false;
        $this->pinUserId = null;
        $this->pinUserName = '';
        $this->newPin = '';
        $this->resetValidation();
    }

    public function updatePin()
    {
        $this->validate([
            'newPin' => ['required', 'digits:6', Rule::unique('users', 'pin')->ignore($this->pinUserId)->whereNull('deleted_at')],
        ], [
            'newPin.required' => 'PIN 6 digit wajib diisi.',
            'newPin.digits' => 'PIN wajib berupa 6 digit angka.',
            'newPin.unique' => 'PIN ini sudah digunakan oleh pengguna lain. Silakan pilih kombinasi 6 digit berbeda.',
        ]);

        $user = User::withTrashed()->findOrFail($this->pinUserId);
        $user->update(['pin' => $this->newPin]);

        session()->flash('success', "PIN login apps untuk '{$user->name}' berhasil diubah menjadi {$this->newPin}.");
        $this->closePinModal();
    }

    public function toggleStatus($id)
    {
        if ($id == auth()->id()) {
            session()->flash('error', 'Anda tidak bisa menonaktifkan akun yang sedang Anda gunakan saat ini!');
            return;
        }

        $user = User::find($id);
        if (!$user) return;

        // Cek jika akun ini adalah satu-satunya admin aktif
        if ($user->role === 'admin' && $user->is_active && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            session()->flash('error', 'Sistem membutuhkan minimal 1 Administrator aktif agar akses tidak terkunci!');
            return;
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'Diaktifkan (Dapat login)' : 'Dinonaktifkan (Akses login dibekukan)';
        session()->flash('success', "Akun '{$user->name}' berhasil {$statusText}.");
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|min:2',
            'email' => ['required', 'email', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password' => 'required|min:6',
            'pin' => ['required', 'digits:6', Rule::unique('users', 'pin')->whereNull('deleted_at')],
            'role' => 'required|in:admin,kasir',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'name.min' => 'Nama minimal 2 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar pada pengguna aktif.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'pin.required' => 'PIN login apps kasir (6 digit) wajib diisi.',
            'pin.digits' => 'PIN wajib berupa 6 digit angka.',
            'pin.unique' => 'PIN ini sudah digunakan oleh pengguna lain. Pilih kombinasi 6 digit berbeda.',
            'role.required' => 'Jabatan wajib dipilih.',
        ]);

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'pin' => $this->pin,
            'role' => $this->role,
            'is_active' => (bool) $this->is_active,
        ]);

        session()->flash('success', 'Pengguna baru berhasil ditambahkan.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->pin = $user->pin ?? '';
        $this->role = $user->role;
        $this->is_active = (bool) $user->is_active;
        $this->password = '';
        $this->isEdit = true;
        $this->isOpen = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|min:2',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->userId)->whereNull('deleted_at')],
            'pin' => ['required', 'digits:6', Rule::unique('users', 'pin')->ignore($this->userId)->whereNull('deleted_at')],
            'role' => 'required|in:admin,kasir',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'pin.required' => 'PIN login apps kasir (6 digit) wajib diisi.',
            'pin.digits' => 'PIN wajib berupa 6 digit angka.',
            'pin.unique' => 'PIN ini sudah digunakan oleh pengguna lain.',
            'role.required' => 'Jabatan wajib dipilih.',
        ]);

        $user = User::withTrashed()->findOrFail($this->userId);

        // Jika mengubah akun diri sendiri
        if ($user->id == auth()->id()) {
            if ($this->role !== 'admin') {
                session()->flash('error', 'Anda tidak dapat mengubah role akun Anda sendiri menjadi non-admin!');
                return;
            }
            if (!$this->is_active) {
                session()->flash('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri!');
                return;
            }
        }
        
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'pin' => $this->pin,
            'role' => $this->role,
            'is_active' => (bool) $this->is_active,
        ];

        if (!empty($this->password)) {
            $this->validate([
                'password' => 'min:6'
            ], [
                'password.min' => 'Password minimal 6 karakter.'
            ]);
            $data['password'] = Hash::make($this->password);
        }

        $user->update($data);

        session()->flash('success', 'Data pengguna berhasil diperbarui.');
        $this->closeModal();
    }

    public function delete($id)
    {
        if ($id == auth()->id()) {
            session()->flash('error', 'Anda tidak bisa menghapus akun sendiri!');
            return;
        }

        $user = User::find($id);
        if (!$user) return;

        // Cek jika akun ini adalah satu-satunya admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            session()->flash('error', 'Tidak dapat menghapus satu-satunya Administrator!');
            return;
        }

        $hasTransactions = $user->transactions()->exists();
        $hasShifts = $user->cashierShifts()->exists();

        // Soft delete user
        $user->delete();

        if ($hasTransactions || $hasShifts) {
            session()->flash('success', "Akun '{$user->name}' berhasil diarsipkan (Soft Delete). Riwayat transaksi dan shift masa lalu tetap 100% aman tersimpan.");
        } else {
            session()->flash('success', "Akun '{$user->name}' berhasil dipindahkan ke arsip.");
        }
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        session()->flash('success', "Akun '{$user->name}' berhasil dipulihkan dari arsip.");
    }

    public function forceDelete($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);

        if ($user->transactions()->exists() || $user->cashierShifts()->exists()) {
            session()->flash('error', "Akun '{$user->name}' tidak dapat dihapus permanen karena memiliki riwayat transaksi / shift kasir.");
            return;
        }

        $user->forceDelete();
        session()->flash('success', "Akun '{$user->name}' berhasil dihapus permanen.");
    }

    public function render()
    {
        // Counter badge untuk tab
        $countAll = User::count();
        $countActive = User::where('is_active', true)->count();
        $countInactive = User::where('is_active', false)->count();
        $countTrashed = User::onlyTrashed()->count();

        // Query berdasarkan filter status
        if ($this->statusFilter === 'trashed') {
            $query = User::onlyTrashed();
        } elseif ($this->statusFilter === 'active') {
            $query = User::where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query = User::where('is_active', false);
        } else {
            $query = User::query();
        }

        if (!empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('email', 'like', $term);
            });
        }

        if (!empty($this->roleFilter)) {
            $query->where('role', $this->roleFilter);
        }

        $users = $query->orderByRaw("CASE WHEN role = 'admin' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(10);

        return view('livewire.users.user-index', [
            'users' => $users,
            'countAll' => $countAll,
            'countActive' => $countActive,
            'countInactive' => $countInactive,
            'countTrashed' => $countTrashed,
        ]);
    }
}