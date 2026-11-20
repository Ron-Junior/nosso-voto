<?php

namespace App\Livewire\Dashboard;

use App\Models\PlatformSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Painel — Nosso Voto')]
class UserPanel extends Component
{
    public string $statusMessage = '';

    public function regenerateToken(): void
    {
        $user = Auth::user();

        abort_unless($user !== null, 403);

        $user->regenerateApiToken();

        $this->statusMessage = 'Novo token de integração gerado com sucesso.';
    }

    public function render(): View
    {
        return view('livewire.dashboard.user-panel', [
            'user' => Auth::user(),
            'settings' => PlatformSetting::current(),
        ]);
    }
}
