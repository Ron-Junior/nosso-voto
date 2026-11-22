<?php

namespace App\Livewire\Vendor;

use App\Models\BannerCycle;
use App\Models\BannerSubmission;
use App\Models\VendorProfile;
use App\Models\WeddingVendor;
use App\Support\BrazilLocations;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Vitrine Regional de Fornecedores — Nosso Voto')]
class VendorShowcase extends Component
{
    #[Url(as: 'uf', except: '')]
    public string $stateFilter = '';

    #[Url(as: 'cidade', except: '')]
    public string $cityFilter = '';

    #[Url(as: 'categoria', except: '')]
    public string $categoryFilter = '';

    #[Url(as: 'busca', except: '')]
    public string $search = '';

    public ?int $selectedVendorId = null;

    public string $statusMessage = '';

    public function mount(?string $slug = null): void
    {
        if (filled($this->stateFilter)) {
            $this->stateFilter = Str::upper(trim($this->stateFilter));
        }

        if ($this->stateFilter !== '' && ! in_array($this->stateFilter, BrazilLocations::stateCodes(), true)) {
            $this->stateFilter = '';
            $this->cityFilter = '';
        }

        if ($slug !== null) {
            $profile = VendorProfile::query()->where('slug', $slug)->firstOrFail();
            $this->selectedVendorId = $profile->id;
        }
    }

    public function updatedStateFilter(): void
    {
        $this->statusMessage = '';
        $this->stateFilter = Str::upper(trim($this->stateFilter));

        if ($this->stateFilter === '' || ! BrazilLocations::isValidCityForState($this->stateFilter, $this->cityFilter)) {
            $this->cityFilter = '';
        }
    }

    public function updatedCityFilter(): void
    {
        $this->statusMessage = '';
        $this->cityFilter = trim($this->cityFilter);
    }

    public function updatedCategoryFilter(): void
    {
        $this->statusMessage = '';
    }

    public function updatedSearch(): void
    {
        $this->statusMessage = '';
    }

    public function applyCoupleRegion(): void
    {
        $this->statusMessage = '';
        $user = Auth::user();
        $wedding = $user?->wedding()->first();

        $regionState = $wedding?->state ?? $user?->state;
        $regionCity = $wedding?->city ?? $user?->city;

        if (blank($regionState) || blank($regionCity)) {
            return;
        }

        $this->stateFilter = Str::upper(trim((string) $regionState));
        $this->cityFilter = trim((string) $regionCity);
    }

    public function clearFilters(): void
    {
        $this->statusMessage = '';
        $this->stateFilter = '';
        $this->cityFilter = '';
        $this->categoryFilter = '';
        $this->search = '';
    }

    public function openVendorDetails(int $vendorProfileId): void
    {
        $this->statusMessage = '';
        $profile = VendorProfile::query()->findOrFail($vendorProfileId);
        $this->selectedVendorId = $profile->id;
    }

    public function closeVendorDetails(): void
    {
        $this->statusMessage = '';
        $this->selectedVendorId = null;
    }

    public function linkVendorToWedding(int $vendorProfileId): void
    {
        $user = Auth::user();

        abort_unless($user !== null && ($user->isCouple() || $user->isAdmin()), 403);

        $vendorProfile = VendorProfile::query()->with('user')->findOrFail($vendorProfileId);
        $wedding = $user->resolveOrCreateWedding();

        $alreadyLinked = $wedding->vendors()
            ->where('vendor_profile_id', $vendorProfile->id)
            ->exists();

        if ($alreadyLinked) {
            $this->notify("{$vendorProfile->business_name} já está vinculado ao seu casamento.");

            return;
        }

        $resolvedCategory = $this->categoryFilter !== '' && $vendorProfile->hasCategory($this->categoryFilter)
            ? $this->categoryFilter
            : $vendorProfile->category;

        WeddingVendor::query()->create([
            'wedding_id' => $wedding->id,
            'vendor_profile_id' => $vendorProfile->id,
            'created_by_side' => WeddingVendor::SIDE_COUPLE,
            'vendor_name' => $vendorProfile->business_name,
            'couple_names' => $wedding->coupleTitle(),
            'event_date' => $wedding->wedding_date,
            'event_city' => trim("{$wedding->city} - {$wedding->state}"),
            'event_venue' => $wedding->venue_name,
            'category' => $resolvedCategory,
            'contact_name' => $vendorProfile->user?->name ?? $vendorProfile->business_name,
            'contact_phone' => filled($vendorProfile->whatsapp) ? $vendorProfile->whatsapp : null,
            'contact_email' => $vendorProfile->user?->email,
            'estimated_amount' => $vendorProfile->price_from ?? 0,
            'contracted_amount' => 0,
            'paid_amount' => 0,
            'status' => WeddingVendor::STATUS_QUOTING,
            'notes' => "Importado da Vitrine Regional ({$vendorProfile->city} - {$vendorProfile->state}).",
        ]);

        $this->notify("{$vendorProfile->business_name} vinculado ao orçamento do seu casamento!");
    }

