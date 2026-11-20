<?php

namespace App\Livewire\Admin;

use App\Models\PlatformSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Configurações Globais — Admin Nosso Voto')]
class PlatformSettings extends Component
{
    public bool $paywall_pinterest_inspirations_enabled = false;

    public string $platform_gift_fee_percent = '4.99';

    public string $regional_highlight_monthly_price = '69.90';

    public string $portfolio_monthly_price = '39.90';

    public int $banner_cycle_months = 2;

    public int $banner_max_slots = 5;

    public string $banner_cycle_price = '149.90';

    public string $statusMessage = '';

    public function mount(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $settings = PlatformSetting::current();

        $this->paywall_pinterest_inspirations_enabled = (bool) $settings->paywall_pinterest_inspirations_enabled;
        $this->platform_gift_fee_percent = (string) $settings->platform_gift_fee_percent;
        $this->regional_highlight_monthly_price = (string) $settings->regional_highlight_monthly_price;
        $this->portfolio_monthly_price = (string) $settings->portfolio_monthly_price;
        $this->banner_cycle_months = (int) $settings->banner_cycle_months;
        $this->banner_max_slots = (int) $settings->banner_max_slots;
        $this->banner_cycle_price = (string) $settings->banner_cycle_price;
    }

    public function togglePinterestPaywall(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $this->paywall_pinterest_inspirations_enabled = ! $this->paywall_pinterest_inspirations_enabled;

        PlatformSetting::current()->update([
            'paywall_pinterest_inspirations_enabled' => $this->paywall_pinterest_inspirations_enabled,
        ]);

        $this->statusMessage = $this->paywall_pinterest_inspirations_enabled
            ? 'Paywall de inspirações do Pinterest ativado.'
            : 'Paywall de inspirações do Pinterest desativado (acesso livre no MVP).';
    }

    public function save(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $validated = $this->validate([
            'paywall_pinterest_inspirations_enabled' => ['boolean'],
            'platform_gift_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'regional_highlight_monthly_price' => ['required', 'numeric', 'min:0'],
            'portfolio_monthly_price' => ['required', 'numeric', 'min:0'],
            'banner_cycle_months' => ['required', 'integer', 'min:1', 'max:24'],
            'banner_max_slots' => ['required', 'integer', 'min:1', 'max:20'],
            'banner_cycle_price' => ['required', 'numeric', 'min:0'],
        ]);

        PlatformSetting::current()->update($validated);

        $this->statusMessage = 'Configurações globais da plataforma atualizadas com sucesso.';
    }

    public function render(): View
    {
        return view('livewire.admin.platform-settings');
    }
}
