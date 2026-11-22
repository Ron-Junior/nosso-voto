<?php

namespace Database\Factories;

use App\Models\PinterestInspiration;
use App\Models\Wedding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PinterestInspiration>
 */
class PinterestInspirationFactory extends Factory
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
            'category' => fake()->randomElement(PinterestInspiration::CATEGORIES),
            'type' => PinterestInspiration::TYPE_PIN,
            'title' => fake()->sentence(4),
            'pinterest_url' => 'https://br.pinterest.com/pin/'.fake()->numerify('##########').'/',
            'image_url' => 'https://i.pinimg.com/736x/'.fake()->bothify('##/##/##/########').'.jpg',
            'bride_notes' => fake()->sentence(8),
            'source' => PinterestInspiration::SOURCE_WEB,
        ];
    }

    public function decoracao(): static
    {
        return $this->state(fn (): array => [
            'category' => PinterestInspiration::CATEGORY_DECORACAO,
        ]);
    }

    public function maquiagem(): static
    {
        return $this->state(fn (): array => [
            'category' => PinterestInspiration::CATEGORY_MAQUIAGEM,
        ]);
    }

    public function penteado(): static
    {
        return $this->state(fn (): array => [
            'category' => PinterestInspiration::CATEGORY_PENTEADO,
        ]);
    }

    public function board(): static
    {
        return $this->state(fn (): array => [
            'type' => PinterestInspiration::TYPE_BOARD,
            'pinterest_url' => 'https://br.pinterest.com/noivaterracota/'.fake()->slug(2).'/',
        ]);
    }

    public function fromChromeExtension(): static
    {
        return $this->state(fn (): array => [
            'source' => PinterestInspiration::SOURCE_CHROME_EXTENSION,
        ]);
    }

    public function fromPwaShare(): static
    {
        return $this->state(fn (): array => [
            'source' => PinterestInspiration::SOURCE_PWA_SHARE,
        ]);
    }
}
