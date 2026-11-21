<?php

namespace App\Models;

use Database\Factories\WeddingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'slug',
    'partner_1_name',
    'partner_2_name',
    'wedding_date',
    'city',
    'state',
    'venue_name',
    'estimated_budget',
])]
class Wedding extends Model
{
    /** @use HasFactory<WeddingFactory> */
    use HasFactory;

    /**
     * Bootstrap the model and automatically provision slug and normalized state.
     */
    protected static function booted(): void
    {
        static::creating(function (Wedding $wedding): void {
            if (filled($wedding->state)) {
                $wedding->state = Str::upper(trim($wedding->state));
            }

            if (blank($wedding->slug)) {
                $wedding->slug = self::generateUniqueSlug(
                    (string) $wedding->partner_1_name,
                    (string) $wedding->partner_2_name,
                );
            }
        });

        static::updating(function (Wedding $wedding): void {
            if (filled($wedding->state)) {
                $wedding->state = Str::upper(trim($wedding->state));
            }
        });
    }

    /**
     * Generate a unique URL-friendly slug from the couple's names.
     */
    public static function generateUniqueSlug(string $partner1, string $partner2): string
    {
        $base = Str::slug(trim($partner1).' e '.trim($partner2));

        if ($base === '') {
            $base = 'casamento';
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

    public function vendors(): HasMany
    {
        return $this->hasMany(WeddingVendor::class);
    }

    public function weddingVendors(): HasMany
    {
        return $this->hasMany(WeddingVendor::class);
    }

    public function pinterestInspirations(): HasMany
    {
        return $this->hasMany(PinterestInspiration::class);
    }

    public function inspirations(): HasMany
    {
        return $this->hasMany(PinterestInspiration::class);
    }

    public function coupleTitle(): string
    {
        return trim("{$this->partner_1_name} & {$this->partner_2_name}");
    }

    public function daysUntilWedding(): ?int
    {
        if ($this->wedding_date === null) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->wedding_date->copy()->startOfDay(), false);
    }

    public function totalEstimatedVendors(): float
    {
        return round((float) $this->vendors()->sum('estimated_amount'), 2);
    }

    public function totalContracted(): float
    {
        return round((float) $this->vendors()->sum('contracted_amount'), 2);
    }

    public function totalPaid(): float
    {
        return round((float) $this->vendors()->sum('paid_amount'), 2);
    }

    public function remainingToPay(): float
    {
        return round($this->totalContracted() - $this->totalPaid(), 2);
    }

    public function budgetBalance(): float
    {
        return round((float) $this->estimated_budget - $this->totalContracted(), 2);
    }

    public function isOverBudget(): bool
    {
        return $this->totalContracted() > (float) $this->estimated_budget;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'wedding_date' => 'date',
            'estimated_budget' => 'decimal:2',
        ];
    }
}
