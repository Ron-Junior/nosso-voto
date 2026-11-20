<?php

namespace App\Livewire\Dashboard;

use App\Models\PlatformSetting;
use App\Models\User;
use App\Models\WeddingVendor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
        $this->dispatch('toast', message: $this->statusMessage);
    }

    public function render(): View
    {
        $user = Auth::user();

        return view('livewire.dashboard.user-panel', [
            'user' => $user,
            'settings' => PlatformSetting::current(),
            'linkedWeddingVendors' => $this->resolveLinkedWeddingVendors($user),
        ]);
    }

    /**
     * Find weddings where the authenticated vendor's email is registered as contact.
     *
     * @return Collection<int, WeddingVendor>
     */
    private function resolveLinkedWeddingVendors(?User $user): Collection
    {
        if ($user === null || ! $user->isVendor() || blank($user->email)) {
            return collect();
        }

        $normalizedEmail = Str::lower(trim($user->email));

        return WeddingVendor::query()
            ->with('wedding')
            ->whereNotNull('wedding_id')
            ->whereRaw('LOWER(TRIM(contact_email)) = ?', [$normalizedEmail])
            ->latest('id')
            ->get()
            ->filter(fn (WeddingVendor $vendor): bool => $vendor->wedding !== null)
            ->values();
    }
}
