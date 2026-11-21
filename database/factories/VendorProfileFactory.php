<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VendorProfile;
use App\Models\WeddingVendor;
use App\Support\BrazilLocations;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VendorProfile>
 */
class VendorProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $businessName = fake()->company();

        return [
            'user_id' => User::factory()->vendor(),
            'business_name' => $businessName,
            'slug' => fn (array $attributes) => VendorProfile::generateUniqueSlug(
                (string) ($attributes['business_name'] ?? $businessName)
            ),
            'category' => fake()->randomElement(WeddingVendor::CATEGORIES),
            'state' => 'SP',
            'city' => fn (array $attributes) => fake()->randomElement(
                BrazilLocations::citiesForState((string) ($attributes['state'] ?? 'SP'))
            ),
            'whatsapp' => fake()->numerify('(11) 9####-####'),
            'instagram' => '@'.fake()->userName(),
            'description' => fake()->sentence(12),
            'cover_image_url' => null,
            'price_from' => 3500.00,
            'is_regional_highlight' => false,
            'highlight_expires_at' => null,
        ];
    }
}
