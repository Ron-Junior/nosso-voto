<?php

namespace App\Livewire\Vendor;

use App\Livewire\Dashboard\UserPanel;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

#[Title('Agenda de Próximos Noivos — Nosso Voto')]
class VendorSchedule extends UserPanel
{
    public function mount(): void
    {
        $user = Auth::user();

        abort_unless($user !== null && ($user->isVendor() || $user->isAdmin()), 403);

        parent::mount();
    }

    public function render(): View
    {
        return view('livewire.vendor.vendor-schedule', $this->viewData(true));
    }
}
