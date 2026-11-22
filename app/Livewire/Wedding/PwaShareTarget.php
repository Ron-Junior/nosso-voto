<?php

namespace App\Livewire\Wedding;

use App\Models\PinterestInspiration;
use App\Models\User;
use App\Services\PinterestMetadataResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Salvar Pin Compartilhado — Nosso Voto')]
class PwaShareTarget extends Component
{
    public string $title = '';

    public string $text = '';

    public string $url = '';

    public string $pinterest_url = '';

    public string $category = PinterestInspiration::CATEGORY_DECORACAO;

    public string $type = PinterestInspiration::TYPE_PIN;

    public ?string $image_url = null;

    public ?string $bride_notes = null;

    public string $statusMessage = '';

    public ?int $savedInspirationId = null;

    public string $resolvedMetadataForUrl = '';

    public function mount(?string $title = null, ?string $text = null, ?string $url = null, ?string $image_url = null): void
    {
        $this->authorizeCoupleAccess();

        $this->title = trim((string) ($title ?? request()->query('title', '')));
        $this->text = trim((string) ($text ?? request()->query('text', '')));
        $this->url = trim((string) ($url ?? request()->query('url', '')));

        $incomingImageUrl = trim((string) ($image_url ?? request()->query('image_url', '')));
        if ($incomingImageUrl !== '' && filter_var($incomingImageUrl, FILTER_VALIDATE_URL) !== false) {
            $this->image_url = $incomingImageUrl;
        }

        $this->syncUrlFromIncomingShare();
    }

    public function updatedUrl(): void
    {
        $this->statusMessage = '';
        $this->syncUrlFromIncomingShare();
    }

    public function updatedText(): void
    {
        $this->statusMessage = '';
        $this->syncUrlFromIncomingShare();
    }

    public function updatedPinterestUrl(string $value): void
    {
        $this->statusMessage = '';
        $trimmed = trim($value);

        if ($trimmed !== '') {
            $this->type = PinterestInspiration::inferTypeFromUrl($trimmed);

            if ($trimmed !== $this->resolvedMetadataForUrl) {
                $this->image_url = null;
                $this->hydrateMetadataFromPinterestUrl();
            }
        }
    }

    public function saveToCategory(string $category): void
    {
        $this->quickSave($category);
    }

    public function quickSave(string $category): void
    {
        $this->authorizeCoupleAccess();

        abort_unless(in_array($category, PinterestInspiration::CATEGORIES, true), 422);

        $this->category = $category;
        $this->saveInspiration();
    }

    public function save(): void
    {
        $this->saveInspiration();
    }

    public function saveInspiration(): void
    {
        $user = $this->authorizeCoupleAccess();

        if (blank($this->pinterest_url)) {
            $this->syncUrlFromIncomingShare();
        } elseif (blank($this->image_url) && trim($this->pinterest_url) !== $this->resolvedMetadataForUrl) {
            $this->hydrateMetadataFromPinterestUrl();
        }

        $validated = $this->validate([
            'category' => ['required', Rule::in(PinterestInspiration::CATEGORIES)],
            'type' => ['required', Rule::in(PinterestInspiration::TYPES)],
            'pinterest_url' => ['required', 'url', 'max:2048'],
            'title' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'bride_notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'category.required' => 'Escolha uma categoria para salvar a inspiração.',
            'category.in' => 'Categoria inválida. Escolha Decoração, Maquiagem ou Penteado.',
            'type.in' => 'Selecione se a referência é Pin ou Pasta.',
            'pinterest_url.required' => 'Nenhuma URL válida foi identificada no compartilhamento. Cole o link do Pinterest abaixo.',
            'pinterest_url.url' => 'Informe uma URL válida do Pinterest.',
            'image_url.url' => 'Informe uma URL válida de imagem.',
        ]);

        $wedding = $user->resolveOrCreateWedding();

        $inspiration = $wedding->pinterestInspirations()->create([
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
            'source' => PinterestInspiration::SOURCE_PWA_SHARE,
        ]);

        $this->savedInspirationId = $inspiration->id;
        $this->notify("Inspiração salva em {$inspiration->categoryLabel()} com sucesso!");
    }

    public function render(): View
    {
        $user = $this->authorizeCoupleAccess();

        return view('livewire.wedding.pwa-share-target', [
            'user' => $user,
            'categoryLabels' => PinterestInspiration::categoryLabels(),
            'typeLabels' => PinterestInspiration::typeLabels(),
            'savedInspiration' => $this->savedInspirationId !== null
                ? PinterestInspiration::query()->find($this->savedInspirationId)
                : null,
        ]);
    }

    private function authorizeCoupleAccess(): User
    {
        $user = Auth::user();

        abort_unless($user !== null && ($user->isCouple() || $user->isAdmin()), 403);

        return $user;
    }

    private function syncUrlFromIncomingShare(): void
    {
        $extracted = PinterestInspiration::extractFirstUrl($this->url, $this->text, $this->title);

        if ($extracted !== null) {
            $this->pinterest_url = $extracted;
            $this->type = PinterestInspiration::inferTypeFromUrl($extracted);
        }

        if (blank($this->image_url)) {
            $embeddedImage = PinterestInspiration::extractImageUrl($this->url, $this->text, $this->title);
            if ($embeddedImage !== null) {
                $this->image_url = $embeddedImage;
            }
        }

        if (blank($this->image_url) && filled($this->pinterest_url)) {
            $this->hydrateMetadataFromPinterestUrl();
        }
    }

    private function hydrateMetadataFromPinterestUrl(): void
    {
        $targetUrl = trim($this->pinterest_url);

        if ($targetUrl === '' || $this->resolvedMetadataForUrl === $targetUrl) {
            return;
        }

        $metadata = app(PinterestMetadataResolver::class)->resolve($targetUrl);
        $this->resolvedMetadataForUrl = $targetUrl;

        if (blank($this->image_url) && filled($metadata['image_url'])) {
            $this->image_url = $metadata['image_url'];
        }

        if ((blank($this->title) || trim($this->title) === $targetUrl) && filled($metadata['title'])) {
            $this->title = $metadata['title'];
        }

        if (filled($metadata['canonical_url'])) {
            $this->type = PinterestInspiration::inferTypeFromUrl($metadata['canonical_url']);
        }
    }

    private function notify(string $message): void
    {
        $this->statusMessage = $message;
        $this->dispatch('toast', message: $message);
    }
}
