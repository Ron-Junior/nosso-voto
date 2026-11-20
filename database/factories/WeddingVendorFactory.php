<?php

namespace Database\Factories;

use App\Models\VendorProfile;
use App\Models\Wedding;
use App\Models\WeddingVendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeddingVendor>
 */
class WeddingVendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wedding_id' => Wedding::factory(),
            'vendor_profile_id' => null,
            'created_by_side' => WeddingVendor::SIDE_COUPLE,
            'vendor_name' => fake()->company(),
            'couple_names' => null,
            'event_date' => null,
            'event_city' => null,
            'event_venue' => null,
            'category' => fake()->randomElement(WeddingVendor::CATEGORIES),
            'contact_name' => fake()->name(),
            'contact_phone' => fake()->numerify('(11) 9####-####'),
            'contact_email' => fake()->safeEmail(),
            'estimated_amount' => 5000.00,
            'contracted_amount' => 4800.00,
            'paid_amount' => 1500.00,
            'status' => WeddingVendor::STATUS_CONTRACTED,
            'notes' => null,
            'invite_token' => fn () => WeddingVendor::generateUniqueInviteToken(),
            'is_pinterest_authorized' => true,
            'has_paid_pinterest_unlock' => false,
        ];
    }

    public function autonomousForVendor(?VendorProfile $profile = null): static
    {
        return $this->state(function (array $attributes) use ($profile): array {
            return [
                'wedding_id' => null,
                'vendor_profile_id' => $profile !== null ? $profile->id : VendorProfile::factory(),
                'created_by_side' => WeddingVendor::SIDE_VENDOR,
                'vendor_name' => $profile?->business_name ?? ($attributes['vendor_name'] ?? fake()->company()),
                'category' => $profile?->category ?? ($attributes['category'] ?? 'fotografia'),
                'couple_names' => fake()->firstName().' & '.fake()->firstName(),
                'event_date' => now()->addDays(45)->toDateString(),
                'event_city' => $profile !== null ? "{$profile->city} - {$profile->state}" : 'São Paulo - SP',
                'event_venue' => 'Espaço Jardim '.fake()->lastName(),
            ];
        });
    }
}
