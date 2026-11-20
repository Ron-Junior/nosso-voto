<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wedding;
use App\Support\BrazilLocations;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wedding>
 */
class WeddingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $partner1 = fake()->firstName();
        $partner2 = fake()->firstName();

        return [
            'user_id' => User::factory()->couple(),
            'slug' => fn (array $attributes) => Wedding::generateUniqueSlug(
                (string) ($attributes['partner_1_name'] ?? $partner1),
                (string) ($attributes['partner_2_name'] ?? $partner2),
            ),
            'partner_1_name' => $partner1,
            'partner_2_name' => $partner2,
            'wedding_date' => now()->addMonths(6)->toDateString(),
            'state' => 'SP',
            'city' => fn (array $attributes) => fake()->randomElement(
                BrazilLocations::citiesForState((string) ($attributes['state'] ?? 'SP'))
            ),
            'venue_name' => 'Espaço Jardim '.fake()->lastName(),
            'estimated_budget' => 50000.00,
        ];
    }
}
