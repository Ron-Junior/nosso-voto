<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password', 'role', 'api_token'])]
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

            if (blank($user->api_token)) {
                $user->api_token = self::generateUniqueApiToken();
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
