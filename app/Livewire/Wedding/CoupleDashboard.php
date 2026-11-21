<?php

namespace App\Livewire\Wedding;

use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingVendor;
use App\Support\BrazilLocations;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Painel dos Noivos — Nosso Voto')]
class CoupleDashboard extends Component
{
    public string $partner_1_name = '';

    public string $partner_2_name = '';

    public ?string $wedding_date = null;

    public string $city = '';

    public string $state = '';

    public ?string $venue_name = null;

    public string $estimated_budget = '0.00';

    public bool $showWeddingModal = false;

    public bool $showVendorModal = false;

    public ?int $editingVendorId = null;

    public string $vendor_name = '';

    public string $category = 'espaco';

    public ?string $contact_name = null;

    public ?string $contact_phone = null;

    public ?string $contact_email = null;

    public string $estimated_amount = '0.00';

    public string $contracted_amount = '0.00';

    public string $paid_amount = '0.00';

    public string $status = WeddingVendor::STATUS_QUOTING;

    public ?string $notes = null;

    public string $categoryFilter = 'all';

    public string $statusFilter = 'all';

    public string $search = '';

    public string $statusMessage = '';

    private ?Wedding $cachedWedding = null;

    private bool $weddingLoaded = false;

    public function mount(): void
    {
        $user = $this->authorizeCoupleAccess();
        $wedding = $this->resolveWedding($user);

        if ($wedding !== null) {
            $this->fillWeddingForm($wedding);

            return;
        }

        $this->prefillCoupleNamesFromUser($user);
    }

    public function openWeddingModal(): void
    {
        $user = $this->authorizeCoupleAccess();
        $this->statusMessage = '';
        $wedding = $this->resolveWedding($user);

        if ($wedding !== null) {
            $this->fillWeddingForm($wedding);
        } else {
            $this->prefillCoupleNamesFromUser($user);
        }

        $this->resetValidation();
        $this->showWeddingModal = true;
    }

    public function closeWeddingModal(): void
    {
        $this->statusMessage = '';
        $this->showWeddingModal = false;
        $this->resetValidation();
    }

    public function updatedState(): void
    {
        $this->statusMessage = '';
        $this->state = Str::upper(trim($this->state));

        if (! BrazilLocations::isValidCityForState($this->state, $this->city)) {
            $this->city = '';
        }
    }

    public function saveWedding(): void
    {
        $user = $this->authorizeCoupleAccess();

        $this->state = Str::upper(trim($this->state));
        $this->city = trim($this->city);

        $validated = $this->validate([
            'partner_1_name' => ['required', 'string', 'max:120'],
            'partner_2_name' => ['required', 'string', 'max:120'],
            'wedding_date' => ['nullable', 'date'],
            'state' => ['required', 'string', Rule::in(BrazilLocations::stateCodes())],
            'city' => ['required', 'string', Rule::in(BrazilLocations::citiesForState($this->state))],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'estimated_budget' => ['required', 'numeric', 'min:0'],
        ], [
            'partner_1_name.required' => 'Informe o nome do primeiro noivo(a).',
            'partner_2_name.required' => 'Informe o nome do segundo noivo(a).',
            'wedding_date.date' => 'Informe uma data válida para o casamento.',
            'state.required' => 'Selecione o estado (UF) do casamento.',
            'state.in' => 'Selecione um estado (UF) válido.',
            'city.required' => 'Selecione a cidade do casamento.',
            'city.in' => 'Selecione uma cidade válida para o estado escolhido.',
            'estimated_budget.required' => 'Informe o orçamento pretendido.',
            'estimated_budget.numeric' => 'O orçamento deve ser um valor numérico.',
            'estimated_budget.min' => 'O orçamento não pode ser negativo.',
        ]);

        $payload = [
            'partner_1_name' => trim($validated['partner_1_name']),
            'partner_2_name' => trim($validated['partner_2_name']),
            'wedding_date' => filled($validated['wedding_date']) ? $validated['wedding_date'] : null,
            'city' => trim($validated['city']),
            'state' => Str::upper(trim($validated['state'])),
            'venue_name' => filled($validated['venue_name']) ? trim((string) $validated['venue_name']) : null,
            'estimated_budget' => $validated['estimated_budget'],
        ];

        $wedding = $this->resolveWedding($user);

        if ($wedding !== null) {
            $wedding->update($payload);
            $message = 'Dados do casamento atualizados com sucesso.';
        } else {
            $wedding = Wedding::query()->create([
                'user_id' => $user->id,
                ...$payload,
            ]);
            $message = 'Casamento criado com sucesso! Agora você pode cadastrar seus fornecedores.';
        }

        $freshWedding = $wedding->fresh();
        $user->setRelation('wedding', $freshWedding);
        $this->cachedWedding = $freshWedding;
        $this->weddingLoaded = true;

        $this->fillWeddingForm($freshWedding);
        $this->showWeddingModal = false;
        $this->notify($message);
    }

