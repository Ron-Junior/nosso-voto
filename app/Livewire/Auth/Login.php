<?php

namespace App\Livewire\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::guest')]
#[Title('Entrar — Nosso Voto')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'password.required' => 'Informe sua senha.',
        ]);

        if (! Auth::attempt($credentials, $this->remember)) {
            $this->addError('email', 'Credenciais inválidas. Verifique seu e-mail e senha.');

            return;
        }

        session()->regenerate();

        $this->redirectIntended(default: Auth::user()->homeRoute());
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
