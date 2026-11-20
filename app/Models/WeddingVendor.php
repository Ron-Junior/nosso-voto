<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\WeddingVendorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'wedding_id',
    'vendor_profile_id',
    'created_by_side',
    'vendor_name',
    'couple_names',
    'event_date',
    'event_city',
    'event_venue',
    'category',
    'contact_name',
    'contact_phone',
    'contact_email',
    'estimated_amount',
    'contracted_amount',
    'paid_amount',
    'status',
    'notes',
    'invite_token',
    'is_pinterest_authorized',
    'has_paid_pinterest_unlock',
])]
class WeddingVendor extends Model
{
    /** @use HasFactory<WeddingVendorFactory> */
    use HasFactory;

    public const SIDE_COUPLE = 'couple';

    public const SIDE_VENDOR = 'vendor';

    public const STATUS_QUOTING = 'cotando';

    public const STATUS_CONTRACTED = 'contratado';

    public const STATUS_PAID = 'quitado';

    public const STATUSES = [
        self::STATUS_QUOTING,
        self::STATUS_CONTRACTED,
        self::STATUS_PAID,
    ];

    public const CATEGORIES = [
        'cerimonialista',
        'decoracao',
        'maquiagem',
        'penteado',
        'fotografia',
        'buffet',
        'musica',
        'espaco',
        'outros',
    ];

    /**
     * Bootstrap the model and automatically provision invite token and defaults.
     */
    protected static function booted(): void
    {
        static::creating(function (WeddingVendor $vendor): void {
            $vendor->created_by_side = $vendor->created_by_side ?: self::SIDE_COUPLE;
            $vendor->status = $vendor->status ?: self::STATUS_QUOTING;
            $vendor->is_pinterest_authorized ??= true;
            $vendor->has_paid_pinterest_unlock ??= false;

            if (blank($vendor->invite_token)) {
                $vendor->invite_token = self::generateUniqueInviteToken();
            }
        });
    }

    /**
     * Human-readable labels for vendor categories.
     *
     * @return array<string, string>
     */
    public static function categoryLabels(): array
    {
        return [
            'cerimonialista' => 'Cerimonialista',
            'decoracao' => 'Decoração',
            'maquiagem' => 'Maquiagem',
            'penteado' => 'Penteado',
            'fotografia' => 'Fotografia & Vídeo',
            'buffet' => 'Buffet & Gastronomia',
            'musica' => 'Música & DJ',
            'espaco' => 'Espaço & Recepção',
            'outros' => 'Outros',
        ];
    }

