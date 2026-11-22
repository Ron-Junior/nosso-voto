<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password', 'role', 'city', 'state', 'api_token'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_COUPLE = 'couple';

    public const ROLE_VENDOR = 'vendor';

    public const ROLE_ADMIN = 'admin';

    public const ROLES = [
        self::ROLE_COUPLE,
        self::ROLE_VENDOR,
        self::ROLE_ADMIN,
    ];

    /**
     * Bootstrap the model and automatically provision defaults on creation.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $user->role = $user->role ?: self::ROLE_COUPLE;

            if (filled($user->state)) {
                $user->state = Str::upper(trim($user->state));
            }

            if (filled($user->city)) {
                $user->city = trim($user->city);
            }

            if (blank($user->api_token)) {
                $user->api_token = self::generateUniqueApiToken();
            }
        });

        static::updating(function (User $user): void {
            if (filled($user->state)) {
                $user->state = Str::upper(trim($user->state));
            }

            if (filled($user->city)) {
                $user->city = trim($user->city);
            }
        });
    }

    /**
     * Generate a unique API token with at least 40 characters.
     */
    public static function generateUniqueApiToken(): string
    {
        do {
            $token = 'nv_'.Str::random(57);
        } while (static::query()->where('api_token', $token)->exists());

        return $token;
    }

    /**
     * Regenerate and persist a new unique API token for this user.
     */
    public function regenerateApiToken(): string
    {
        $token = self::generateUniqueApiToken();

        $this->forceFill(['api_token' => $token])->save();

        return $token;
    }

    public function isCouple(): bool
    {
        return $this->role === self::ROLE_COUPLE;
    }

    public function isVendor(): bool
    {
        return $this->role === self::ROLE_VENDOR;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Couples always enjoy 100% free access with no subscription required.
     */
    public function hasFreeAccess(): bool
    {
        return $this->isCouple();
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_VENDOR => 'Fornecedor',
            self::ROLE_ADMIN => 'Administrador',
            default => 'Noivos / Casal',
        };
    }

    public function homeRoute(): string
    {
        return match ($this->role) {
            self::ROLE_VENDOR => '/fornecedor/painel',
            self::ROLE_ADMIN => '/admin/configuracoes',
            default => '/painel',
        };
    }

    public function wedding(): HasOne
    {
        return $this->hasOne(Wedding::class);
    }

    /**
     * Return the user's wedding or provision a default record when saving early inspirations.
     */
    public function resolveOrCreateWedding(): Wedding
    {
        $existing = $this->wedding()->first();

        if ($existing !== null) {
            return $existing;
        }

        $partner1 = trim((string) $this->name);
        $partner2 = 'A definir';

        if (str_contains($partner1, '&')) {
            [$first, $second] = array_map('trim', explode('&', $partner1, 2));
            $partner1 = $first !== '' ? $first : $partner1;
            $partner2 = $second !== '' ? $second : $partner2;
        }

        $wedding = Wedding::query()->create([
            'user_id' => $this->id,
            'partner_1_name' => $partner1 !== '' ? $partner1 : 'Noivo(a) 1',
            'partner_2_name' => $partner2,
            'city' => filled($this->city) ? trim((string) $this->city) : 'São Paulo',
            'state' => filled($this->state) ? Str::upper(trim((string) $this->state)) : 'SP',
            'estimated_budget' => 0,
        ]);

        $fresh = $wedding->fresh();
        $this->setRelation('wedding', $fresh);

        return $fresh;
    }

    public function vendorProfile(): HasOne
    {
        return $this->hasOne(VendorProfile::class);
    }

    public function vendorProfiles(): HasMany
    {
        return $this->hasMany(VendorProfile::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