    public function render(): View
    {
        $user = Auth::user();
        $canLinkVendors = $user !== null && ($user->isCouple() || $user->isAdmin());
        $coupleWedding = $canLinkVendors ? $user->wedding()->first() : null;

        $normalizedState = Str::upper(trim($this->stateFilter));
        $normalizedCity = trim($this->cityFilter);

        $activeBannerSubmissions = BannerSubmission::query()
            ->with(['vendorProfile', 'bannerCycle'])
            ->where('status', BannerSubmission::STATUS_ACTIVE)
            ->whereHas('bannerCycle', function ($query) use ($normalizedState, $normalizedCity): void {
                $query->current();

                if ($normalizedState !== '' || $normalizedCity !== '') {
                    $query->forRegion($normalizedState, $normalizedCity);
                }
            })
            ->orderBy('slot_number')
            ->orderBy('id')
            ->limit(BannerCycle::HARD_MAX_SLOTS)
            ->get();

        $vendorQuery = VendorProfile::query();

        if ($normalizedState !== '') {
            $vendorQuery->where('state', $normalizedState);
        }

        if ($normalizedCity !== '') {
            $vendorQuery->whereRaw('LOWER(TRIM(city)) = ?', [Str::lower($normalizedCity)]);
        }

        if ($this->categoryFilter !== '' && in_array($this->categoryFilter, WeddingVendor::CATEGORIES, true)) {
            $vendorQuery->forCategory($this->categoryFilter);
        }

        if (filled($this->search)) {
            $term = '%'.trim($this->search).'%';
            $vendorQuery->where(function ($builder) use ($term): void {
                $builder->whereLike('business_name', $term, caseSensitive: false)
                    ->orWhereLike('description', $term, caseSensitive: false)
                    ->orWhereLike('city', $term, caseSensitive: false)
                    ->orWhereLike('instagram', $term, caseSensitive: false);
            });
        }

        $vendors = $vendorQuery->orderedForShowcase()->get();

        $linkedVendorProfileIds = $coupleWedding !== null
            ? WeddingVendor::query()
                ->where('wedding_id', $coupleWedding->id)
                ->whereNotNull('vendor_profile_id')
                ->pluck('vendor_profile_id')
                ->map(fn (mixed $id): int => (int) $id)
                ->all()
            : [];

        $selectedVendor = null;

        if ($this->selectedVendorId !== null) {
            $selectedVendor = $vendors->firstWhere('id', $this->selectedVendorId)
                ?? $activeBannerSubmissions->pluck('vendorProfile')->filter()->firstWhere('id', $this->selectedVendorId)
                ?? VendorProfile::query()->find($this->selectedVendorId);
        }

        $preferredRegionState = filled($coupleWedding?->state)
            ? Str::upper(trim((string) $coupleWedding->state))
            : (filled($user?->state) ? Str::upper(trim((string) $user->state)) : null);
        $preferredRegionCity = filled($coupleWedding?->city)
            ? trim((string) $coupleWedding->city)
            : (filled($user?->city) ? trim((string) $user->city) : null);

        return view('livewire.vendor.vendor-showcase', [
            'user' => $user,
            'canLinkVendors' => $canLinkVendors,
            'coupleWedding' => $coupleWedding,
            'preferredRegionState' => $preferredRegionState,
            'preferredRegionCity' => $preferredRegionCity,
            'activeBannerSubmissions' => $activeBannerSubmissions,
            'vendors' => $vendors,
            'linkedVendorProfileIds' => $linkedVendorProfileIds,
            'selectedVendor' => $selectedVendor,
            'categoryLabels' => WeddingVendor::categoryLabels(),
            'states' => BrazilLocations::states(),
            'citiesForSelectedState' => BrazilLocations::citiesForState($normalizedState),
            'citiesByState' => BrazilLocations::allCitiesByState(),
        ]);
    }

    private function notify(string $message): void
    {
        $this->statusMessage = $message;
        $this->dispatch('toast', message: $message);
    }
}
