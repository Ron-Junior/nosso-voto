<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VendorProfile;
use App\Models\WeddingVendor;
use App\Support\BrazilLocations;
use Carbon\CarbonInterface;
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
            'categories' => fn (array $attributes): array => [(string) ($attributes['category'] ?? 'outros')],
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

    public function regionalHighlight(?CarbonInterface $expiresAt = null): static
    {
        return $this->state(fn (): array => [
            'is_regional_highlight' => true,
            'highlight_expires_at' => $expiresAt ?? now()->addMonth(),
        ]);
    }

    /**
     * @param  list<string>  $categories
     */
    public function withCategories(array $categories): static
    {
        $sanitized = VendorProfile::sanitizeCategories($categories);
        $resolved = $sanitized !== [] ? $sanitized : ['outros'];

        return $this->state(fn (): array => [
            'category' => $resolved[0],
            'categories' => $resolved,
        ]);
    }
}