    public function saveBudget(): void
    {
        $user = $this->authorizeCoupleAccess();
        $wedding = $this->resolveWedding($user);

        if ($wedding === null) {
            $this->openWeddingModal();

            return;
        }

        $validated = $this->validate([
            'estimated_budget' => ['required', 'numeric', 'min:0'],
        ], [
            'estimated_budget.required' => 'Informe o orçamento pretendido.',
            'estimated_budget.numeric' => 'O orçamento deve ser um valor numérico.',
            'estimated_budget.min' => 'O orçamento não pode ser negativo.',
        ]);

        $wedding->update([
            'estimated_budget' => $validated['estimated_budget'],
        ]);

        $freshWedding = $wedding->fresh();
        $this->cachedWedding = $freshWedding;
        $this->weddingLoaded = true;
        $this->estimated_budget = number_format((float) $freshWedding->estimated_budget, 2, '.', '');
        $this->notify('Orçamento pretendido atualizado com sucesso.');
    }

    public function openNewVendorModal(): void
    {
        $user = $this->authorizeCoupleAccess();
        $this->statusMessage = '';

        if ($this->resolveWedding($user) === null) {
            $this->openWeddingModal();

            return;
        }

        $this->resetVendorForm();
        $this->showVendorModal = true;
    }

    public function editVendor(int $vendorId): void
    {
        $wedding = $this->requireWedding();
        $this->statusMessage = '';
        $vendor = $wedding->vendors()->findOrFail($vendorId);

        $this->editingVendorId = $vendor->id;
        $this->vendor_name = $vendor->vendor_name;
        $this->category = $vendor->category;
        $this->contact_name = $vendor->contact_name;
        $this->contact_phone = $vendor->contact_phone;
        $this->contact_email = $vendor->contact_email;
        $this->estimated_amount = number_format((float) $vendor->estimated_amount, 2, '.', '');
        $this->contracted_amount = number_format((float) $vendor->contracted_amount, 2, '.', '');
        $this->paid_amount = number_format((float) $vendor->paid_amount, 2, '.', '');
        $this->status = $vendor->status;
        $this->notes = $vendor->notes;

        $this->resetValidation();
        $this->showVendorModal = true;
    }

    public function closeVendorModal(): void
    {
        $this->statusMessage = '';
        $this->showVendorModal = false;
        $this->resetVendorForm();
    }

    public function updatedCategoryFilter(): void
    {
        $this->statusMessage = '';
    }

    public function updatedStatusFilter(): void
    {
        $this->statusMessage = '';
    }

    public function updatedSearch(): void
    {
        $this->statusMessage = '';
    }

