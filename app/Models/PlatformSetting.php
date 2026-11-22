<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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
    private const CACHE_KEY = 'nossovoto.platform_setting.current';

    /**
     * Default singleton attributes provisioned when no row exists yet.
     *
     * @var array<string, bool|float|int>
     */
    private const DEFAULT_ATTRIBUTES = [
        'paywall_pinterest_inspirations_enabled' => false,
        'platform_gift_fee_percent' => 4.99,
        'regional_highlight_monthly_price' => 69.90,
        'portfolio_monthly_price' => 39.90,
        'banner_cycle_months' => 2,
        'banner_max_slots' => 5,
        'banner_cycle_price' => 149.90,
    ];

    /**
     * Keep shared and request-memoized cache synchronized whenever settings change.
     */
    protected static function booted(): void
    {
        static::saved(function (self $setting): void {
            Cache::memo()->forever(self::CACHE_KEY, self::serializableAttributes($setting));
        });

        static::deleted(function (): void {
            Cache::memo()->forget(self::CACHE_KEY);
        });
    }

    /**
     * Return the singleton platform settings row, creating defaults if missing.
     */
    public static function current(): self
    {
        $attributes = Cache::memo()->rememberForever(
            self::CACHE_KEY,
            static fn (): array => self::serializableAttributes(
                static::query()->firstOrCreate([], self::DEFAULT_ATTRIBUTES),
            ),
        );

        if (! is_array($attributes)) {
            Cache::memo()->forget(self::CACHE_KEY);

            return static::query()->firstOrCreate([], self::DEFAULT_ATTRIBUTES);
        }

        return (new static)->newFromBuilder($attributes);
    }

    /**
     * Convert model attributes to scalar/null values safe for serializable_classes=false.
     *
     * @return array<string, mixed>
     */
    private static function serializableAttributes(self $setting): array
    {
        $attributes = [];

        foreach ($setting->getAttributes() as $key => $value) {
            $attributes[$key] = $value instanceof DateTimeInterface
                ? $value->format($setting->getDateFormat())
                : $value;
        }

        return $attributes;
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
