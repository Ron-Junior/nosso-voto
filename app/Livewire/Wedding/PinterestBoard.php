<?php

namespace App\Livewire\Wedding;

use App\Models\PinterestInspiration;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingVendor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Inspirações do Pinterest — Nosso Voto')]
class PinterestBoard extends Component
{
    public ?int $editingInspirationId = null;

    public string $category = PinterestInspiration::CATEGORY_DECORACAO;

    public string $type = PinterestInspiration::TYPE_PIN;

    public string $title = '';

    public string $pinterest_url = '';

    public ?string $image_url = null;

    public ?string $bride_notes = null;

    public string $activeCategory = 'all';

    public string $categoryFilter = 'all';

    public string $typeFilter = 'all';

    public string $search = '';

    public string $statusMessage = '';

    private ?Wedding $cachedWedding = null;

    private bool $weddingLoaded = false;

    public function mount(): void
    {
        $this->authorizeCoupleAccess();
    }

    public function selectCategory(string $category): void
    {
        $this->authorizeCoupleAccess();
        $this->statusMessage = '';

        $normalized = $this->normalizeCategoryFilter($category);
        $this->activeCategory = $normalized;
        $this->categoryFilter = $normalized;

        if ($this->editingInspirationId === null && in_array($normalized, PinterestInspiration::CATEGORIES, true)) {
            $this->category = $normalized;
        }
    }

    public function updatedActiveCategory(string $value): void
    {
        $normalized = $this->normalizeCategoryFilter($value);
        $this->activeCategory = $normalized;
        $this->categoryFilter = $normalized;
        $this->statusMessage = '';
    }

    public function updatedCategoryFilter(string $value): void
    {
        $normalized = $this->normalizeCategoryFilter($value);
        $this->categoryFilter = $normalized;
        $this->activeCategory = $normalized;
        $this->statusMessage = '';
    }

    public function updatedTypeFilter(): void
    {
        $this->statusMessage = '';
    }

    public function updatedSearch(): void
    {
        $this->statusMessage = '';
    }

    public function save(): void
    {
        $this->saveInspiration();
    }

    public function saveInspiration(): void
    {
        $user = $this->authorizeCoupleAccess();

        $validated = $this->validate([
            'category' => ['required', Rule::in(PinterestInspiration::CATEGORIES)],
            'type' => ['required', Rule::in(PinterestInspiration::TYPES)],
            'pinterest_url' => ['required', 'url', 'max:2048'],
            'title' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'bride_notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'category.required' => 'Selecione a especialidade da inspiração.',
            'category.in' => 'Selecione uma categoria válida (Decoração, Maquiagem ou Penteado).',
            'type.in' => 'Selecione se a referência é um Pin ou uma Pasta.',
            'pinterest_url.required' => 'Cole o link do Pin ou da Pasta do Pinterest.',
            'pinterest_url.url' => 'Informe uma URL válida do Pinterest (ex.: https://br.pinterest.com/pin/...).',
            'image_url.url' => 'Informe uma URL de imagem válida ou deixe em branco.',
        ]);

        $wedding = $user->resolveOrCreateWedding();
        $this->cachedWedding = $wedding;
        $this->weddingLoaded = true;

        $payload = [
            'category' => $validated['category'],
            'type' => $validated['type'],
            'title' => PinterestInspiration::fallbackTitle(
                $validated['title'] ?? null,
                $validated['category'],
                $validated['type'],
            ),
            'pinterest_url' => trim($validated['pinterest_url']),
            'image_url' => filled($validated['image_url'] ?? null) ? trim((string) $validated['image_url']) : null,
            'bride_notes' => filled($validated['bride_notes'] ?? null) ? trim((string) $validated['bride_notes']) : null,
        ];

        if ($this->editingInspirationId !== null) {
            $inspiration = $wedding->pinterestInspirations()->findOrFail($this->editingInspirationId);
            $inspiration->update($payload);
            $message = 'Inspiração atualizada com sucesso.';
        } else {
            $wedding->pinterestInspirations()->create([
                ...$payload,
                'source' => PinterestInspiration::SOURCE_WEB,
            ]);
            $message = 'Inspiração salva no moodboard com sucesso.';
        }

        $this->resetForm();
        $this->notify($message);
    }

    public function edit(int $inspirationId): void
    {
        $this->editInspiration($inspirationId);
    }

    public function editInspiration(int $inspirationId): void
    {
        $user = $this->authorizeCoupleAccess();
        $this->statusMessage = '';

        $wedding = $this->resolveWedding($user);
        abort_if($wedding === null, 404);

        $inspiration = $wedding->pinterestInspirations()->findOrFail($inspirationId);

        $this->editingInspirationId = $inspiration->id;
        $this->category = $inspiration->category;
        $this->type = $inspiration->type;
        $this->title = $inspiration->title;
        $this->pinterest_url = $inspiration->pinterest_url;
        $this->image_url = $inspiration->image_url;
        $this->bride_notes = $inspiration->bride_notes;

        $this->resetValidation();
    }

    public function cancelEdit(): void
    {
        $this->authorizeCoupleAccess();
        $this->statusMessage = '';
        $this->resetForm();
    }

