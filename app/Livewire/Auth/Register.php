<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::guest')]
#[Title('Criar Conta — Nosso Voto')]
class Register extends Component
{
    public string $role = User::ROLE_COUPLE;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function selectRole(string $role): void
    {
        if (in_array($role, [User::ROLE_COUPLE, User::ROLE_VENDOR], true)) {
            $this->role = $role;
        }
    }

    public function register(): void
    {
        $validated = $this->validate([
            'role' => ['required', 'in:couple,vendor'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'role.in' => 'Selecione um perfil válido (Noivos ou Fornecedor).',
            'name.required' => 'Informe seu nome completo ou nome do casal.',
            'email.required' => 'Informe seu endereço de e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.required' => 'Crie uma senha de acesso.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
        ]);

        Auth::login($user);
        session()->regenerate();

        $this->redirect($user->homeRoute());
    }

    public function render(): View
    {
        return view('livewire.auth.register');
    }
}
