<?php

namespace Tests\Feature\Vendor;

use App\Exceptions\BannerCycleFullException;
use App\Livewire\Admin\PlatformSettings;
use App\Livewire\Dashboard\UserPanel;
use App\Models\BannerCycle;
use App\Models\BannerSubmission;
use App\Models\User;
use App\Models\VendorProfile;
use App\Services\BannerCycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BannerCyclesAndMonetizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_edit_multiple_categories_and_activate_regional_highlight_on_panel(): void
    {
        $vendorUser = User::factory()->vendor()->create(['name' => 'Espaço & Sabor']);
        $profile = VendorProfile::factory()->withCategories(['buffet'])->create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Espaço & Sabor Eventos',
            'state' => 'SP',
            'city' => 'Campinas',
            'is_regional_highlight' => false,
            'highlight_expires_at' => null,
        ]);

        $this->actingAs($vendorUser);

        // Cannot save empty categories
        Livewire::test(UserPanel::class)
            ->call('openProfileModal')
            ->set('profile_categories', [])
            ->call('saveVendorProfile')
            ->assertHasErrors(['profile_categories']);

        // Can update to multiple categories
        Livewire::test(UserPanel::class)
            ->call('openProfileModal')
            ->set('profile_categories', ['buffet', 'espaco', 'decoracao'])
            ->set('profile_category', 'buffet')
            ->call('saveVendorProfile')
            ->assertHasNoErrors()
            ->assertDispatched('toast', message: 'Perfil profissional atualizado com sucesso.')
            ->assertSee('Buffet & Gastronomia')
            ->assertSee('Espaço & Recepção')
            ->assertSee('Decoração');

        $profile->refresh();
        $this->assertSame('buffet', $profile->category);
        $this->assertSame(['buffet', 'espaco', 'decoracao'], $profile->categories);

        // Activate regional highlight monthly subscription
        Livewire::test(UserPanel::class)
            ->call('activateRegionalHighlight')
            ->assertHasNoErrors()
            ->assertDispatched('toast', message: 'Destaque Regional ativado por 30 dias! Seu perfil entra na rotação randômica no topo da Vitrine.')
            ->assertSeeHtml('data-toast')
            ->assertSee('Ativo');

        $profile->refresh();
        $this->assertTrue($profile->is_regional_highlight);
        $this->assertTrue($profile->hasActiveRegionalHighlight());
        $this->assertNotNull($profile->highlight_expires_at);
        $this->assertTrue($profile->highlight_expires_at->isFuture());
    }

    public function test_all_regional_vendors_can_submit_to_active_banner_cycle_and_sixth_activation_is_blocked(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        // Admin creates an active cycle in São Paulo/SP with 5 slots
        Livewire::test(PlatformSettings::class)
            ->set('cycle_title', 'Ciclo Bimestral Mai-Jun • São Paulo/SP')
            ->set('cycle_state', 'SP')
            ->set('cycle_city', 'São Paulo')
            ->set('cycle_duration_months', 2)
            ->set('cycle_max_slots', 5)
            ->set('cycle_price', '149.90')
            ->set('cycle_starts_at', now()->subDay()->toDateString())
            ->set('cycle_ends_at', now()->addMonths(2)->toDateString())
            ->call('createBannerCycle')
            ->assertHasNoErrors()
            ->assertDispatched('toast', message: 'Ciclo do banner criado com sucesso.')
            ->assertSee('Ciclo Bimestral Mai-Jun • São Paulo/SP');

        $cycle = BannerCycle::query()->firstOrFail();
        $this->assertSame(5, $cycle->max_slots);

        // Vendor from another city cannot submit to São Paulo cycle
        $outsideUser = User::factory()->vendor()->create();
        $outsideProfile = VendorProfile::factory()->create([
            'user_id' => $outsideUser->id,
            'state' => 'PR',
            'city' => 'Curitiba',
        ]);

        $this->actingAs($outsideUser);
        Livewire::test(UserPanel::class)
            ->assertDontSee('Ciclo Bimestral Mai-Jun • São Paulo/SP')
            ->set('selected_banner_cycle_id', $cycle->id)
            ->set('banner_headline', 'Tentativa de fora da região')
            ->call('submitToBannerCycle')
            ->assertHasErrors(['banner_submission']);

        $this->assertDatabaseMissing('banner_submissions', [
            'vendor_profile_id' => $outsideProfile->id,
        ]);

        // All 6 regional vendors in São Paulo/SP can submit applications
        $spVendors = collect();
        foreach (range(1, 6) as $index) {
            $user = User::factory()->vendor()->create(['name' => "Fornecedor SP {$index}"]);
            $profile = VendorProfile::factory()->withCategories(['decoracao'])->create([
                'user_id' => $user->id,
                'business_name' => "Fornecedor SP {$index}",
                'state' => 'SP',
                'city' => 'São Paulo',
            ]);
            $spVendors->push(['user' => $user, 'profile' => $profile]);

            $this->actingAs($user);
            Livewire::test(UserPanel::class)
                ->assertSee('5 de 5 vagas disponíveis')
                ->set('selected_banner_cycle_id', $cycle->id)
                ->set('banner_headline', "Destaque Exclusivo Fornecedor {$index}")
                ->set('banner_cta_link', "https://exemplo.com.br/fornecedor-{$index}")
                ->call('submitToBannerCycle')
                ->assertHasNoErrors()
                ->assertDispatched('toast', message: 'Candidatura ao Banner Principal enviada com sucesso! Aguarde a aprovação.');
        }

        $submissions = BannerSubmission::query()
            ->where('banner_cycle_id', $cycle->id)
            ->orderBy('id')
            ->get();

        $this->assertCount(6, $submissions);

        // Admin approves the first 5 submissions (slots 1..5)
        $this->actingAs($admin);

        foreach ($submissions->take(5) as $idx => $submission) {
            $expectedSlot = $idx + 1;

            Livewire::test(PlatformSettings::class)
                ->call('approveBannerSubmission', $submission->id)
                ->assertHasNoErrors()
                ->assertDispatched(
                    'toast',
                    message: "Candidatura de {$submission->vendorProfile->business_name} aprovada na Vaga #{$expectedSlot}."
                );
        }

        $this->assertSame(5, $cycle->activeSubmissions()->count());

        // Vendor sees 0 of 5 slots remaining and a 7th vendor submitting enters waitlist automatically
        $seventhUser = User::factory()->vendor()->create(['name' => 'Fornecedor SP 7']);
        $seventhProfile = VendorProfile::factory()->withCategories(['fotografia'])->create([
            'user_id' => $seventhUser->id,
            'business_name' => 'Fornecedor SP 7',
            'state' => 'SP',
            'city' => 'São Paulo',
        ]);
        $this->actingAs($seventhUser);
        Livewire::test(UserPanel::class)
            ->assertSee('0 de 5 vagas disponíveis')
            ->set('selected_banner_cycle_id', $cycle->id)
            ->set('banner_headline', 'Fornecedor 7 na Fila')
            ->call('submitToBannerCycle')
            ->assertHasNoErrors()
            ->assertDispatched(
                'toast',
                message: 'Candidatura registrada na Lista de Espera deste ciclo (as 5 vagas já estão preenchidas).'
            );

        $this->assertDatabaseHas('banner_submissions', [
            'banner_cycle_id' => $cycle->id,
            'vendor_profile_id' => $seventhProfile->id,
            'status' => BannerSubmission::STATUS_WAITLIST,
            'slot_number' => null,
        ]);

        // Attempt #1: Admin tries to activate the 6th vendor via PlatformSettings -> blocked and moved to waitlist
        $sixthSubmission = $submissions->last()->fresh();
        $this->actingAs($admin);

        Livewire::test(PlatformSettings::class)
            ->call('approveBannerSubmission', $sixthSubmission->id)
            ->assertHasErrors(['banner_slot_limit']);

        $sixthSubmission->refresh();
        $this->assertSame(BannerSubmission::STATUS_WAITLIST, $sixthSubmission->status);
        $this->assertNull($sixthSubmission->slot_number);
        $this->assertSame(5, $cycle->activeSubmissions()->count());

        // Attempt #2: Service layer throws BannerCycleFullException
        $service = app(BannerCycleService::class);
        $thrownByService = false;

        try {
            $service->activateSubmission($sixthSubmission);
        } catch (BannerCycleFullException) {
            $thrownByService = true;
        }

        $this->assertTrue($thrownByService, 'BannerCycleService must block activating a 6th slot.');
        $this->assertSame(5, $cycle->activeSubmissions()->count());

        // Attempt #3: Direct Eloquent model mutation throws BannerCycleFullException
        $thrownByModel = false;

        try {
            $sixthSubmission->update(['status' => BannerSubmission::STATUS_ACTIVE, 'slot_number' => 6]);
        } catch (BannerCycleFullException) {
            $thrownByModel = true;
        }

        $this->assertTrue($thrownByModel, 'BannerSubmission model guard must block activating a 6th slot.');
        $this->assertSame(5, $cycle->activeSubmissions()->count());

        // Freeing slot #3 allows the 6th vendor to be approved into slot #3 while keeping max 5 active
        $thirdSubmission = $submissions->get(2)->fresh();

        Livewire::test(PlatformSettings::class)
            ->call('waitlistBannerSubmission', $thirdSubmission->id)
            ->assertDispatched('toast', message: "{$thirdSubmission->vendorProfile->business_name} movido para a Lista de Espera.")
            ->call('approveBannerSubmission', $sixthSubmission->id)
            ->assertHasNoErrors()
            ->assertDispatched(
                'toast',
                message: "Candidatura de {$sixthSubmission->vendorProfile->business_name} aprovada na Vaga #3."
            );

        $this->assertSame(5, $cycle->activeSubmissions()->count());
        $this->assertSame(3, $sixthSubmission->fresh()->slot_number);
    }

    public function test_admin_banner_max_slots_setting_enforces_hard_limit_of_five(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(PlatformSettings::class)
            ->set('banner_max_slots', 6)
            ->call('save')
            ->assertHasErrors(['banner_max_slots']);

        Livewire::test(PlatformSettings::class)
            ->set('cycle_title', 'Ciclo Inválido 6 Vagas')
            ->set('cycle_state', 'SP')
            ->set('cycle_city', 'São Paulo')
            ->set('cycle_duration_months', 2)
            ->set('cycle_max_slots', 6)
            ->set('cycle_price', '149.90')
            ->set('cycle_starts_at', now()->toDateString())
            ->set('cycle_ends_at', now()->addMonths(2)->toDateString())
            ->call('createBannerCycle')
            ->assertHasErrors(['cycle_max_slots']);
    }
}
