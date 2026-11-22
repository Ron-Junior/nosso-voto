<?php

namespace Database\Factories;

use App\Models\BannerCycle;
use App\Support\BrazilLocations;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BannerCycle>
 */
class BannerCycleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Ciclo Bimestral • São Paulo/SP',
            'state' => 'SP',
            'city' => fn (array $attributes): string => BrazilLocations::citiesForState(
                (string) ($attributes['state'] ?? 'SP')
            )[0] ?? 'São Paulo',
            'duration_months' => 2,
            'max_slots' => BannerCycle::HARD_MAX_SLOTS,
            'price' => 149.90,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonths(2),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
