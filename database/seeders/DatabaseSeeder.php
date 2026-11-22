<?php

namespace Database\Seeders;

use App\Models\BannerCycle;
use App\Models\PinterestInspiration;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Models\VendorProfile;
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

            $demoInspirations = [
                [
                    'category' => PinterestInspiration::CATEGORY_DECORACAO,
                    'type' => PinterestInspiration::TYPE_BOARD,
                    'title' => 'Cerimônia Terracota & Flores Secas',
                    'pinterest_url' => 'https://br.pinterest.com/anaelucas/cerimonia-terracota-flores-secas/',
                    'image_url' => null,
                    'bride_notes' => 'Paleta quente para o altar ao pôr do sol com folhagens secas e velas.',
                    'source' => PinterestInspiration::SOURCE_WEB,
                ],
                [
                    'category' => PinterestInspiration::CATEGORY_MAQUIAGEM,
                    'type' => PinterestInspiration::TYPE_PIN,
                    'title' => 'Make Glow Natural com Delineado Suave',
                    'pinterest_url' => 'https://br.pinterest.com/pin/987654321001/',
                    'image_url' => null,
                    'bride_notes' => 'Pele iluminada e batom nude rosado para durar até o fim da festa.',
                    'source' => PinterestInspiration::SOURCE_CHROME_EXTENSION,
                ],
                [
                    'category' => PinterestInspiration::CATEGORY_PENTEADO,
                    'type' => PinterestInspiration::TYPE_PIN,
                    'title' => 'Coque Baixo Desconstruído com Grinalda',
                    'pinterest_url' => 'https://br.pinterest.com/pin/987654321002/',
                    'image_url' => null,
                    'bride_notes' => 'Mechas soltas na frente para emoldurar o rosto e combinar com o véu.',
                    'source' => PinterestInspiration::SOURCE_PWA_SHARE,
                ],
            ];

            foreach ($demoInspirations as $inspirationData) {
                PinterestInspiration::query()->firstOrCreate(
                    [
                        'wedding_id' => $wedding->id,
                        'pinterest_url' => $inspirationData['pinterest_url'],
                    ],
                    $inspirationData,
                );
            }
        }

        $vendorUser = User::query()->where('email', 'fornecedor@nossovoto.com.br')->first();

        if ($vendorUser !== null) {
            VendorProfile::query()->firstOrCreate(
                ['user_id' => $vendorUser->id],
                [
                    'business_name' => 'Ateliê Terracota Eventos',
                    'slug' => 'atelie-terracota-eventos',
                    'category' => 'decoracao',
                    'categories' => ['decoracao', 'espaco'],
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'whatsapp' => '(11) 98888-7777',
                    'instagram' => '@atelieterra',
                    'description' => 'Decoração autoral e espaço integrado em tons terracota e areia para cerimônias memoráveis.',
                    'price_from' => 9500.00,
                    'is_regional_highlight' => true,
                    'highlight_expires_at' => now()->addMonth(),
                ],
            );
        }

        $settings = PlatformSetting::current();

        BannerCycle::query()->firstOrCreate(
            [
                'state' => 'SP',
                'city' => 'São Paulo',
                'title' => 'Ciclo Bimestral • São Paulo/SP',
            ],
            [
                'duration_months' => $settings->banner_cycle_months,
                'max_slots' => min((int) $settings->banner_max_slots, BannerCycle::HARD_MAX_SLOTS),
                'price' => $settings->banner_cycle_price,
                'starts_at' => now()->startOfDay(),
                'ends_at' => now()->addMonths((int) $settings->banner_cycle_months)->endOfDay(),
                'is_active' => true,
            ],
        );
    }
}
