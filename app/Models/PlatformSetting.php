<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'paywall_pinterest_inspirations_enabled',
    'platform_gift_fee_percent',
    'regional_highlight_monthly_price',
    'portfolio_monthly_price',
    'banner_cycle_months',
    'banner_max_slots',
    'banner_cycle_price',
])]
class PlatformSetting extends Model
{
    /**
     * Return the singleton platform settings row, creating defaults if missing.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'paywall_pinterest_inspirations_enabled' => false,
            'platform_gift_fee_percent' => 4.99,
            'regional_highlight_monthly_price' => 69.90,
            'portfolio_monthly_price' => 39.90,
            'banner_cycle_months' => 2,
            'banner_max_slots' => 5,
            'banner_cycle_price' => 149.90,
        ]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'paywall_pinterest_inspirations_enabled' => 'boolean',
            'platform_gift_fee_percent' => 'decimal:2',
            'regional_highlight_monthly_price' => 'decimal:2',
            'portfolio_monthly_price' => 'decimal:2',
            'banner_cycle_months' => 'integer',
            'banner_max_slots' => 'integer',
            'banner_cycle_price' => 'decimal:2',
        ];
    }
}
