<?php

namespace App\Services;

use App\Exceptions\BannerCycleFullException;
use App\Models\BannerCycle;
use App\Models\BannerSubmission;
use App\Models\VendorProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BannerCycleService
{
    /**
     * Submit or update a vendor application for an active regional banner cycle.
     *
     * @param  array{headline: string, banner_image_url?: ?string, cta_link?: ?string}  $data
     */
    public function submitVendor(BannerCycle $cycle, VendorProfile $vendor, array $data): BannerSubmission
    {
        if (! $cycle->is_active || ($cycle->ends_at !== null && $cycle->ends_at->isPast())) {
            throw new InvalidArgumentException('Este ciclo de banner não está mais aberto para candidaturas.');
        }

        if (
            Str::upper(trim($vendor->state)) !== Str::upper(trim($cycle->state))
            || Str::lower(trim($vendor->city)) !== Str::lower(trim($cycle->city))
        ) {
            throw new InvalidArgumentException('Apenas fornecedores desta cidade/UF podem se candidatar a este ciclo.');
        }

        return DB::transaction(function () use ($cycle, $vendor, $data): BannerSubmission {
            $lockedCycle = BannerCycle::query()
                ->whereKey($cycle->id)
                ->lockForUpdate()
                ->firstOrFail();

            $existing = BannerSubmission::query()
                ->where('banner_cycle_id', $lockedCycle->id)
                ->where('vendor_profile_id', $vendor->id)
                ->lockForUpdate()
                ->first();

            $headline = trim($data['headline']);
            $bannerImageUrl = filled($data['banner_image_url'] ?? null) ? trim((string) $data['banner_image_url']) : null;
            $ctaLink = filled($data['cta_link'] ?? null) ? trim((string) $data['cta_link']) : null;

            $activeCount = BannerSubmission::query()
                ->where('banner_cycle_id', $lockedCycle->id)
                ->where('status', BannerSubmission::STATUS_ACTIVE)
                ->count();

            $hasSlot = $activeCount < $lockedCycle->effectiveMaxSlots();

            if ($existing !== null) {
                $nextStatus = $existing->status === BannerSubmission::STATUS_REJECTED
                    ? ($hasSlot ? BannerSubmission::STATUS_PENDING : BannerSubmission::STATUS_WAITLIST)
                    : $existing->status;

                $existing->update([
                    'headline' => $headline,
                    'banner_image_url' => $bannerImageUrl,
                    'cta_link' => $ctaLink,
                    'status' => $nextStatus,
                ]);

                return $existing->fresh();
            }

            return BannerSubmission::query()->create([
                'banner_cycle_id' => $lockedCycle->id,
                'vendor_profile_id' => $vendor->id,
                'headline' => $headline,
                'banner_image_url' => $bannerImageUrl,
                'cta_link' => $ctaLink,
                'status' => $hasSlot ? BannerSubmission::STATUS_PENDING : BannerSubmission::STATUS_WAITLIST,
            ]);
        });
    }

    /**
     * Activate a submission in the banner cycle inside a pessimistic row lock.
     *
     * @throws BannerCycleFullException
     */
    public function activateSubmission(BannerSubmission $submission, bool $fallbackToWaitlist = false): BannerSubmission
    {
        return DB::transaction(function () use ($submission, $fallbackToWaitlist): BannerSubmission {
            $lockedCycle = BannerCycle::query()
                ->whereKey($submission->banner_cycle_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedSubmission = BannerSubmission::query()
                ->whereKey($submission->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSubmission->status === BannerSubmission::STATUS_ACTIVE) {
                return $lockedSubmission;
            }

            $maxSlots = $lockedCycle->effectiveMaxSlots();

            $activeSubmissions = BannerSubmission::query()
                ->where('banner_cycle_id', $lockedCycle->id)
                ->where('status', BannerSubmission::STATUS_ACTIVE)
                ->whereKeyNot($lockedSubmission->id)
                ->lockForUpdate()
                ->get(['id', 'slot_number']);

            if ($activeSubmissions->count() >= $maxSlots) {
                if ($fallbackToWaitlist) {
                    $lockedSubmission->update([
                        'status' => BannerSubmission::STATUS_WAITLIST,
                        'slot_number' => null,
                    ]);

                    return $lockedSubmission->fresh();
                }

                throw new BannerCycleFullException(
                    "Este ciclo já atingiu o limite máximo de {$maxSlots} vagas ativas no banner."
                );
            }

            $occupiedNumbers = $activeSubmissions
                ->pluck('slot_number')
                ->filter(fn (mixed $slot): bool => $slot !== null)
                ->map(fn (mixed $slot): int => (int) $slot)
                ->all();

            $assignedSlot = null;

            for ($slot = 1; $slot <= $maxSlots; $slot++) {
                if (! in_array($slot, $occupiedNumbers, true)) {
                    $assignedSlot = $slot;
                    break;
                }
            }

            $lockedSubmission->update([
                'status' => BannerSubmission::STATUS_ACTIVE,
                'slot_number' => $assignedSlot,
                'paid_at' => $lockedSubmission->paid_at ?? now(),
            ]);

            return $lockedSubmission->fresh();
        });
    }

    public function moveToWaitlist(BannerSubmission $submission): BannerSubmission
    {
        return DB::transaction(function () use ($submission): BannerSubmission {
            BannerCycle::query()
                ->whereKey($submission->banner_cycle_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedSubmission = BannerSubmission::query()
                ->whereKey($submission->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedSubmission->update([
                'status' => BannerSubmission::STATUS_WAITLIST,
                'slot_number' => null,
            ]);

            return $lockedSubmission->fresh();
        });
    }

    public function rejectSubmission(BannerSubmission $submission): BannerSubmission
    {
        return DB::transaction(function () use ($submission): BannerSubmission {
            BannerCycle::query()
                ->whereKey($submission->banner_cycle_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedSubmission = BannerSubmission::query()
                ->whereKey($submission->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedSubmission->update([
                'status' => BannerSubmission::STATUS_REJECTED,
                'slot_number' => null,
            ]);

            return $lockedSubmission->fresh();
        });
    }
}
