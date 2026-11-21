<?php

namespace App\Models;

use Database\Factories\PinterestInspirationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'wedding_id',
    'category',
    'type',
    'title',
    'pinterest_url',
    'image_url',
    'bride_notes',
    'source',
])]
class PinterestInspiration extends Model
{
    /** @use HasFactory<PinterestInspirationFactory> */
    use HasFactory;

    public const CATEGORY_DECORACAO = 'decoracao';

    public const CATEGORY_MAQUIAGEM = 'maquiagem';

    public const CATEGORY_PENTEADO = 'penteado';

    public const CATEGORIES = [
        self::CATEGORY_DECORACAO,
        self::CATEGORY_MAQUIAGEM,
        self::CATEGORY_PENTEADO,
    ];

    public const TYPE_PIN = 'pin';

    public const TYPE_BOARD = 'board';

    public const TYPES = [
        self::TYPE_PIN,
        self::TYPE_BOARD,
    ];

    public const SOURCE_WEB = 'web';

    public const SOURCE_CHROME_EXTENSION = 'chrome_extension';

    public const SOURCE_PWA_SHARE = 'pwa_share';

    public const SOURCES = [
        self::SOURCE_WEB,
        self::SOURCE_CHROME_EXTENSION,
        self::SOURCE_PWA_SHARE,
    ];

    /**
     * Bootstrap defaults for inspiration type and capture source.
     */
    protected static function booted(): void
    {
        static::creating(function (PinterestInspiration $inspiration): void {
            $inspiration->type = $inspiration->type ?: self::TYPE_PIN;
            $inspiration->source = $inspiration->source ?: self::SOURCE_WEB;
        });
    }

    /**
     * Human-readable labels for inspiration categories.
     *
     * @return array<string, string>
     */
    public static function categoryLabels(): array
    {
        return [
            self::CATEGORY_DECORACAO => 'Decoração',
            self::CATEGORY_MAQUIAGEM => 'Maquiagem',
            self::CATEGORY_PENTEADO => 'Penteado',
        ];
    }

    /**
     * Human-readable labels for inspiration types.
     *
     * @return array<string, string>
     */
    public static function typeLabels(): array
    {
        return [
            self::TYPE_PIN => 'Pin',
            self::TYPE_BOARD => 'Pasta',
        ];
    }

    /**
     * Human-readable labels for capture sources.
     *
     * @return array<string, string>
     */
    public static function sourceLabels(): array
    {
        return [
            self::SOURCE_WEB => 'Painel Web',
            self::SOURCE_CHROME_EXTENSION => 'Extensão Chrome',
            self::SOURCE_PWA_SHARE => 'PWA Celular',
        ];
    }

    /**
     * Extract the first HTTP/HTTPS URL found across the given inputs.
     */
    public static function extractFirstUrl(?string ...$inputs): ?string
    {
        foreach ($inputs as $input) {
            if (blank($input)) {
                continue;
            }

            if (preg_match('/https?:\/\/[^\s<>"\'\]]+/i', (string) $input, $matches) === 1) {
                $url = rtrim($matches[0], ".,;:!?)]}'\"");

                if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
                    return $url;
                }
            }
        }

        return null;
    }

    /**
     * Infer whether a Pinterest URL points to a board (pasta) or a single pin.
     */
    public static function inferTypeFromUrl(?string $url): string
    {
        if (blank($url)) {
            return self::TYPE_PIN;
        }

        $parsed = parse_url((string) $url);
        $host = Str::lower((string) ($parsed['host'] ?? ''));
        $path = trim((string) ($parsed['path'] ?? ''), '/');

        if ($host === '' || str_contains($host, 'pin.it')) {
            return self::TYPE_PIN;
        }

        if (str_contains($host, 'pinterest.') && ! preg_match('#(^|/)pin(/|$)#i', $path) && str_contains($path, '/')) {
            return self::TYPE_BOARD;
        }

        return self::TYPE_PIN;
    }

    /**
     * Return a sanitized title or a sensible fallback based on category and type.
     */
    public static function fallbackTitle(?string $title, string $category, string $type = self::TYPE_PIN): string
    {
        $clean = trim((string) $title);

        if ($clean !== '') {
            return Str::limit($clean, 255, '');
        }

        $typeLabel = self::typeLabels()[$type] ?? 'Pin';
        $categoryLabel = self::categoryLabels()[$category] ?? 'Inspiração';

        return "{$typeLabel} de {$categoryLabel}";
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function categoryLabel(): string
    {
        return self::categoryLabels()[$this->category] ?? ucfirst($this->category);
    }

    public function typeLabel(): string
    {
        return self::typeLabels()[$this->type] ?? ucfirst($this->type);
    }

    public function sourceLabel(): string
    {
        return self::sourceLabels()[$this->source] ?? ucfirst($this->source);
    }

    public function isBoard(): bool
    {
        return $this->type === self::TYPE_BOARD;
    }

    public function isPin(): bool
    {
        return $this->type === self::TYPE_PIN;
    }
}

