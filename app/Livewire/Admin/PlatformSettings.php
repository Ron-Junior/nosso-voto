<?php

namespace App\Livewire\Admin;

use App\Exceptions\BannerCycleFullException;
use App\Models\BannerCycle;
use App\Models\BannerSubmission;
use App\Models\PlatformSetting;
use App\Services\BannerCycleService;
use App\Support\BrazilLocations;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

    // Banner cycle creation fields
    public string $cycle_title = '';

    public string $cycle_state = 'SP';

    public string $cycle_city = 'São Paulo';

    public int $cycle_duration_months = 2;

    public int $cycle_max_slots = 5;

    public string $cycle_price = '149.90';

    public string $cycle_starts_at = '';

    public string $cycle_ends_at = '';

    public function mount(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $settings = PlatformSetting::current();

        $this->paywall_pinterest_inspirations_enabled = (bool) $settings->paywall_pinterest_inspirations_enabled;
        $this->platform_gift_fee_percent = (string) $settings->platform_gift_fee_percent;
        $this->regional_highlight_monthly_price = (string) $settings->regional_highlight_monthly_price;
        $this->portfolio_monthly_price = (string) $settings->portfolio_monthly_price;
        $this->banner_cycle_months = (int) $settings->banner_cycle_months;
        $this->banner_max_slots = min((int) $settings->banner_max_slots, BannerCycle::HARD_MAX_SLOTS);
        $this->banner_cycle_price = (string) $settings->banner_cycle_price;

        $this->resetCycleDefaults($settings);
    }

    public function updatedCycleState(): void
    {
        $this->statusMessage = '';
        $this->cycle_state = Str::upper(trim($this->cycle_state));

        if (! BrazilLocations::isValidCityForState($this->cycle_state, $this->cycle_city)) {
            $this->cycle_city = '';
        }
    }

    public function updatedCycleDurationMonths(): void
    {
        $this->syncCycleEndDate();
    }

    public function updatedCycleStartsAt(): void
    {
        $this->syncCycleEndDate();
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

        $this->dispatch('toast', message: $this->statusMessage);
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
            'banner_max_slots' => ['required', 'integer', 'min:1', 'max:'.BannerCycle::HARD_MAX_SLOTS],
            'banner_cycle_price' => ['required', 'numeric', 'min:0'],
        ], [
            'banner_max_slots.max' => 'O Banner Principal permite no máximo '.BannerCycle::HARD_MAX_SLOTS.' vagas por ciclo.',
        ]);

        PlatformSetting::current()->update($validated);

        $this->statusMessage = 'Configurações globais da plataforma atualizadas com sucesso.';
        $this->dispatch('toast', message: $this->statusMessage);
    }

    public function createBannerCycle(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $this->cycle_state = Str::upper(trim($this->cycle_state));
        $this->cycle_city = trim($this->cycle_city);

        $validated = $this->validate([
            'cycle_title' => ['required', 'string', 'max:255'],
            'cycle_state' => ['required', 'string', Rule::in(BrazilLocations::stateCodes())],
            'cycle_city' => ['required', 'string', Rule::in(BrazilLocations::citiesForState($this->cycle_state))],
            'cycle_duration_months' => ['required', 'integer', 'min:1', 'max:24'],
            'cycle_max_slots' => ['required', 'integer', 'min:1', 'max:'.BannerCycle::HARD_MAX_SLOTS],
            'cycle_price' => ['required', 'numeric', 'min:0'],
            'cycle_starts_at' => ['required', 'date'],
            'cycle_ends_at' => ['required', 'date', 'after:cycle_starts_at'],
        ], [
            'cycle_title.required' => 'Informe o título do ciclo do banner.',
            'cycle_state.required' => 'Selecione o estado (UF) do ciclo.',
            'cycle_city.required' => 'Selecione a cidade do ciclo.',
            'cycle_city.in' => 'Selecione uma cidade válida para o estado escolhido.',
            'cycle_max_slots.max' => 'Cada ciclo do banner permite no máximo '.BannerCycle::HARD_MAX_SLOTS.' vagas.',
            'cycle_ends_at.after' => 'A data final do ciclo deve ser posterior à data inicial.',
        ]);

        BannerCycle::query()->create([
            'title' => trim($validated['cycle_title']),
            'state' => $validated['cycle_state'],
            'city' => $validated['cycle_city'],
            'duration_months' => (int) $validated['cycle_duration_months'],
            'max_slots' => (int) $validated['cycle_max_slots'],
            'price' => $validated['cycle_price'],
            'starts_at' => Carbon::parse($validated['cycle_starts_at'])->startOfDay(),
            'ends_at' => Carbon::parse($validated['cycle_ends_at'])->endOfDay(),
            'is_active' => true,
        ]);

        $this->cycle_title = '';
        $this->resetCycleDefaults(PlatformSetting::current());

        $this->statusMessage = 'Ciclo do banner criado com sucesso.';
        $this->dispatch('toast', message: $this->statusMessage);
    }

    public function toggleBannerCycleActive(int $cycleId): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $cycle = BannerCycle::query()->findOrFail($cycleId);
        $cycle->update(['is_active' => ! $cycle->is_active]);

        $this->statusMessage = $cycle->is_active
            ? "Ciclo {$cycle->title} reativado."
            : "Ciclo {$cycle->title} pausado.";

        $this->dispatch('toast', message: $this->statusMessage);
    }

    public function approveBannerSubmission(int $submissionId, BannerCycleService $bannerCycleService): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $submission = BannerSubmission::query()->with('vendorProfile')->findOrFail($submissionId);
        $vendorName = $submission->vendorProfile?->business_name ?? 'Fornecedor';

        try {
            $activated = $bannerCycleService->activateSubmission($submission);
        } catch (BannerCycleFullException $exception) {
            if ($submission->status === BannerSubmission::STATUS_PENDING) {
                $bannerCycleService->moveToWaitlist($submission);
            }

            $this->addError('banner_slot_limit', $exception->getMessage());
            $this->statusMessage = $exception->getMessage();
            $this->dispatch('toast', message: $this->statusMessage);

            return;
        }

        $this->statusMessage = "Candidatura de {$vendorName} aprovada na Vaga #{$activated->slot_number}.";
        $this->dispatch('toast', message: $this->statusMessage);
    }

    public function waitlistBannerSubmission(int $submissionId, BannerCycleService $bannerCycleService): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $submission = BannerSubmission::query()->with('vendorProfile')->findOrFail($submissionId);
        $bannerCycleService->moveToWaitlist($submission);

        $vendorName = $submission->vendorProfile?->business_name ?? 'Fornecedor';
        $this->statusMessage = "{$vendorName} movido para a Lista de Espera.";
        $this->dispatch('toast', message: $this->statusMessage);
    }

    public function rejectBannerSubmission(int $submissionId, BannerCycleService $bannerCycleService): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $submission = BannerSubmission::query()->with('vendorProfile')->findOrFail($submissionId);
        $bannerCycleService->rejectSubmission($submission);

        $vendorName = $submission->vendorProfile?->business_name ?? 'Fornecedor';
        $this->statusMessage = "Candidatura de {$vendorName} recusada.";
        $this->dispatch('toast', message: $this->statusMessage);
    }

    public function render(): View
    {
        $bannerCycles = BannerCycle::query()
            ->with([
                'submissions' => fn ($query) => $query
                    ->with('vendorProfile')
                    ->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'pending' THEN 2 WHEN 'waitlist' THEN 3 ELSE 4 END")
                    ->orderBy('slot_number')
                    ->orderBy('id'),
            ])
            ->latest('starts_at')
            ->latest('id')
            ->get();

        return view('livewire.admin.platform-settings', [
            'bannerCycles' => $bannerCycles,
            'states' => BrazilLocations::states(),
            'citiesForCycleState' => BrazilLocations::citiesForState($this->cycle_state),
            'citiesByState' => BrazilLocations::allCitiesByState(),
            'submissionStatusLabels' => BannerSubmission::statusLabels(),
        ]);
    }

    private function resetCycleDefaults(PlatformSetting $settings): void
    {
        $this->cycle_duration_months = max(1, (int) $settings->banner_cycle_months);
        $this->cycle_max_slots = min(max(1, (int) $settings->banner_max_slots), BannerCycle::HARD_MAX_SLOTS);
        $this->cycle_price = (string) $settings->banner_cycle_price;
        $this->cycle_starts_at = now()->toDateString();
        $this->cycle_ends_at = now()->addMonths($this->cycle_duration_months)->toDateString();
    }

    private function syncCycleEndDate(): void
    {
        if (blank($this->cycle_starts_at)) {
            return;
        }

        try {
            $start = Carbon::parse($this->cycle_starts_at);
            $months = max(1, (int) $this->cycle_duration_months);
            $this->cycle_ends_at = $start->copy()->addMonths($months)->toDateString();
        } catch (\Throwable) {
            // Keep manual date when intermediate input is incomplete
        }
    }
}
