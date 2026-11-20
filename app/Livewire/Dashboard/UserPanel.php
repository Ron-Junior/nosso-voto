<?php

namespace App\Livewire\Dashboard;

use App\Models\PlatformSetting;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\WeddingVendor;
use App\Support\BrazilLocations;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Painel — Nosso Voto')]
class UserPanel extends Component
{
    public string $statusMessage = '';

    // Vendor profile fields
    public bool $showProfileModal = false;

    public string $business_name = '';

    public string $profile_category = 'fotografia';

    public string $state = '';

    public string $city = '';

    public string $whatsapp = '';

    public ?string $instagram = null;

    public ?string $description = null;

    public ?string $cover_image_url = null;

    public ?string $price_from = null;

    // Schedule entry modal & form fields
    public bool $showScheduleModal = false;

    public ?int $editingEntryId = null;

    public string $couple_names = '';

    public ?string $event_date = null;

    public ?string $event_city = null;

    public ?string $event_venue = null;

    public ?string $contact_name = null;

    public ?string $contact_phone = null;

    public ?string $contact_email = null;

    public string $contracted_amount = '0.00';

    public string $paid_amount = '0.00';

    public string $status = WeddingVendor::STATUS_CONTRACTED;

    public ?string $notes = null;

    // Schedule filters
    public string $periodFilter = 'upcoming';

    public string $statusFilter = 'all';

    public string $search = '';

    public function mount(): void
    {
        $user = Auth::user();

        if ($user === null || ! $this->canManageVendorArea($user)) {
            return;
        }

        $profile = $user->vendorProfile()->first();

        if ($profile !== null) {
            $this->fillProfileForm($profile);

            return;
        }

        $this->prefillProfileFormFromUser($user);
    }

    public function openProfileModal(): void
    {
        $user = $this->authorizeVendorAccess();
        $this->statusMessage = '';

        $profile = $user->vendorProfile()->first();

        if ($profile !== null) {
            $this->fillProfileForm($profile);
        } else {
            $this->prefillProfileFormFromUser($user);
        }

        $this->resetValidation();
        $this->showProfileModal = true;
    }

    public function closeProfileModal(): void
    {
        $this->statusMessage = '';
        $this->showProfileModal = false;
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

    public function saveProfile(): void
    {
        $this->saveVendorProfile();
    }

    public function saveVendorProfile(): void
    {
        $user = $this->authorizeVendorAccess();

        $this->state = Str::upper(trim($this->state));
        $this->city = trim($this->city);

        $validated = $this->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'profile_category' => ['required', Rule::in(WeddingVendor::CATEGORIES)],
            'state' => ['required', 'string', Rule::in(BrazilLocations::stateCodes())],
            'city' => ['required', 'string', Rule::in(BrazilLocations::citiesForState($this->state))],
            'whatsapp' => ['required', 'string', 'max:50'],
            'instagram' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cover_image_url' => ['nullable', 'url', 'max:2048'],
            'price_from' => ['nullable', 'numeric', 'min:0'],
        ], [
            'business_name.required' => 'Informe o nome comercial ou fantasia do seu negócio.',
            'profile_category.in' => 'Selecione uma categoria válida.',
            'state.required' => 'Selecione o estado (UF) de atuação.',
            'state.in' => 'Selecione um estado (UF) válido.',
            'city.required' => 'Selecione a cidade de atuação.',
            'city.in' => 'Selecione uma cidade válida para o estado escolhido.',
            'whatsapp.required' => 'Informe o WhatsApp comercial para atendimento.',
            'cover_image_url.url' => 'Informe uma URL válida para a imagem de capa.',
            'price_from.numeric' => 'O valor inicial deve ser numérico.',
            'price_from.min' => 'O valor inicial não pode ser negativo.',
        ]);

        $payload = [
            'business_name' => trim($validated['business_name']),
            'category' => $validated['profile_category'],
            'state' => Str::upper(trim($validated['state'])),
            'city' => trim($validated['city']),
            'whatsapp' => trim($validated['whatsapp']),
            'instagram' => filled($validated['instagram']) ? trim((string) $validated['instagram']) : null,
            'description' => filled($validated['description']) ? trim((string) $validated['description']) : null,
            'cover_image_url' => filled($validated['cover_image_url']) ? trim((string) $validated['cover_image_url']) : null,
            'price_from' => filled($validated['price_from']) ? $validated['price_from'] : null,
        ];

        $profile = $user->vendorProfile()->first();

