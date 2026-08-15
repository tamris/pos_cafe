<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.guest')]
#[Title('Masuk - Cafe Noli POS')]
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

        // Tambahkan debug log ke laravel.log
        logger()->info('DEBUG LOGIN INPUT', [
            'email' => $this->email,
            'password' => $this->password,
            'password_length' => strlen($this->password),
            'remember' => $this->remember,
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            $user = Auth::user();
            logger()->info('LOGIN SUCCESS', ['user_id' => $user->id, 'role' => $user->role]);

            if ($user->role === 'admin') {
                return redirect()->intended('/dashboard')->with('navigate');
            }

            return redirect()->intended('/pos');
        }

        logger()->warning('LOGIN FAILED', [
            'email' => $this->email,
            'password_length' => strlen($this->password),
        ]);

        $this->addError('email', 'Email atau password salah.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
