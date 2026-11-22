<?php

namespace App\Models;

use App\Exceptions\BannerCycleFullException;
use Database\Factories\BannerSubmissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'banner_cycle_id',
    'vendor_profile_id',
    'headline',
    'banner_image_url',
    'cta_link',
    'status',
    'slot_number',
    'paid_at',
])]
class BannerSubmission extends Model
{
    /** @use HasFactory<BannerSubmissionFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_WAITLIST = 'waitlist';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACTIVE,
        self::STATUS_WAITLIST,
        self::STATUS_REJECTED,
    ];

    /**
     * Enforce the hard slot cap on every write so no cycle ever exceeds its max_slots limit.
     */
    protected static function booted(): void
    {
        static::saving(function (BannerSubmission $submission): void {
            $submission->status = $submission->status ?: self::STATUS_PENDING;

            if ($submission->status !== self::STATUS_ACTIVE) {
                $submission->slot_number = null;

                return;
            }

            $needsSlotValidation = ! $submission->exists
                || $submission->isDirty('status')
                || $submission->isDirty('banner_cycle_id')
                || $submission->slot_number === null;

            if (! $needsSlotValidation) {
                return;
            }

            $cycle = BannerCycle::query()->findOrFail($submission->banner_cycle_id);
            $maxSlots = $cycle->effectiveMaxSlots();

            $occupiedSlots = static::query()
                ->where('banner_cycle_id', $cycle->id)
                ->where('status', self::STATUS_ACTIVE)
                ->when($submission->exists, fn ($query) => $query->whereKeyNot($submission->id))
                ->get(['slot_number']);

            if ($occupiedSlots->count() >= $maxSlots) {
                throw new BannerCycleFullException(
                    "Este ciclo já atingiu o limite máximo de {$maxSlots} vagas ativas no banner."
                );
            }

            $usedNumbers = $occupiedSlots
                ->pluck('slot_number')
                ->filter(fn (mixed $slot): bool => $slot !== null)
                ->map(fn (mixed $slot): int => (int) $slot)
                ->all();

            $requestedSlot = $submission->slot_number !== null ? (int) $submission->slot_number : null;

            if (
                $requestedSlot === null
                || $requestedSlot < 1
                || $requestedSlot > $maxSlots
                || in_array($requestedSlot, $usedNumbers, true)
            ) {
                for ($slot = 1; $slot <= $maxSlots; $slot++) {
                    if (! in_array($slot, $usedNumbers, true)) {
                        $submission->slot_number = $slot;
                        break;
                    }
                }
            }
        });
    }

    /**
     * Human-readable labels for banner submission statuses.
     *
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Em Análise',
            self::STATUS_ACTIVE => 'Ativo no Banner',
            self::STATUS_WAITLIST => 'Lista de Espera',
            self::STATUS_REJECTED => 'Recusado',
        ];
    }

    public function bannerCycle(): BelongsTo
    {
        return $this->belongsTo(BannerCycle::class);
    }

    public function vendorProfile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst($this->status);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function resolvedCtaUrl(): string
    {
        if (filled($this->cta_link)) {
            return trim((string) $this->cta_link);
        }

        if ($this->vendorProfile !== null) {
            return route('showcase.show', $this->vendorProfile->slug);
        }

        return route('showcase.index');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'slot_number' => 'integer',
            'paid_at' => 'datetime',
        ];
    }
}
