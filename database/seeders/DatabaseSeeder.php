<?php

namespace Database\Seeders;

use App\Models\PlatformSetting;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingVendor;
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

        $coupleUser = User::query()->where('email', 'noivos@nossovoto.com.br')->first();

        if ($coupleUser !== null) {
            $wedding = Wedding::query()->firstOrCreate(
                ['user_id' => $coupleUser->id],
                [
                    'slug' => 'ana-e-lucas',
                    'partner_1_name' => 'Ana',
                    'partner_2_name' => 'Lucas',
                    'wedding_date' => now()->addMonths(6)->toDateString(),
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'venue_name' => 'Espaço Terracota Jardim',
                    'estimated_budget' => 65000.00,
                ],
            );

            $demoVendors = [
                [
                    'vendor_name' => 'Espaço Terracota Jardim',
                    'category' => 'espaco',
                    'contact_name' => 'Beatriz Almeida',
                    'contact_phone' => '(11) 99123-4567',
                    'contact_email' => 'contato@espacoterracota.com.br',
                    'estimated_amount' => 18000.00,
                    'contracted_amount' => 18000.00,
                    'paid_amount' => 9000.00,
                    'status' => WeddingVendor::STATUS_CONTRACTED,
                    'notes' => 'Reserva do salão e área externa para cerimônia ao pôr do sol.',
                ],
                [
                    'vendor_name' => 'Atelier Flor & Folha',
                    'category' => 'decoracao',
                    'contact_name' => 'Camila Nogueira',
                    'contact_phone' => '(11) 98234-5678',
                    'contact_email' => 'atendimento@florefolha.com.br',
                    'estimated_amount' => 10000.00,
                    'contracted_amount' => 11500.00,
                    'paid_amount' => 4000.00,
                    'status' => WeddingVendor::STATUS_CONTRACTED,
                    'notes' => 'Inclui buquê, arco floral e arranjos das mesas em tons terracota.',
                ],
                [
                    'vendor_name' => 'Mariana Cerimonial',
                    'category' => 'cerimonialista',
                    'contact_name' => 'Mariana Costa',
                    'contact_phone' => '(11) 97345-6789',
                    'contact_email' => 'mariana@cerimonial.com.br',
                    'estimated_amount' => 5500.00,
                    'contracted_amount' => 0.00,
                    'paid_amount' => 0.00,
                    'status' => WeddingVendor::STATUS_QUOTING,
                    'notes' => 'Reunião de alinhamento marcada para próxima terça-feira.',
                ],
            ];

            foreach ($demoVendors as $vendorData) {
                WeddingVendor::query()->firstOrCreate(
                    [
                        'wedding_id' => $wedding->id,
                        'vendor_name' => $vendorData['vendor_name'],
                    ],
                    [
                        ...$vendorData,
                        'created_by_side' => WeddingVendor::SIDE_COUPLE,
                        'vendor_profile_id' => null,
                        'invite_token' => WeddingVendor::generateUniqueInviteToken(),
                    ],
                );
            }
        }
    }
}
