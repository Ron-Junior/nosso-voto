<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\VendorProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'business_name',
    'slug',
    'category',
    'categories',
    'city',
    'state',
    'whatsapp',
    'instagram',
    'description',
    'cover_image_url',
    'price_from',
    'is_regional_highlight',
    'highlight_expires_at',
])]
class VendorProfile extends Model
{
    /** @use HasFactory<VendorProfileFactory> */
    use HasFactory;

    /**
     * Bootstrap the model and automatically provision slug and normalized defaults.
     */
    protected static function booted(): void
    {
        static::creating(function (VendorProfile $profile): void {
            if (filled($profile->state)) {
                $profile->state = Str::upper(trim($profile->state));
            }

            if (filled($profile->city)) {
                $profile->city = trim($profile->city);
            }

            $explicitPrimary = filled($profile->category) && in_array($profile->category, WeddingVendor::CATEGORIES, true)
                ? $profile->category
                : null;
            $normalizedCategories = self::sanitizeCategories($profile->categories);

            if ($normalizedCategories === [] && $explicitPrimary !== null) {
                $normalizedCategories = [$explicitPrimary];
            } elseif ($normalizedCategories === []) {
                $normalizedCategories = ['outros'];
                $explicitPrimary = 'outros';
            } elseif ($explicitPrimary === null || ! in_array($explicitPrimary, $normalizedCategories, true)) {
                $explicitPrimary = $explicitPrimary !== null
                    ? $explicitPrimary
                    : $normalizedCategories[0];
                $normalizedCategories = array_values(array_unique([$explicitPrimary, ...$normalizedCategories]));
            }

            $profile->category = $explicitPrimary;
            $profile->categories = $normalizedCategories;
            $profile->is_regional_highlight ??= false;

            if (blank($profile->slug)) {
                $profile->slug = self::generateUniqueSlug((string) $profile->business_name);
            }
        });

        static::updating(function (VendorProfile $profile): void {
            if (filled($profile->state)) {
                $profile->state = Str::upper(trim($profile->state));
            }

            if (filled($profile->city)) {
                $profile->city = trim($profile->city);
            }

            if ($profile->isDirty('categories')) {
                $normalizedCategories = self::sanitizeCategories($profile->categories);

                if ($normalizedCategories === []) {
                    $fallback = filled($profile->category) && in_array($profile->category, WeddingVendor::CATEGORIES, true)
                        ? $profile->category
                        : 'outros';
                    $normalizedCategories = [$fallback];
                }

                if (! in_array($profile->category, $normalizedCategories, true)) {
                    $profile->category = $normalizedCategories[0];
                }

                $profile->categories = $normalizedCategories;
            } elseif ($profile->isDirty('category')) {
                $primary = filled($profile->category) && in_array($profile->category, WeddingVendor::CATEGORIES, true)
                    ? $profile->category
                    : 'outros';
                $originalPrimary = $profile->getOriginal('category');
                $current = self::sanitizeCategories($profile->categories);

                if ($current === [] || $current === [$originalPrimary]) {
                    $current = [$primary];
                } elseif (! in_array($primary, $current, true)) {
                    $current = array_values(array_unique([$primary, ...$current]));
                }

                $profile->category = $primary;
                $profile->categories = $current;
            }
        });
    }

    /**
     * Filter and deduplicate a list of category slugs against valid platform categories.
     *
     * @return list<string>
     */
    public static function sanitizeCategories(mixed $categories): array
    {
        if (! is_array($categories)) {
            return [];
        }

        $clean = [];

        foreach ($categories as $item) {
            if (! is_string($item)) {
                continue;
            }

            $normalized = trim($item);

            if (in_array($normalized, WeddingVendor::CATEGORIES, true) && ! in_array($normalized, $clean, true)) {
                $clean[] = $normalized;
            }
        }

        return $clean;
    }

    /**
     * Generate a unique URL-friendly slug from the vendor business name.
     */
    public static function generateUniqueSlug(string $businessName): string
    {
        $base = Str::slug(trim($businessName));

        if ($base === '') {
            $base = 'fornecedor';
        }

        $slug = $base;
        $counter = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function weddingVendors(): HasMany
    {
        return $this->hasMany(WeddingVendor::class);
    }

    public function scheduleEntries(): HasMany
    {
        return $this->hasMany(WeddingVendor::class);
    }

    public function bannerSubmissions(): HasMany
    {
        return $this->hasMany(BannerSubmission::class);
    }

    public function scopeForCategory(Builder $query, string $category): Builder
    {
        return $query->where(function (Builder $builder) use ($category): void {
            $builder->where('vendor_profiles.category', $category)
                ->orWhereRaw('vendor_profiles.categories @> ?::jsonb', [json_encode([$category])]);
        });
    }

    public function scopeOrderedForShowcase(Builder $query, ?CarbonInterface $at = null): Builder
    {
        $moment = ($at ?? now())->format('Y-m-d H:i:s');

        return $query
            ->orderByRaw(
                'CASE WHEN vendor_profiles.is_regional_highlight = true AND (vendor_profiles.highlight_expires_at IS NULL OR vendor_profiles.highlight_expires_at > ?) THEN 1 ELSE 0 END DESC',
                [$moment],
            )
            ->inRandomOrder();
    }

    /**
     * Return all categories assigned to this vendor with the primary category first.
     *
     * @return list<string>
     */
    public function allCategories(): array
    {
        $categories = self::sanitizeCategories($this->categories);
        $primary = filled($this->category) ? $this->category : null;

        if ($primary !== null && in_array($primary, WeddingVendor::CATEGORIES, true)) {
            $categories = array_values(array_unique([$primary, ...$categories]));
        }

        return $categories !== [] ? $categories : ['outros'];
    }

    /**
     * Return human-readable labels indexed by category slug for all vendor areas.
     *
     * @return array<string, string>
     */
    public function categoryLabelsList(): array
    {
        $allLabels = WeddingVendor::categoryLabels();
        $result = [];

        foreach ($this->allCategories() as $slug) {
            $result[$slug] = $allLabels[$slug] ?? ucfirst($slug);
        }

        return $result;
    }

    public function hasCategory(string $category): bool
    {
        return in_array($category, $this->allCategories(), true);
    }

    public function categoryLabel(): string
    {
        return WeddingVendor::categoryLabels()[$this->category] ?? ucfirst($this->category);
    }

    public function whatsappDigits(): string
    {
        return WeddingVendor::extractPhoneDigits($this->whatsapp);
    }

    public function whatsappUrl(): ?string
    {
        return WeddingVendor::buildWhatsappUrl($this->whatsapp);
    }

    public function hasActiveRegionalHighlight(): bool
    {
        return $this->is_regional_highlight
            && ($this->highlight_expires_at === null || $this->highlight_expires_at->isFuture());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'categories' => 'array',
            'price_from' => 'decimal:2',
            'is_regional_highlight' => 'boolean',
            'highlight_expires_at' => 'datetime',
        ];
    }
}
