<?php

namespace App\Livewire\Users;

use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Manajemen Pengguna - Cafe Noli')]
class UserIndex extends Component
{
    use WithPagination;

    public $name, $email, $password, $role = 'kasir'; // Default role kasir
    public $userId;
    public $isOpen = false;
    public $isEdit = false;

    // Reset input saat modal ditutup
    public function resetFields()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'kasir';
        $this->userId = null;
        $this->isEdit = false;
    }

    public function openModal()
    {
        $this->resetFields();
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    public function store()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,kasir',
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Jabatan wajib dipilih.',
        ]);

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => $this->role,
        ]);

        session()->flash('success', 'Pengguna baru berhasil ditambahkan.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->isEdit = true;
        $this->isOpen = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'role' => 'required|in:admin,kasir',
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'role.required' => 'Jabatan wajib dipilih.',
        ]);

        $user = User::findOrFail($this->userId);
        
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];

        // Hanya update password jika diisi
        if (!empty($this->password)) {
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

        User::find($id)->delete();
        session()->flash('success', 'Pengguna berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.users.user-index', [
            'users' => User::latest()->paginate(10)
        ]);
    }
}