    public function saveVendor(): void
    {
        $wedding = $this->requireWedding();

        $validated = $this->validate([
            'vendor_name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(WeddingVendor::CATEGORIES)],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'estimated_amount' => ['required', 'numeric', 'min:0'],
            'contracted_amount' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(WeddingVendor::STATUSES)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'vendor_name.required' => 'Informe o nome do fornecedor.',
            'category.in' => 'Selecione uma categoria válida.',
            'contact_email.email' => 'Informe um e-mail de contato válido.',
            'estimated_amount.required' => 'Informe o teto previsto.',
            'estimated_amount.numeric' => 'O teto previsto deve ser numérico.',
            'estimated_amount.min' => 'O teto previsto não pode ser negativo.',
            'contracted_amount.required' => 'Informe o valor fechado.',
            'contracted_amount.numeric' => 'O valor fechado deve ser numérico.',
            'contracted_amount.min' => 'O valor fechado não pode ser negativo.',
            'paid_amount.required' => 'Informe o valor já pago.',
            'paid_amount.numeric' => 'O valor pago deve ser numérico.',
            'paid_amount.min' => 'O valor pago não pode ser negativo.',
            'status.in' => 'Selecione um status válido.',
        ]);

        $payload = [
            'vendor_name' => trim($validated['vendor_name']),
            'category' => $validated['category'],
            'contact_name' => filled($validated['contact_name']) ? trim((string) $validated['contact_name']) : null,
            'contact_phone' => filled($validated['contact_phone']) ? trim((string) $validated['contact_phone']) : null,
            'contact_email' => filled($validated['contact_email']) ? trim((string) $validated['contact_email']) : null,
            'estimated_amount' => $validated['estimated_amount'],
            'contracted_amount' => $validated['contracted_amount'],
            'paid_amount' => $validated['paid_amount'],
            'status' => $validated['status'],
            'notes' => filled($validated['notes']) ? trim((string) $validated['notes']) : null,
        ];

        if ($this->editingVendorId !== null) {
            $vendor = $wedding->vendors()->findOrFail($this->editingVendorId);
            $vendor->update($payload);
            $message = 'Fornecedor atualizado com sucesso.';
        } else {
            $wedding->vendors()->create([
                ...$payload,
                'vendor_profile_id' => null,
                'created_by_side' => WeddingVendor::SIDE_COUPLE,
            ]);
            $message = 'Fornecedor cadastrado com sucesso.';
        }

        $this->showVendorModal = false;
        $this->resetVendorForm();
        $this->notify($message);
    }

    public function deleteVendor(int $vendorId): void
    {
        $wedding = $this->requireWedding();
        $vendor = $wedding->vendors()->findOrFail($vendorId);
        $vendor->delete();

        if ($this->editingVendorId === $vendorId) {
            $this->showVendorModal = false;
            $this->resetVendorForm();
        }

        $this->notify('Fornecedor removido do planejamento.');
    }

    public function regenerateVendorInviteToken(int $vendorId): void
    {
        $wedding = $this->requireWedding();
        $vendor = $wedding->vendors()->findOrFail($vendorId);
        $vendor->regenerateInviteToken();

        $this->notify("Novo link de acesso gerado para {$vendor->vendor_name}.");
    }

    public function notifyInviteCopied(string $vendorName): void
    {
        $this->authorizeCoupleAccess();
        $this->notify("Link de acesso de {$vendorName} copiado para a área de transferência.");
    }

    public function regenerateToken(): void
    {
        $user = $this->authorizeCoupleAccess();
        $user->regenerateApiToken();

        $this->notify('Novo token de integração gerado com sucesso.');
    }

