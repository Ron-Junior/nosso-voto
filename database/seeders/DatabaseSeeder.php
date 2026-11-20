<?php

namespace Database\Seeders;

use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        PlatformSetting::current();

        $demoUsers = [
            [
                'email' => 'noivos@nossovoto.com.br',
                'name' => 'Ana & Lucas',
                'role' => User::ROLE_COUPLE,
            ],
            [
                'email' => 'fornecedor@nossovoto.com.br',
                'name' => 'Ateliê Terracota Eventos',
                'role' => User::ROLE_VENDOR,
            ],
            [
                'email' => 'admin@nossovoto.com.br',
                'name' => 'Admin Nosso Voto',
                'role' => User::ROLE_ADMIN,
            ],
        ];

        foreach ($demoUsers as $userData) {
            User::query()->firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => 'password',
                    'role' => $userData['role'],
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
