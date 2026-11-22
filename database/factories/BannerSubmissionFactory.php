<?php

namespace Database\Factories;

use App\Models\BannerCycle;
use App\Models\BannerSubmission;
use App\Models\VendorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BannerSubmission>
 */
class BannerSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'banner_cycle_id' => BannerCycle::factory(),
            'vendor_profile_id' => VendorProfile::factory(),
            'headline' => fake()->sentence(5),
            'banner_image_url' => null,
            'cta_link' => null,
            'status' => BannerSubmission::STATUS_PENDING,
            'slot_number' => null,
            'paid_at' => null,
        ];
    }

    public function active(int $slotNumber = 1): static
    {
        return $this->state(fn (): array => [
            'status' => BannerSubmission::STATUS_ACTIVE,
            'slot_number' => $slotNumber,
            'paid_at' => now(),
        ]);
    }

    public function waitlist(): static
    {
        return $this->state(fn (): array => [
            'status' => BannerSubmission::STATUS_WAITLIST,
            'slot_number' => null,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (): array => [
            'status' => BannerSubmission::STATUS_REJECTED,
            'slot_number' => null,
        ]);
    }
}
