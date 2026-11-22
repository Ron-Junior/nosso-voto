<?php

namespace Tests\Feature\Showcase;

use App\Livewire\Auth\Register;
use App\Livewire\Vendor\VendorShowcase;
use App\Models\BannerCycle;
use App\Models\BannerSubmission;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\Wedding;
use App\Models\WeddingVendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VendorShowcaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_multi_category_vendor_appears_in_all_selected_categories_on_showcase(): void
    {
        Livewire::test(Register::class)
            ->call('selectRole', User::ROLE_VENDOR)
            ->set('name', 'Studio Beleza Completa')
            ->set('state', 'SP')
            ->set('city', 'São Paulo')
            ->set('email', 'beleza@exemplo.com.br')
            ->set('vendor_categories', ['maquiagem', 'penteado'])
            ->set('password', 'segredo123')
            ->set('password_confirmation', 'segredo123')
            ->call('register')
            ->assertHasNoErrors();

        $registeredProfile = VendorProfile::query()
            ->where('business_name', 'Studio Beleza Completa')
            ->firstOrFail();

        $this->assertSame('SP', $registeredProfile->state);
        $this->assertSame('São Paulo', $registeredProfile->city);
        $this->assertSame('maquiagem', $registeredProfile->category);
        $this->assertSame(['maquiagem', 'penteado'], $registeredProfile->categories);

        $buffetVendor = VendorProfile::factory()->withCategories(['buffet'])->create([
            'business_name' => 'Buffet Sabor Paulista',
            'state' => 'SP',
            'city' => 'São Paulo',
        ]);

        $curitibaBeautyVendor = VendorProfile::factory()->withCategories(['maquiagem', 'penteado'])->create([
            'business_name' => 'Studio Beleza Sul',
            'state' => 'PR',
            'city' => 'Curitiba',
        ]);

        Livewire::test(VendorShowcase::class)
            ->set('stateFilter', 'SP')
            ->set('cityFilter', 'São Paulo')
            ->set('categoryFilter', 'maquiagem')
            ->assertSee('Studio Beleza Completa')
            ->assertDontSee($buffetVendor->business_name)
            ->assertDontSee($curitibaBeautyVendor->business_name)
            ->set('categoryFilter', 'penteado')
            ->assertSee('Studio Beleza Completa')
            ->assertDontSee($buffetVendor->business_name)
            ->set('categoryFilter', 'buffet')
            ->assertSee($buffetVendor->business_name)
            ->assertDontSee('Studio Beleza Completa');
    }

    public function test_regional_highlights_appear_above_free_vendors_and_shuffle_randomly_between_requests(): void
    {
        $highlights = collect();

        foreach (range(1, 5) as $index) {
            $highlights->push(
                VendorProfile::factory()
                    ->regionalHighlight(now()->addDays(20))
                    ->withCategories(['decoracao'])
                    ->create([
                        'business_name' => "Destaque Regional {$index}",
                        'state' => 'SP',
                        'city' => 'São Paulo',
                    ])
            );
        }

        $expiredHighlight = VendorProfile::factory()
            ->regionalHighlight(now()->subDay())
            ->withCategories(['decoracao'])
            ->create([
                'business_name' => 'Destaque Expirado',
                'state' => 'SP',
                'city' => 'São Paulo',
            ]);

        $freeVendors = collect();
        foreach (range(1, 3) as $index) {
            $freeVendors->push(
                VendorProfile::factory()
                    ->withCategories(['decoracao'])
                    ->create([
                        'business_name' => "Fornecedor Gratuito {$index}",
                        'state' => 'SP',
                        'city' => 'São Paulo',
                        'is_regional_highlight' => false,
                    ])
            );
        }

        $highlightIds = $highlights->pluck('id')->all();
        $nonHighlightIds = collect([$expiredHighlight, ...$freeVendors])->pluck('id')->all();
        $observedOrders = [];

        for ($attempt = 0; $attempt < 15; $attempt++) {
            $html = Livewire::test(VendorShowcase::class)
                ->set('stateFilter', 'SP')
                ->set('cityFilter', 'São Paulo')
                ->set('categoryFilter', 'decoracao')
                ->html();

            preg_match_all('/data-vendor-id="(\d+)"/', $html, $matches);
            $renderedIds = array_map('intval', $matches[1] ?? []);

            $this->assertCount(9, $renderedIds);

            $topFive = array_slice($renderedIds, 0, 5);
            $bottomFour = array_slice($renderedIds, 5);

            $this->assertEqualsCanonicalizing($highlightIds, $topFive);
            $this->assertEqualsCanonicalizing($nonHighlightIds, $bottomFour);

            $observedOrders[] = implode(',', $topFive);
        }

        $this->assertGreaterThan(
            1,
            count(array_unique($observedOrders)),
            'Highlighted vendors should shuffle randomly across requests.'
        );
    }

    public function test_showcase_displays_up_to_five_active_banner_slots_and_supports_slug_route(): void
    {
        $cycle = BannerCycle::factory()->create([
            'title' => 'Ciclo Bimestral • São Paulo/SP',
            'state' => 'SP',
            'city' => 'São Paulo',
            'max_slots' => 5,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonths(2),
            'is_active' => true,
        ]);

        foreach (range(1, 5) as $slot) {
            $vendor = VendorProfile::factory()->withCategories(['fotografia'])->create([
                'business_name' => "Banner Fotografia {$slot}",
                'state' => 'SP',
                'city' => 'São Paulo',
            ]);

            BannerSubmission::factory()->active($slot)->create([
                'banner_cycle_id' => $cycle->id,
                'vendor_profile_id' => $vendor->id,
                'headline' => "Oferta Banner Slot {$slot}",
            ]);
        }

        $waitlistVendor = VendorProfile::factory()->withCategories(['fotografia'])->create([
            'business_name' => 'Fornecedor Fila Espera',
            'state' => 'SP',
            'city' => 'São Paulo',
        ]);
        BannerSubmission::factory()->waitlist()->create([
            'banner_cycle_id' => $cycle->id,
            'vendor_profile_id' => $waitlistVendor->id,
            'headline' => 'Chamada Waitlist Não Exibida',
        ]);

        $this->get(route('showcase.index', ['uf' => 'SP', 'cidade' => 'São Paulo']))
            ->assertOk()
            ->assertSee('Banner Principal')
            ->assertSee('5 de 5 vagas em destaque')
            ->assertSee('Oferta Banner Slot 1')
            ->assertSee('Oferta Banner Slot 5')
            ->assertDontSee('Chamada Waitlist Não Exibida')
            ->assertDontSee('100% Gratuito')
            ->assertDontSee('Sem mensalidades');

        $targetVendor = VendorProfile::query()->where('business_name', 'Banner Fotografia 2')->firstOrFail();

        $this->get(route('showcase.show', $targetVendor->slug))
            ->assertOk()
            ->assertSee('Banner Fotografia 2')
            ->assertSee('Áreas de Atuação');

        $this->get('/vitrine/fornecedor-inexistente-999')
            ->assertNotFound();
    }

    public function test_authenticated_couple_can_link_showcase_vendor_to_wedding_budget_in_one_click(): void
    {
        $vendorUser = User::factory()->vendor()->create([
            'name' => 'Camila Makeup & Hair',
            'email' => 'camila@makeup.com.br',
        ]);
        $vendorProfile = VendorProfile::factory()->withCategories(['maquiagem', 'penteado'])->create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Ateliê Camila Beauty',
            'state' => 'SP',
            'city' => 'Campinas',
            'whatsapp' => '(19) 99111-2222',
            'price_from' => 2800.00,
        ]);

        $coupleUser = User::factory()->couple()->create(['name' => 'Mariana & João']);
        $wedding = Wedding::factory()->create([
            'user_id' => $coupleUser->id,
            'partner_1_name' => 'Mariana',
            'partner_2_name' => 'João',
            'state' => 'SP',
            'city' => 'Campinas',
        ]);

        // Vendor cannot link showcase vendor to a wedding
        $this->actingAs($vendorUser);
        Livewire::test(VendorShowcase::class)
            ->call('linkVendorToWedding', $vendorProfile->id)
            ->assertForbidden();

        // Couple filters by their region & category and links vendor in 1 click
        $this->actingAs($coupleUser);

        $this->get(route('showcase.index'))
            ->assertOk()
            ->assertDontSee('100% Gratuito')
            ->assertDontSee('Sem mensalidades');

        Livewire::test(VendorShowcase::class)
            ->call('applyCoupleRegion')
            ->assertSet('stateFilter', 'SP')
            ->assertSet('cityFilter', 'Campinas')
            ->set('categoryFilter', 'penteado')
            ->assertSee('Ateliê Camila Beauty')
            ->call('linkVendorToWedding', $vendorProfile->id)
            ->assertDispatched('toast', message: 'Ateliê Camila Beauty vinculado ao orçamento do seu casamento!')
            ->assertSeeHtml('data-toast')
            ->assertSee('Vinculado ao Casamento')
            // Clicking again does not create duplicate
            ->call('linkVendorToWedding', $vendorProfile->id)
            ->assertDispatched('toast', message: 'Ateliê Camila Beauty já está vinculado ao seu casamento.');

        $this->assertDatabaseCount('wedding_vendors', 1);

        $linked = WeddingVendor::query()->where('wedding_id', $wedding->id)->first();
        $this->assertNotNull($linked);
        $this->assertSame($vendorProfile->id, $linked->vendor_profile_id);
        $this->assertSame('Ateliê Camila Beauty', $linked->vendor_name);
        $this->assertSame('penteado', $linked->category);
        $this->assertSame('2800.00', (string) $linked->estimated_amount);
        $this->assertSame('camila@makeup.com.br', $linked->contact_email);
        $this->assertSame('(19) 99111-2222', $linked->contact_phone);

        // Appears inside couple's budget dashboard
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ateliê Camila Beauty')
            ->assertSee('Explorar Vitrine');

        // Couple without wedding record yet gets one auto-provisioned on 1-click link
        $freshCouple = User::factory()->couple()->create(['name' => 'Bianca & Tiago']);
        $this->actingAs($freshCouple);

        Livewire::test(VendorShowcase::class)
            ->call('linkVendorToWedding', $vendorProfile->id)
            ->assertDispatched('toast', message: 'Ateliê Camila Beauty vinculado ao orçamento do seu casamento!');

        $this->assertNotNull($freshCouple->fresh()->wedding);
        $this->assertDatabaseHas('wedding_vendors', [
            'wedding_id' => $freshCouple->fresh()->wedding->id,
            'vendor_profile_id' => $vendorProfile->id,
        ]);
    }
}