    public function render(): View
    {
        $user = $this->authorizeCoupleAccess();
        $wedding = $this->resolveWedding($user);

        $vendors = collect();
        $allVendorsCount = 0;
        $totalEstimatedVendors = 0.0;
        $totalContracted = 0.0;
        $totalPaid = 0.0;
        $remainingToPay = 0.0;
        $budgetBalance = 0.0;
        $isOverBudget = false;
        $daysUntilWedding = null;

        if ($wedding !== null) {
            $daysUntilWedding = $wedding->daysUntilWedding();

            $query = $wedding->vendors()->latest('id');
            $hasFilters = false;

            if ($this->categoryFilter !== 'all' && in_array($this->categoryFilter, WeddingVendor::CATEGORIES, true)) {
                $query->where('category', $this->categoryFilter);
                $hasFilters = true;
            }

            if ($this->statusFilter !== 'all' && in_array($this->statusFilter, WeddingVendor::STATUSES, true)) {
                $query->where('status', $this->statusFilter);
                $hasFilters = true;
            }

            if (filled($this->search)) {
                $term = '%'.trim($this->search).'%';
                $query->where(function ($builder) use ($term): void {
                    $builder->whereLike('vendor_name', $term, caseSensitive: false)
                        ->orWhereLike('contact_name', $term, caseSensitive: false)
                        ->orWhereLike('notes', $term, caseSensitive: false);
                });
                $hasFilters = true;
            }

            $vendors = $query->get();

            if (! $hasFilters) {
                $allVendorsCount = $vendors->count();
                $totalEstimatedVendors = round((float) $vendors->sum('estimated_amount'), 2);
                $totalContracted = round((float) $vendors->sum('contracted_amount'), 2);
                $totalPaid = round((float) $vendors->sum('paid_amount'), 2);
            } else {
                $totals = $wedding->vendors()
                    ->selectRaw('COUNT(*) as total_count, COALESCE(SUM(estimated_amount), 0) as total_estimated, COALESCE(SUM(contracted_amount), 0) as total_contracted, COALESCE(SUM(paid_amount), 0) as total_paid')
                    ->first();

                $allVendorsCount = (int) ($totals->total_count ?? 0);
                $totalEstimatedVendors = round((float) ($totals->total_estimated ?? 0), 2);
                $totalContracted = round((float) ($totals->total_contracted ?? 0), 2);
                $totalPaid = round((float) ($totals->total_paid ?? 0), 2);
            }

            $remainingToPay = round($totalContracted - $totalPaid, 2);
            $budgetBalance = round((float) $wedding->estimated_budget - $totalContracted, 2);
            $isOverBudget = $totalContracted > (float) $wedding->estimated_budget;
        }

        return view('livewire.wedding.couple-dashboard', [
            'user' => $user,
            'wedding' => $wedding,
            'vendors' => $vendors,
            'allVendorsCount' => $allVendorsCount,
            'totalEstimatedVendors' => $totalEstimatedVendors,
            'totalContracted' => $totalContracted,
            'totalPaid' => $totalPaid,
            'remainingToPay' => $remainingToPay,
            'budgetBalance' => $budgetBalance,
            'isOverBudget' => $isOverBudget,
            'daysUntilWedding' => $daysUntilWedding,
            'categoryLabels' => WeddingVendor::categoryLabels(),
            'statusLabels' => WeddingVendor::statusLabels(),
            'states' => BrazilLocations::states(),
            'citiesForSelectedState' => BrazilLocations::citiesForState($this->state),
            'citiesByState' => BrazilLocations::allCitiesByState(),
        ]);
    }

    private function authorizeCoupleAccess(): User
    {
        $user = Auth::user();

        abort_unless($user !== null && ($user->isCouple() || $user->isAdmin()), 403);

        return $user;
    }

    private function resolveWedding(User $user): ?Wedding
    {
        if (! $this->weddingLoaded) {
            $this->cachedWedding = $user->wedding()->first();
            $this->weddingLoaded = true;
        }

        return $this->cachedWedding;
    }

    private function requireWedding(): Wedding
    {
        $user = $this->authorizeCoupleAccess();
        $wedding = $this->resolveWedding($user);

        abort_if($wedding === null, 404);

        return $wedding;
    }

    private function fillWeddingForm(Wedding $wedding): void
    {
        $this->partner_1_name = $wedding->partner_1_name;
        $this->partner_2_name = $wedding->partner_2_name;
        $this->wedding_date = $wedding->wedding_date?->format('Y-m-d');
        $this->city = $wedding->city;
        $this->state = $wedding->state;
        $this->venue_name = $wedding->venue_name;
        $this->estimated_budget = number_format((float) $wedding->estimated_budget, 2, '.', '');
    }

    private function prefillCoupleNamesFromUser(User $user): void
    {
        if (str_contains($user->name, '&')) {
            [$first, $second] = array_map('trim', explode('&', $user->name, 2));
            $this->partner_1_name = $first;
            $this->partner_2_name = $second;

            return;
        }

        $this->partner_1_name = $user->name;
        $this->partner_2_name = '';
    }

    private function resetVendorForm(): void
    {
        $this->editingVendorId = null;
        $this->vendor_name = '';
        $this->category = 'espaco';
        $this->contact_name = null;
        $this->contact_phone = null;
        $this->contact_email = null;
        $this->estimated_amount = '0.00';
        $this->contracted_amount = '0.00';
        $this->paid_amount = '0.00';
        $this->status = WeddingVendor::STATUS_QUOTING;
        $this->notes = null;
        $this->resetValidation();
    }

    private function notify(string $message): void
    {
        $this->statusMessage = $message;
        $this->dispatch('toast', message: $message);
    }
}
