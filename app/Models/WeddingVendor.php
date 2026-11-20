<?php

namespace App\Models;

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

    public function isCerimonialista(): bool
    {
        return $this->category === 'cerimonialista';
    }

    /**
     * Extract only numeric digits from the vendor contact phone.
     */
    public function phoneDigits(): string
    {
        if (blank($this->contact_phone)) {
            return '';
        }

        return preg_replace('/\D+/', '', (string) $this->contact_phone) ?? '';
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
        $digits = $this->phoneDigits();

        if (strlen($digits) < 10) {
            return null;
        }

        if (in_array(strlen($digits), [10, 11], true)) {
            $digits = '55'.$digits;
        }

        return "https://wa.me/{$digits}";
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
