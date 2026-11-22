<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\BannerCycleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'title',
    'city',
    'state',
    'duration_months',
    'max_slots',
    'price',
    'starts_at',
    'ends_at',
    'is_active',
])]
class BannerCycle extends Model
{
    /** @use HasFactory<BannerCycleFactory> */
    use HasFactory;

    public const HARD_MAX_SLOTS = 5;

    /**
     * Normalize regional attributes and enforce the hard slot cap of 5.
     */
    protected static function booted(): void
    {
        static::saving(function (BannerCycle $cycle): void {
            if (filled($cycle->state)) {
                $cycle->state = Str::upper(trim($cycle->state));
            }

            if (filled($cycle->city)) {
                $cycle->city = trim($cycle->city);
            }

            $cycle->duration_months = max(1, (int) ($cycle->duration_months ?? 2));
            $cycle->max_slots = min(max(1, (int) ($cycle->max_slots ?? self::HARD_MAX_SLOTS)), self::HARD_MAX_SLOTS);
            $cycle->is_active ??= true;
        });
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(BannerSubmission::class);
    }

    public function activeSubmissions(): HasMany
    {
        return $this->hasMany(BannerSubmission::class)
            ->where('status', BannerSubmission::STATUS_ACTIVE)
            ->orderBy('slot_number')
            ->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent(Builder $query, ?CarbonInterface $at = null): Builder
    {
        $moment = ($at ?? now())->format('Y-m-d H:i:s');

        return $query
            ->where('is_active', true)
            ->where('starts_at', '<=', $moment)
            ->where('ends_at', '>=', $moment);
    }

    public function scopeOpenForSubmissions(Builder $query, ?CarbonInterface $at = null): Builder
    {
        $moment = ($at ?? now())->format('Y-m-d H:i:s');

        return $query
            ->where('is_active', true)
            ->where('ends_at', '>=', $moment);
    }

    public function scopeForRegion(Builder $query, ?string $state, ?string $city = null): Builder
    {
        $normalizedState = Str::upper(trim((string) $state));

        if ($normalizedState !== '') {
            $query->where('state', $normalizedState);
        }

        $normalizedCity = trim((string) $city);

        if ($normalizedCity !== '') {
            $query->whereRaw('LOWER(TRIM(city)) = ?', [Str::lower($normalizedCity)]);
        }

        return $query;
    }

    public function effectiveMaxSlots(): int
    {
        return min(max(1, (int) $this->max_slots), self::HARD_MAX_SLOTS);
    }

    public function activeSlotsCount(): int
    {
        if (array_key_exists('active_slots_count', $this->attributes)) {
            return (int) $this->attributes['active_slots_count'];
        }

        if ($this->relationLoaded('submissions')) {
            return $this->submissions
                ->where('status', BannerSubmission::STATUS_ACTIVE)
                ->count();
        }

        return $this->activeSubmissions()->count();
    }

    public function remainingSlotsCount(): int
    {
        return max(0, $this->effectiveMaxSlots() - $this->activeSlotsCount());
    }

    public function hasAvailableSlot(): bool
    {
        return $this->remainingSlotsCount() > 0;
    }

    public function nextAvailableSlotNumber(): ?int
    {
        $maxSlots = $this->effectiveMaxSlots();
        $occupied = $this->activeSubmissions()
            ->whereNotNull('slot_number')
            ->pluck('slot_number')
            ->map(fn (mixed $slot): int => (int) $slot)
            ->all();

        for ($slot = 1; $slot <= $maxSlots; $slot++) {
            if (! in_array($slot, $occupied, true)) {
                return $slot;
            }
        }

        return null;
    }

    public function periodLabel(): string
    {
        return sprintf(
            '%s até %s',
            $this->starts_at?->format('d/m/Y') ?? '—',
            $this->ends_at?->format('d/m/Y') ?? '—',
        );
    }

    public function isCurrentlyRunning(?CarbonInterface $at = null): bool
    {
        $moment = $at ?? now();

        return $this->is_active
            && $this->starts_at !== null
            && $this->ends_at !== null
            && $this->starts_at->lessThanOrEqualTo($moment)
            && $this->ends_at->greaterThanOrEqualTo($moment);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_months' => 'integer',
            'max_slots' => 'integer',
            'price' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