    /**
     * Human-readable labels for contract statuses.
     *
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_QUOTING => 'Em Cotação',
            self::STATUS_CONTRACTED => 'Contratado',
            self::STATUS_PAID => 'Quitado',
        ];
    }

    /**
     * Generate a unique 48-character invite token for this wedding vendor record.
     */
    public static function generateUniqueInviteToken(): string
    {
        do {
            $token = Str::random(48);
        } while (static::query()->where('invite_token', $token)->exists());

        return $token;
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function vendorProfile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function isCerimonialista(): bool
    {
        return $this->category === 'cerimonialista';
    }

    public static function extractPhoneDigits(?string $phone): string
    {
        if (blank($phone)) {
            return '';
        }

        return preg_replace('/\D+/', '', (string) $phone) ?? '';
    }

    public static function buildWhatsappUrl(?string $phone): ?string
    {
        $digits = self::extractPhoneDigits($phone);

        if (strlen($digits) < 10) {
            return null;
        }

        if (in_array(strlen($digits), [10, 11], true)) {
            $digits = '55'.$digits;
        }

        return "https://wa.me/{$digits}";
    }

    /**
     * Extract only numeric digits from the vendor contact phone.
     */
    public function phoneDigits(): string
    {
        return self::extractPhoneDigits($this->contact_phone);
    }

    /**
     * Build a clickable tel: URI when the phone has at least 8 digits.
     */
    public function phoneHref(): ?string
    {
        $digits = $this->phoneDigits();

        if (strlen($digits) < 8) {
            return null;
        }

        if (in_array(strlen($digits), [10, 11], true)) {
            return "tel:+55{$digits}";
        }

        return strlen($digits) >= 12 ? "tel:+{$digits}" : "tel:{$digits}";
    }

    /**
     * Build a WhatsApp wa.me URL normalized with country code 55 for BR numbers.
     */
    public function whatsappUrl(): ?string
    {
        return self::buildWhatsappUrl($this->contact_phone);
    }

    public function resolvedCoupleNames(): string
    {
        if (filled($this->couple_names)) {
            return trim((string) $this->couple_names);
        }

        if ($this->wedding !== null) {
            return $this->wedding->coupleTitle();
        }

        return 'Casal a definir';
    }

    public function resolvedEventDate(): ?CarbonInterface
    {
        return $this->event_date ?? $this->wedding?->wedding_date;
    }

    public function resolvedEventCity(): ?string
    {
        if (filled($this->event_city)) {
            return trim((string) $this->event_city);
        }

        if ($this->wedding !== null) {
            return trim("{$this->wedding->city} - {$this->wedding->state}");
        }

        return null;
    }

    public function resolvedEventVenue(): ?string
    {
        if (filled($this->event_venue)) {
            return trim((string) $this->event_venue);
        }

        return filled($this->wedding?->venue_name)
            ? trim((string) $this->wedding->venue_name)
            : null;
    }

    public function daysUntilEvent(): ?int
    {
        $eventDate = $this->resolvedEventDate();

        if ($eventDate === null) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($eventDate->copy()->startOfDay(), false);
    }

    public function countdownLabel(): string
    {
        $days = $this->daysUntilEvent();

        if ($days === null) {
            return 'Data a definir';
        }

        if ($days > 1) {
            return "Em {$days} dias";
        }

        if ($days === 1) {
            return 'Em 1 dia';
        }

        if ($days === 0) {
            return 'Hoje';
        }

        $pastDays = abs($days);

        return $pastDays === 1 ? 'Há 1 dia' : "Há {$pastDays} dias";
    }

    public function timelineHeadline(): string
    {
        $parts = [
            $this->countdownLabel(),
            $this->resolvedCoupleNames(),
        ];

        $city = $this->resolvedEventCity();

        if (filled($city)) {
            $parts[] = $city;
        }

        return implode(' • ', $parts);
    }

    public function isPlatformCouple(): bool
    {
        return $this->wedding_id !== null;
    }

    public function originLabel(): string
    {
        return $this->isPlatformCouple() ? 'Casal na Plataforma' : 'Cadastro Manual';
    }

    /**
     * Link a vendor-created schedule entry to a couple's wedding without overwriting financial history.
     */
    public function linkToWedding(Wedding $wedding): self
    {
        $this->forceFill([
            'wedding_id' => $wedding->id,
            'couple_names' => $this->couple_names ?: $wedding->coupleTitle(),
            'event_date' => $this->event_date ?? $wedding->wedding_date,
            'event_city' => $this->event_city ?: trim("{$wedding->city} - {$wedding->state}"),
            'event_venue' => $this->event_venue ?: $wedding->venue_name,
        ])->save();

        return $this->setRelation('wedding', $wedding);
    }

    public function categoryLabel(): string
    {
        return self::categoryLabels()[$this->category] ?? ucfirst($this->category);
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst($this->status);
    }

    public function remainingAmount(): float
    {
        return round((float) $this->contracted_amount - (float) $this->paid_amount, 2);
    }

    public function isOverEstimate(): bool
    {
        return (float) $this->contracted_amount > (float) $this->estimated_amount;
    }

    public function overEstimateAmount(): float
    {
        return round(max(0, (float) $this->contracted_amount - (float) $this->estimated_amount), 2);
    }

    public function inviteUrl(): string
    {
        return url("/acesso/profissional/{$this->invite_token}");
    }

    /**
     * Regenerate and persist a new unique invite token for this vendor.
     */
    public function regenerateInviteToken(): string
    {
        $token = self::generateUniqueInviteToken();

        $this->forceFill(['invite_token' => $token])->save();

        return $token;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'estimated_amount' => 'decimal:2',
            'contracted_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'is_pinterest_authorized' => 'boolean',
            'has_paid_pinterest_unlock' => 'boolean',
        ];
    }
}
