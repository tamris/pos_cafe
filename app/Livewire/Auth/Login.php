<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.guest')]
#[Title('Masuk - POS Cafe')]
class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    protected $messages = [
        'email.required' => 'Email wajib diisi',
        'email.email' => 'Format email tidak valid',
        'password.required' => 'Password wajib diisi',
        'password.min' => 'Password minimal 6 karakter',
    ];

    public function login()
    {
        $this->validate();

        // 1. Cek apakah pengguna mencoba login dengan akun aktif
        if (Auth::attempt(['email' => $this->email, 'password' => $this->password, 'is_active' => true], $this->remember)) {
            session()->regenerate();

            $user = Auth::user();
            logger()->info('LOGIN SUCCESS', ['user_id' => $user->id, 'role' => $user->role]);

            if ($user->role === 'admin') {
                return redirect()->intended('/dashboard')->with('navigate');
            }

            return redirect()->intended('/pos');
        }

        // 2. Cek apakah password benar namun akun berstatus Non-aktif
        $inactiveUser = User::where('email', $this->email)->where('is_active', false)->first();
        if ($inactiveUser && Hash::check($this->password, $inactiveUser->password)) {
            $this->addError('email', 'Akun Anda telah dinonaktifkan oleh Administrator. Silakan hubungi pemilik cafe.');
            return;
        }

        logger()->warning('LOGIN FAILED', [
            'email' => $this->email,
        ]);

        $this->addError('email', 'Email atau password salah.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