    public function delete(int $inspirationId): void
    {
        $this->deleteInspiration($inspirationId);
    }

    public function deleteInspiration(int $inspirationId): void
    {
        $user = $this->authorizeCoupleAccess();
        $wedding = $this->resolveWedding($user);

        abort_if($wedding === null, 404);

        $inspiration = $wedding->pinterestInspirations()->findOrFail($inspirationId);
        $inspiration->delete();

        if ($this->editingInspirationId === $inspirationId) {
            $this->resetForm();
        }

        $this->notify('Inspiração removida do moodboard.');
    }

    public function toggleVendorPinterestAuthorization(int $vendorId): void
    {
        $user = $this->authorizeCoupleAccess();
        $wedding = $this->resolveWedding($user);

        abort_if($wedding === null, 404);

        $vendor = $wedding->vendors()->findOrFail($vendorId);
        abort_unless($vendor->supportsPinterestMoodboard(), 422);

        $vendor->forceFill([
            'is_pinterest_authorized' => ! $vendor->is_pinterest_authorized,
        ])->save();

        $message = $vendor->is_pinterest_authorized
            ? "Acesso às inspirações liberado para {$vendor->vendor_name}."
            : "Acesso às inspirações pausado para {$vendor->vendor_name}.";

        $this->notify($message);
    }

    public function regenerateToken(): void
    {
        $user = $this->authorizeCoupleAccess();
        $user->regenerateApiToken();

        $this->notify('Novo token de integração gerado com sucesso.');
    }

    public function notifyTokenCopied(): void
    {
        $this->authorizeCoupleAccess();
        $this->notify('Token de API copiado para a área de transferência.');
    }

    public function render(): View
    {
        $user = $this->authorizeCoupleAccess();
        $wedding = $this->resolveWedding($user);

        $inspirations = collect();
        $specialtyVendors = collect();
        $countsByCategory = [
            PinterestInspiration::CATEGORY_DECORACAO => 0,
            PinterestInspiration::CATEGORY_MAQUIAGEM => 0,
            PinterestInspiration::CATEGORY_PENTEADO => 0,
        ];
        $totalCount = 0;

        if ($wedding !== null) {
            $groupedCounts = $wedding->pinterestInspirations()
                ->selectRaw('category, COUNT(*) as total')
                ->groupBy('category')
                ->pluck('total', 'category');

            foreach (PinterestInspiration::CATEGORIES as $cat) {
                $countsByCategory[$cat] = (int) ($groupedCounts[$cat] ?? 0);
            }

            $totalCount = array_sum($countsByCategory);

            $query = $wedding->pinterestInspirations()->latest('id');

            if ($this->activeCategory !== 'all' && in_array($this->activeCategory, PinterestInspiration::CATEGORIES, true)) {
                $query->where('category', $this->activeCategory);
            }

            if ($this->typeFilter !== 'all' && in_array($this->typeFilter, PinterestInspiration::TYPES, true)) {
                $query->where('type', $this->typeFilter);
            }

            if (filled($this->search)) {
                $term = '%'.trim($this->search).'%';
                $query->where(function ($builder) use ($term): void {
                    $builder->whereLike('title', $term, caseSensitive: false)
                        ->orWhereLike('bride_notes', $term, caseSensitive: false)
                        ->orWhereLike('pinterest_url', $term, caseSensitive: false);
                });
            }

            $inspirations = $query->get();

            $specialtyVendors = $wedding->vendors()
                ->whereIn('category', [
                    PinterestInspiration::CATEGORY_DECORACAO,
                    PinterestInspiration::CATEGORY_MAQUIAGEM,
                    PinterestInspiration::CATEGORY_PENTEADO,
                ])
                ->orderBy('category')
                ->orderBy('vendor_name')
                ->get();
        }

        return view('livewire.wedding.pinterest-board', [
            'user' => $user,
            'wedding' => $wedding,
            'inspirations' => $inspirations,
            'specialtyVendors' => $specialtyVendors,
            'countsByCategory' => $countsByCategory,
            'totalCount' => $totalCount,
            'categoryLabels' => PinterestInspiration::categoryLabels(),
            'typeLabels' => PinterestInspiration::typeLabels(),
            'sourceLabels' => PinterestInspiration::sourceLabels(),
            'settings' => PlatformSetting::current(),
            'clipEndpointUrl' => url('/api/v1/inspirations/clip'),
            'vendorCategoryLabels' => WeddingVendor::categoryLabels(),
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

    private function normalizeCategoryFilter(string $category): string
    {
        if (in_array($category, PinterestInspiration::CATEGORIES, true)) {
            return $category;
        }

        return 'all';
    }

    private function resetForm(): void
    {
        $this->editingInspirationId = null;
        $this->category = in_array($this->activeCategory, PinterestInspiration::CATEGORIES, true)
            ? $this->activeCategory
            : PinterestInspiration::CATEGORY_DECORACAO;
        $this->type = PinterestInspiration::TYPE_PIN;
        $this->title = '';
        $this->pinterest_url = '';
        $this->image_url = null;
        $this->bride_notes = null;
        $this->resetValidation();
    }

    private function notify(string $message): void
    {
        $this->statusMessage = $message;
        $this->dispatch('toast', message: $message);
    }
}

