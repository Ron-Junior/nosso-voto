<?php

namespace App\Models;

use Database\Factories\VendorProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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

            $profile->category = $profile->category ?: 'outros';
            $profile->is_regional_highlight ??= false;

            if (blank($profile->slug)) {
                $profile->slug = self::generateUniqueSlug((string) $profile->business_name);
            }
        });

        static::updating(function (VendorProfile $profile): void {
            if (filled($profile->state)) {
                $profile->state = Str::upper(trim($profile->state));
            }
        });
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
            'price_from' => 'decimal:2',
            'is_regional_highlight' => 'boolean',
            'highlight_expires_at' => 'datetime',
        ];
    }
}