        if ($profile !== null) {
            $profile->update($payload);
            $message = 'Perfil profissional atualizado com sucesso.';
        } else {
            $profile = VendorProfile::query()->create([
                'user_id' => $user->id,
                ...$payload,
            ]);
            $message = 'Perfil profissional criado com sucesso!';
        }

        $freshProfile = $profile->fresh();
        $user->setRelation('vendorProfile', $freshProfile);

        $this->fillProfileForm($freshProfile);
        $this->showProfileModal = false;
        $this->notify($message);
    }

    public function openNewScheduleModal(): void
    {
        $this->authorizeVendorAccess();
        $this->statusMessage = '';
        $this->resetScheduleForm();
        $this->showScheduleModal = true;
    }

    public function closeScheduleModal(): void
    {
        $this->statusMessage = '';
        $this->showScheduleModal = false;
        $this->resetScheduleForm();
    }

    public function editScheduleEntry(int $entryId): void
    {
        $profile = $this->requireVendorProfile();
        $this->statusMessage = '';

        $entry = $profile->scheduleEntries()->with('wedding')->findOrFail($entryId);

        $this->editingEntryId = $entry->id;
        $resolvedNames = $entry->resolvedCoupleNames();
        $this->couple_names = $resolvedNames === 'Casal a definir' ? ($entry->couple_names ?? '') : $resolvedNames;
        $this->event_date = $entry->resolvedEventDate()?->format('Y-m-d');
        $this->event_city = $entry->resolvedEventCity();
        $this->event_venue = $entry->resolvedEventVenue();
        $this->contact_name = $entry->contact_name;
        $this->contact_phone = $entry->contact_phone;
        $this->contact_email = $entry->contact_email;
        $this->contracted_amount = number_format((float) $entry->contracted_amount, 2, '.', '');
        $this->paid_amount = number_format((float) $entry->paid_amount, 2, '.', '');
        $this->status = $entry->status;
        $this->notes = $entry->notes;

        $this->resetValidation();
        $this->showScheduleModal = true;
    }

    public function saveScheduleEntry(): void
    {
        $user = $this->authorizeVendorAccess();
        $profile = $this->resolveOrCreateVendorProfile($user);

        $validated = $this->validate([
            'couple_names' => ['required', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'event_city' => ['nullable', 'string', 'max:255'],
            'event_venue' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contracted_amount' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(WeddingVendor::STATUSES)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'couple_names.required' => 'Informe os nomes dos noivos (ex.: Ana & Lucas).',
            'event_date.date' => 'Informe uma data válida para o casamento.',
            'contact_email.email' => 'Informe um e-mail de contato válido.',
            'contracted_amount.required' => 'Informe o valor contratado.',
            'contracted_amount.numeric' => 'O valor contratado deve ser numérico.',
            'contracted_amount.min' => 'O valor contratado não pode ser negativo.',
            'paid_amount.required' => 'Informe o valor já recebido.',
            'paid_amount.numeric' => 'O valor recebido deve ser numérico.',
            'paid_amount.min' => 'O valor recebido não pode ser negativo.',
            'status.in' => 'Selecione um status válido.',
        ]);

        $payload = [
            'couple_names' => trim($validated['couple_names']),
            'event_date' => filled($validated['event_date']) ? $validated['event_date'] : null,
            'event_city' => filled($validated['event_city']) ? trim((string) $validated['event_city']) : null,
            'event_venue' => filled($validated['event_venue']) ? trim((string) $validated['event_venue']) : null,
            'contact_name' => filled($validated['contact_name']) ? trim((string) $validated['contact_name']) : null,
            'contact_phone' => filled($validated['contact_phone']) ? trim((string) $validated['contact_phone']) : null,
            'contact_email' => filled($validated['contact_email']) ? trim((string) $validated['contact_email']) : null,
            'contracted_amount' => $validated['contracted_amount'],
            'paid_amount' => $validated['paid_amount'],
            'status' => $validated['status'],
            'notes' => filled($validated['notes']) ? trim((string) $validated['notes']) : null,
        ];

        if ($this->editingEntryId !== null) {
            $entry = $profile->scheduleEntries()->findOrFail($this->editingEntryId);
            $entry->update($payload);
            $message = 'Casamento na agenda atualizado com sucesso.';
        } else {
            $profile->scheduleEntries()->create([
                ...$payload,
                'wedding_id' => null,
                'created_by_side' => WeddingVendor::SIDE_VENDOR,
                'vendor_name' => $profile->business_name,
                'category' => $profile->category,
                'estimated_amount' => 0,
            ]);
            $message = 'Casamento adicionado à agenda com sucesso.';
        }

        $this->showScheduleModal = false;
        $this->resetScheduleForm();
        $this->notify($message);
    }

    public function updateScheduleStatus(int $entryId, string $status): void
    {
        $profile = $this->requireVendorProfile();

        abort_unless(in_array($status, WeddingVendor::STATUSES, true), 422);

        $entry = $profile->scheduleEntries()->with('wedding')->findOrFail($entryId);
        $entry->update(['status' => $status]);

        $this->notify("Status de {$entry->resolvedCoupleNames()} atualizado para {$entry->statusLabel()}.");
    }

    public function deleteScheduleEntry(int $entryId): void
    {
        $profile = $this->requireVendorProfile();
        $entry = $profile->scheduleEntries()->findOrFail($entryId);

        if ($entry->wedding_id === null || $entry->created_by_side === WeddingVendor::SIDE_VENDOR) {
            $entry->delete();
            $message = 'Casamento removido da sua agenda.';
        } else {
            $entry->update(['vendor_profile_id' => null]);
            $message = 'Casamento desvinculado da sua agenda.';
        }

        if ($this->editingEntryId === $entryId) {
            $this->showScheduleModal = false;
            $this->resetScheduleForm();
        }

        $this->notify($message);
    }

    public function setPeriodFilter(string $period): void
    {
        $this->statusMessage = '';

        if (in_array($period, ['upcoming', 'past', 'all'], true)) {
            $this->periodFilter = $period;
        }
    }

    public function updatedPeriodFilter(): void
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

    public function regenerateEntryInviteToken(int $entryId): void
    {
        $profile = $this->requireVendorProfile();
        $entry = $profile->scheduleEntries()->with('wedding')->findOrFail($entryId);
        $entry->regenerateInviteToken();

        $this->notify("Novo código de vínculo gerado para {$entry->resolvedCoupleNames()}.");
    }

    public function notifyInviteCopied(string $coupleNames): void
    {
        $this->authorizeVendorAccess();
        $this->notify("Código de vínculo de {$coupleNames} copiado para a área de transferência.");
    }

    public function linkEmailWeddingToAgenda(int $weddingVendorId): void
    {
        $user = $this->authorizeVendorAccess();
        $normalizedEmail = Str::lower(trim((string) $user->email));

        abort_if($normalizedEmail === '', 404);

        $weddingVendor = WeddingVendor::query()
            ->with('wedding')
            ->whereNotNull('wedding_id')
            ->whereNull('vendor_profile_id')
            ->whereRaw('LOWER(TRIM(contact_email)) = ?', [$normalizedEmail])
            ->findOrFail($weddingVendorId);

        $profile = $this->resolveOrCreateVendorProfile($user);
        $weddingVendor->forceFill(['vendor_profile_id' => $profile->id])->save();

        $coupleTitle = $weddingVendor->wedding?->coupleTitle() ?? $weddingVendor->resolvedCoupleNames();
        $this->notify("Casamento de {$coupleTitle} adicionado à sua agenda.");
    }

    public function regenerateToken(): void
    {
        $user = Auth::user();

        abort_unless($user !== null, 403);

        $user->regenerateApiToken();

        $this->notify('Novo token de integração gerado com sucesso.');
    }

    public function render(): View
    {
        return view('livewire.dashboard.user-panel', $this->viewData(false));
    }

    /**
     * Build shared view data for vendor panel and schedule components.
     *
     * @return array<string, mixed>
     */
    protected function viewData(bool $includeScheduleEntries = false): array
    {
        $user = Auth::user();
        $isVendorArea = $user !== null && $this->canManageVendorArea($user);
        $vendorProfile = $isVendorArea ? $user->vendorProfile()->first() : null;

        $scheduleEntries = collect();
        $scheduleSummary = [
            'totalEntriesCount' => 0,
            'upcomingCount' => 0,
            'pastCount' => 0,
            'totalContracted' => 0.0,
            'totalPaid' => 0.0,
            'remainingToReceive' => 0.0,
        ];

        if ($vendorProfile !== null) {
            $scheduleSummary = $this->buildScheduleSummary($vendorProfile);

            if ($includeScheduleEntries) {
                $scheduleEntries = $this->resolveScheduleEntries($vendorProfile);
            }
        }

        return [
            'user' => $user,
            'isVendorArea' => $isVendorArea,
            'settings' => PlatformSetting::current(),
            'vendorProfile' => $vendorProfile,
            'scheduleEntries' => $scheduleEntries,
            'scheduleSummary' => $scheduleSummary,
            'linkedWeddingVendors' => $this->resolveLinkedWeddingVendors($user),
            'categoryLabels' => WeddingVendor::categoryLabels(),
            'statusLabels' => WeddingVendor::statusLabels(),
            'states' => BrazilLocations::states(),
            'citiesForSelectedState' => BrazilLocations::citiesForState($this->state),
        ];
    }

    private function canManageVendorArea(User $user): bool
    {
        return $user->isVendor() || $user->isAdmin();
    }

    private function authorizeVendorAccess(): User
    {
        $user = Auth::user();

        abort_unless($user !== null && $this->canManageVendorArea($user), 403);

        return $user;
    }

    private function requireVendorProfile(): VendorProfile
    {
        $user = $this->authorizeVendorAccess();
        $profile = $user->vendorProfile()->first();

        abort_if($profile === null, 404);

        return $profile;
    }

    private function resolveOrCreateVendorProfile(User $user): VendorProfile
    {
        $profile = $user->vendorProfile()->first();

        if ($profile !== null) {
            return $profile;
        }

        $defaultState = in_array(Str::upper(trim($this->state)), BrazilLocations::stateCodes(), true)
            ? Str::upper(trim($this->state))
            : 'SP';
        $cities = BrazilLocations::citiesForState($defaultState);
        $defaultCity = BrazilLocations::isValidCityForState($defaultState, $this->city)
            ? trim($this->city)
            : ($cities[0] ?? 'São Paulo');

        $profile = VendorProfile::query()->create([
            'user_id' => $user->id,
            'business_name' => filled($this->business_name) ? trim($this->business_name) : $user->name,
            'category' => in_array($this->profile_category, WeddingVendor::CATEGORIES, true)
                ? $this->profile_category
                : 'outros',
            'state' => $defaultState,
            'city' => $defaultCity,
            'whatsapp' => filled($this->whatsapp)
                ? trim($this->whatsapp)
                : (filled($this->contact_phone) ? trim((string) $this->contact_phone) : '(11) 99999-0000'),
            'instagram' => filled($this->instagram) ? trim((string) $this->instagram) : null,
            'description' => filled($this->description) ? trim((string) $this->description) : null,
            'cover_image_url' => filled($this->cover_image_url) ? trim((string) $this->cover_image_url) : null,
            'price_from' => filled($this->price_from) ? $this->price_from : null,
        ]);

        $freshProfile = $profile->fresh();
        $user->setRelation('vendorProfile', $freshProfile);
        $this->fillProfileForm($freshProfile);

        return $freshProfile;
    }

    /**
     * Compute counts and financial totals across all schedule entries for this vendor profile.
     *
     * @return array{
     *     totalEntriesCount: int,
     *     upcomingCount: int,
     *     pastCount: int,
     *     totalContracted: float,
     *     totalPaid: float,
     *     remainingToReceive: float,
     * }
     */
    private function buildScheduleSummary(VendorProfile $profile): array
    {
        $today = now()->toDateString();

        $row = WeddingVendor::query()
            ->leftJoin('weddings', 'weddings.id', '=', 'wedding_vendors.wedding_id')
            ->where('wedding_vendors.vendor_profile_id', $profile->id)
            ->selectRaw('
                COUNT(*) as total_count,
                COALESCE(SUM(wedding_vendors.contracted_amount), 0) as total_contracted,
                COALESCE(SUM(wedding_vendors.paid_amount), 0) as total_paid,
                COALESCE(SUM(CASE WHEN COALESCE(wedding_vendors.event_date, weddings.wedding_date) >= ? OR COALESCE(wedding_vendors.event_date, weddings.wedding_date) IS NULL THEN 1 ELSE 0 END), 0) as upcoming_count,
                COALESCE(SUM(CASE WHEN COALESCE(wedding_vendors.event_date, weddings.wedding_date) < ? THEN 1 ELSE 0 END), 0) as past_count
            ', [$today, $today])
            ->first();

        $totalContracted = round((float) ($row->total_contracted ?? 0), 2);
        $totalPaid = round((float) ($row->total_paid ?? 0), 2);

        return [
            'totalEntriesCount' => (int) ($row->total_count ?? 0),
            'upcomingCount' => (int) ($row->upcoming_count ?? 0),
            'pastCount' => (int) ($row->past_count ?? 0),
            'totalContracted' => $totalContracted,
            'totalPaid' => $totalPaid,
            'remainingToReceive' => round($totalContracted - $totalPaid, 2),
        ];
    }

    /**
     * Load unified chronological agenda entries combining platform couples and manual entries.
     *
     * @return Collection<int, WeddingVendor>
     */
    private function resolveScheduleEntries(VendorProfile $profile): Collection
    {
        $today = now()->toDateString();

        $query = WeddingVendor::query()
            ->select('wedding_vendors.*')
            ->with('wedding')
            ->leftJoin('weddings', 'weddings.id', '=', 'wedding_vendors.wedding_id')
            ->where('wedding_vendors.vendor_profile_id', $profile->id);

        if ($this->periodFilter === 'upcoming') {
            $query->where(function ($builder) use ($today): void {
                $builder->whereRaw('COALESCE(wedding_vendors.event_date, weddings.wedding_date) >= ?', [$today])
                    ->orWhereRaw('COALESCE(wedding_vendors.event_date, weddings.wedding_date) IS NULL');
            })
                ->orderByRaw('COALESCE(wedding_vendors.event_date, weddings.wedding_date) ASC NULLS LAST')
                ->orderBy('wedding_vendors.id');
        } elseif ($this->periodFilter === 'past') {
            $query->whereRaw('COALESCE(wedding_vendors.event_date, weddings.wedding_date) < ?', [$today])
                ->orderByRaw('COALESCE(wedding_vendors.event_date, weddings.wedding_date) DESC NULLS LAST')
                ->orderByDesc('wedding_vendors.id');
        } else {
            $query->orderByRaw('COALESCE(wedding_vendors.event_date, weddings.wedding_date) ASC NULLS LAST')
                ->orderBy('wedding_vendors.id');
        }

        if ($this->statusFilter !== 'all' && in_array($this->statusFilter, WeddingVendor::STATUSES, true)) {
            $query->where('wedding_vendors.status', $this->statusFilter);
        }

        if (filled($this->search)) {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($builder) use ($term): void {
                $builder->whereLike('wedding_vendors.couple_names', $term, caseSensitive: false)
                    ->orWhereLike('wedding_vendors.event_city', $term, caseSensitive: false)
                    ->orWhereLike('wedding_vendors.event_venue', $term, caseSensitive: false)
                    ->orWhereLike('wedding_vendors.contact_name', $term, caseSensitive: false)
                    ->orWhereLike('wedding_vendors.notes', $term, caseSensitive: false)
                    ->orWhereLike('weddings.partner_1_name', $term, caseSensitive: false)
                    ->orWhereLike('weddings.partner_2_name', $term, caseSensitive: false)
                    ->orWhereLike('weddings.city', $term, caseSensitive: false)
                    ->orWhereLike('weddings.venue_name', $term, caseSensitive: false)
                    ->orWhereRaw("(COALESCE(weddings.partner_1_name, '') || ' & ' || COALESCE(weddings.partner_2_name, '')) ILIKE ?", [$term]);
            });
        }

        return $query->get();
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

    private function fillProfileForm(VendorProfile $profile): void
    {
        $this->business_name = $profile->business_name;
        $this->profile_category = $profile->category;
        $this->state = $profile->state;
        $this->city = $profile->city;
        $this->whatsapp = $profile->whatsapp;
        $this->instagram = $profile->instagram;
        $this->description = $profile->description;
        $this->cover_image_url = $profile->cover_image_url;
        $this->price_from = $profile->price_from !== null
            ? number_format((float) $profile->price_from, 2, '.', '')
            : null;
    }

    private function prefillProfileFormFromUser(User $user): void
    {
        $this->business_name = $user->name;
        $this->profile_category = 'fotografia';
        $this->state = 'SP';
        $this->city = '';
        $this->whatsapp = '';
        $this->instagram = null;
        $this->description = null;
        $this->cover_image_url = null;
        $this->price_from = null;
    }

    private function resetScheduleForm(): void
    {
        $this->editingEntryId = null;
        $this->couple_names = '';
        $this->event_date = null;
        $this->event_city = null;
        $this->event_venue = null;
        $this->contact_name = null;
        $this->contact_phone = null;
        $this->contact_email = null;
        $this->contracted_amount = '0.00';
        $this->paid_amount = '0.00';
        $this->status = WeddingVendor::STATUS_CONTRACTED;
        $this->notes = null;
        $this->resetValidation();
    }

    private function notify(string $message): void
    {
        $this->statusMessage = $message;
        $this->dispatch('toast', message: $message);
    }
}
