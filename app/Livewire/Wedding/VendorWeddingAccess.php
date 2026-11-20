<?php

namespace App\Livewire\Wedding;

use App\Models\Wedding;
use App\Models\WeddingVendor;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Acesso do Profissional — Nosso Voto')]
class VendorWeddingAccess extends Component
{
    public string $token = '';

    public bool $showVendorModal = false;

    public ?int $editingVendorId = null;

    public string $vendor_name = '';

    public string $category = 'buffet';

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

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->resolveAccessVendor();
    }

    public function openNewVendorModal(): void
    {
        $this->authorizeCerimonialistaAccess();
        $this->statusMessage = '';
        $this->resetVendorForm();
        $this->showVendorModal = true;
    }

    public function editVendor(int $vendorId): void
    {
        $accessVendor = $this->authorizeCerimonialistaAccess();
        $this->statusMessage = '';

        $vendor = $accessVendor->wedding->vendors()->findOrFail($vendorId);
        $this->fillVendorForm($vendor);

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
        $accessVendor = $this->authorizeCerimonialistaAccess();
        $wedding = $accessVendor->wedding;
        $payload = $this->validateVendorPayload();

        if ($this->editingVendorId !== null) {
            $vendor = $wedding->vendors()->findOrFail($this->editingVendorId);
            $vendor->update($payload);
            $message = "Fornecedor {$vendor->vendor_name} atualizado com sucesso.";
        } else {
            $vendor = $wedding->vendors()->create([
                ...$payload,
                'vendor_profile_id' => null,
                'created_by_side' => WeddingVendor::SIDE_COUPLE,
            ]);
            $message = "Fornecedor {$vendor->vendor_name} adicionado ao casamento com sucesso.";
        }

        $this->showVendorModal = false;
        $this->resetVendorForm();
        $this->notify($message);
    }

    public function updateVendorStatus(int $vendorId, string $status): void
    {
        $accessVendor = $this->authorizeCerimonialistaAccess();

        abort_unless(in_array($status, WeddingVendor::STATUSES, true), 422);

        $vendor = $accessVendor->wedding->vendors()->findOrFail($vendorId);
        $vendor->update(['status' => $status]);

        $this->notify("Status de {$vendor->vendor_name} atualizado para {$vendor->statusLabel()}.");
    }

    public function render(): View
    {
        $accessVendor = $this->resolveAccessVendor();
        $wedding = $accessVendor->wedding;
        $isCerimonialista = $accessVendor->isCerimonialista();

        $vendors = collect();
        $summary = [
            'totalVendorsCount' => 0,
            'quotingCount' => 0,
            'contractedCount' => 0,
            'paidCount' => 0,
            'totalContracted' => 0.0,
            'totalPaid' => 0.0,
            'remainingToPay' => 0.0,
        ];

        if ($isCerimonialista) {
            $summary = $this->buildExecutiveSummary($wedding);
            $vendors = $this->filteredWeddingVendors($wedding);
        }

        return view('livewire.wedding.vendor-wedding-access', [
            'accessVendor' => $accessVendor,
            'wedding' => $wedding,
            'isCerimonialista' => $isCerimonialista,
            'vendors' => $vendors,
            'daysUntilWedding' => $wedding->daysUntilWedding(),
            'categoryLabels' => WeddingVendor::categoryLabels(),
            'statusLabels' => WeddingVendor::statusLabels(),
            ...$summary,
        ]);
    }

    private function resolveAccessVendor(): WeddingVendor
    {
        $vendor = WeddingVendor::query()
            ->with('wedding')
            ->where('invite_token', $this->token)
            ->firstOrFail();

        abort_if($vendor->wedding === null, 404);

        return $vendor;
    }

    private function authorizeCerimonialistaAccess(): WeddingVendor
    {
        $vendor = $this->resolveAccessVendor();

        abort_unless($vendor->isCerimonialista(), 403);

        return $vendor;
    }

    /**
     * Compute contract counts and financial totals across all wedding vendors.
     *
     * @return array{
     *     totalVendorsCount: int,
     *     quotingCount: int,
     *     contractedCount: int,
     *     paidCount: int,
     *     totalContracted: float,
     *     totalPaid: float,
     *     remainingToPay: float,
     * }
     */
    private function buildExecutiveSummary(Wedding $wedding): array
    {
        $allVendors = $wedding->vendors()->get(['status', 'contracted_amount', 'paid_amount']);
        $totalContracted = round((float) $allVendors->sum('contracted_amount'), 2);
        $totalPaid = round((float) $allVendors->sum('paid_amount'), 2);

        return [
            'totalVendorsCount' => $allVendors->count(),
            'quotingCount' => $allVendors->where('status', WeddingVendor::STATUS_QUOTING)->count(),
            'contractedCount' => $allVendors->where('status', WeddingVendor::STATUS_CONTRACTED)->count(),
            'paidCount' => $allVendors->where('status', WeddingVendor::STATUS_PAID)->count(),
            'totalContracted' => $totalContracted,
            'totalPaid' => $totalPaid,
            'remainingToPay' => round($totalContracted - $totalPaid, 2),
        ];
    }

    /**
     * Load the wedding's vendors matching the active search, category, and status filters.
     *
     * @return Collection<int, WeddingVendor>
     */
    private function filteredWeddingVendors(Wedding $wedding): Collection
    {
        $query = $wedding->vendors()->orderBy('category')->orderBy('vendor_name');

        if ($this->categoryFilter !== 'all' && in_array($this->categoryFilter, WeddingVendor::CATEGORIES, true)) {
            $query->where('category', $this->categoryFilter);
        }

        if ($this->statusFilter !== 'all' && in_array($this->statusFilter, WeddingVendor::STATUSES, true)) {
            $query->where('status', $this->statusFilter);
        }

        if (filled($this->search)) {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($builder) use ($term): void {
                $builder->whereLike('vendor_name', $term, caseSensitive: false)
                    ->orWhereLike('contact_name', $term, caseSensitive: false)
                    ->orWhereLike('contact_email', $term, caseSensitive: false)
                    ->orWhereLike('notes', $term, caseSensitive: false);
            });
        }

        return $query->get();
    }

    /**
     * Validate and normalize vendor input fields before persisting.
     *
     * @return array<string, mixed>
     */
    private function validateVendorPayload(): array
    {
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

        return [
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
    }

    private function fillVendorForm(WeddingVendor $vendor): void
    {
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
    }

    private function resetVendorForm(): void
    {
        $this->editingVendorId = null;
        $this->vendor_name = '';
        $this->category = 'buffet';
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

