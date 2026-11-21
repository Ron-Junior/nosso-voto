<?php

namespace Tests\Feature\Wedding;

use App\Livewire\Wedding\CoupleDashboard;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingVendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_couple_can_create_and_edit_wedding_with_unique_slug_and_budget(): void
    {
        $couple = User::factory()->couple()->create(['name' => 'Ana & Lucas']);
        $this->actingAs($couple);

        Livewire::test(CoupleDashboard::class)
            ->assertSee('Cadastrar Meu Casamento')
            ->set('partner_1_name', 'Ana')
            ->set('partner_2_name', 'Lucas')
            ->set('wedding_date', now()->addDays(90)->toDateString())
            ->set('city', 'Belo Horizonte')
            ->set('state', 'mg')
            ->set('venue_name', 'Fazenda Terracota')
            ->set('estimated_budget', '75000.00')
            ->call('saveWedding')
            ->assertHasNoErrors()
            ->assertDispatched('toast')
            ->assertSeeHtml('data-toast')
            ->assertSee('Ana & Lucas')
            ->assertSee('Belo Horizonte - MG')
            ->assertSee('Faltam 90 dias')
            ->assertSee('R$ 75.000,00')
            ->assertDontSee('100% Gratuito')
            ->assertDontSee('Sem mensalidades');

        $wedding = $couple->fresh()->wedding;

        $this->assertNotNull($wedding);
        $this->assertSame('ana-e-lucas', $wedding->slug);
        $this->assertSame('MG', $wedding->state);
        $this->assertSame('75000.00', (string) $wedding->estimated_budget);

        // Second couple with identical names receives a distinct slug
        $secondCouple = User::factory()->couple()->create(['name' => 'Ana & Lucas 2']);
        $secondWedding = Wedding::factory()->create([
            'user_id' => $secondCouple->id,
            'partner_1_name' => 'Ana',
            'partner_2_name' => 'Lucas',
            'slug' => null,
        ]);

        $this->assertSame('ana-e-lucas-2', $secondWedding->slug);

        // First couple edits wedding and inline estimated budget
        $this->actingAs($couple);

        Livewire::test(CoupleDashboard::class)
            ->call('openWeddingModal')
            ->assertSet('showWeddingModal', true)
            ->set('venue_name', 'Espaço Vista Serra')
            ->call('saveWedding')
            ->assertHasNoErrors()
            ->assertSet('showWeddingModal', false)
            ->assertSee('Espaço Vista Serra')
            ->set('estimated_budget', '82500.00')
            ->call('saveBudget')
            ->assertHasNoErrors()
            ->assertDispatched('toast', message: 'Orçamento pretendido atualizado com sucesso.')
            ->assertSee('R$ 82.500,00');

        $this->assertSame('Espaço Vista Serra', $wedding->fresh()->venue_name);
        $this->assertSame('82500.00', (string) $wedding->fresh()->estimated_budget);
    }

    public function test_couple_can_autonomously_create_edit_and_delete_external_vendor_without_vendor_profile(): void
    {
        $couple = User::factory()->couple()->create();
        $wedding = Wedding::factory()->create([
            'user_id' => $couple->id,
            'estimated_budget' => 50000.00,
        ]);

        $this->actingAs($couple);

        Livewire::test(CoupleDashboard::class)
            ->call('openNewVendorModal')
            ->assertSet('showVendorModal', true)
            ->set('vendor_name', 'Buffet Sabor Mineiro')
            ->set('category', 'buffet')
            ->set('status', WeddingVendor::STATUS_CONTRACTED)
            ->set('contact_name', 'Renata Silva')
            ->set('contact_phone', '(31) 99888-7766')
            ->set('contact_email', 'contato@sabormineiro.com.br')
            ->set('estimated_amount', '15000.00')
            ->set('contracted_amount', '14200.00')
            ->set('paid_amount', '4200.00')
            ->set('notes', 'Cardápio completo para 120 convidados.')
            ->call('saveVendor')
            ->assertHasNoErrors()
            ->assertSet('showVendorModal', false)
            ->assertDispatched('toast', message: 'Fornecedor cadastrado com sucesso.')
            ->assertSee('Buffet Sabor Mineiro')
            ->assertSee('Cadastro manual')
            ->assertSee('R$ 14.200,00')
            ->assertSee('R$ 4.200,00')
            ->assertSee('R$ 10.000,00');

        $vendor = $wedding->vendors()->first();

        $this->assertNotNull($vendor);
        $this->assertNull($vendor->vendor_profile_id);
        $this->assertSame(WeddingVendor::SIDE_COUPLE, $vendor->created_by_side);
        $this->assertGreaterThanOrEqual(40, strlen($vendor->invite_token));

        // Edit vendor
        Livewire::test(CoupleDashboard::class)
            ->call('editVendor', $vendor->id)
            ->assertSet('editingVendorId', $vendor->id)
            ->set('paid_amount', '14200.00')
            ->set('status', WeddingVendor::STATUS_PAID)
            ->call('saveVendor')
            ->assertHasNoErrors()
            ->assertDispatched('toast', message: 'Fornecedor atualizado com sucesso.')
            ->assertSee('Quitado')
            ->assertSee('R$ 0,00');

        $vendor->refresh();
        $this->assertSame(WeddingVendor::STATUS_PAID, $vendor->status);
        $this->assertSame('14200.00', (string) $vendor->paid_amount);

        // Delete vendor
        Livewire::test(CoupleDashboard::class)
            ->call('deleteVendor', $vendor->id)
            ->assertDispatched('toast', message: 'Fornecedor removido do planejamento.')
            ->assertDontSee('Buffet Sabor Mineiro');

        $this->assertDatabaseMissing('wedding_vendors', ['id' => $vendor->id]);
    }

    public function test_budget_calculations_and_overrun_alerts_update_reactively(): void
    {
        $couple = User::factory()->couple()->create();
        $wedding = Wedding::factory()->create([
            'user_id' => $couple->id,
            'estimated_budget' => 20000.00,
        ]);

        $this->actingAs($couple);

        // Vendor 1 within item estimate and within global budget
        $component = Livewire::test(CoupleDashboard::class)
            ->call('openNewVendorModal')
            ->set('vendor_name', 'Espaço Solar')
            ->set('category', 'espaco')
            ->set('status', WeddingVendor::STATUS_CONTRACTED)
            ->set('estimated_amount', '10000.00')
            ->set('contracted_amount', '10000.00')
            ->set('paid_amount', '4000.00')
            ->call('saveVendor')
            ->assertHasNoErrors();

        $vendor1 = $wedding->vendors()->where('vendor_name', 'Espaço Solar')->firstOrFail();

        $component
            ->assertSee('R$ 10.000,00')
            ->assertSee('R$ 4.000,00')
            ->assertSee('R$ 6.000,00')
            ->assertDontSeeHtml('data-budget-alert="global"')
            ->assertDontSeeHtml("data-vendor-over-estimate=\"{$vendor1->id}\"");

        // Vendor 2 exceeds item estimate AND pushes total contracted above wedding estimated_budget
        $component
            ->call('openNewVendorModal')
            ->set('vendor_name', 'Decoradora Encanto')
            ->set('category', 'decoracao')
            ->set('status', WeddingVendor::STATUS_CONTRACTED)
            ->set('estimated_amount', '8000.00')
            ->set('contracted_amount', '12500.00')
            ->set('paid_amount', '3500.00')
            ->call('saveVendor')
            ->assertHasNoErrors();

        $vendor2 = $wedding->vendors()->where('vendor_name', 'Decoradora Encanto')->firstOrFail();

        $this->assertSame(22500.0, $wedding->totalContracted());
        $this->assertSame(7500.0, $wedding->totalPaid());
        $this->assertSame(15000.0, $wedding->remainingToPay());
        $this->assertSame(-2500.0, $wedding->budgetBalance());
        $this->assertTrue($wedding->isOverBudget());
        $this->assertTrue($vendor2->isOverEstimate());

        $component
            ->assertSee('R$ 22.500,00')
            ->assertSee('R$ 7.500,00')
            ->assertSee('R$ 15.000,00')
            ->assertSee('Estouro de R$ 2.500,00')
            ->assertSeeHtml('data-budget-alert="global"')
            ->assertSeeHtml("data-vendor-over-estimate=\"{$vendor2->id}\"")
            ->assertSee('Acima do teto previsto (+R$ 4.500,00)');

        // Raising estimated_budget clears the global overrun alert reactively
        $component
            ->set('estimated_budget', '30000.00')
            ->call('saveBudget')
            ->assertDontSeeHtml('data-budget-alert="global"')
            ->assertSee('Saldo disponível: R$ 7.500,00');
    }

    public function test_each_vendor_receives_unique_invite_token_and_can_regenerate_or_copy(): void
    {
        $couple = User::factory()->couple()->create();
        $wedding = Wedding::factory()->create(['user_id' => $couple->id]);

        $vendorA = WeddingVendor::factory()->create([
            'wedding_id' => $wedding->id,
            'vendor_name' => 'Cerimonial Alfa',
            'category' => 'cerimonialista',
        ]);
        $vendorB = WeddingVendor::factory()->create([
            'wedding_id' => $wedding->id,
            'vendor_name' => 'Foto Beta',
            'category' => 'fotografia',
        ]);

        $this->assertNotSame($vendorA->invite_token, $vendorB->invite_token);
        $this->assertGreaterThanOrEqual(40, strlen($vendorA->invite_token));

        $originalToken = $vendorA->invite_token;

        $this->actingAs($couple);

        Livewire::test(CoupleDashboard::class)
            ->assertSee($vendorA->inviteUrl())
            ->assertSee($vendorB->inviteUrl())
            ->assertSee('Copiar Link de Acesso')
            ->call('notifyInviteCopied', $vendorA->vendor_name)
            ->assertDispatched('toast', message: 'Link de acesso de Cerimonial Alfa copiado para a área de transferência.')
            ->call('regenerateVendorInviteToken', $vendorA->id)
            ->assertDispatched('toast', message: 'Novo link de acesso gerado para Cerimonial Alfa.');

        $vendorA->refresh();
        $this->assertNotSame($originalToken, $vendorA->invite_token);
        $this->assertNotSame($vendorB->invite_token, $vendorA->invite_token);
    }

    public function test_vendor_filters_and_cross_couple_isolation_are_enforced(): void
    {
        $coupleA = User::factory()->couple()->create();
        $weddingA = Wedding::factory()->create(['user_id' => $coupleA->id]);
        $vendorA = WeddingVendor::factory()->create([
            'wedding_id' => $weddingA->id,
            'vendor_name' => 'Atelier Flor & Folha',
            'category' => 'decoracao',
            'status' => WeddingVendor::STATUS_CONTRACTED,
        ]);
        WeddingVendor::factory()->create([
            'wedding_id' => $weddingA->id,
            'vendor_name' => 'Banda Harmonia',
            'category' => 'musica',
            'status' => WeddingVendor::STATUS_QUOTING,
        ]);

        $coupleB = User::factory()->couple()->create();
        Wedding::factory()->create(['user_id' => $coupleB->id]);

        // Couple A can filter case-insensitively by search, category, and status
        $this->actingAs($coupleA);

        Livewire::test(CoupleDashboard::class)
            ->set('search', 'flor')
            ->assertSee('Atelier Flor & Folha')
            ->assertDontSee('Banda Harmonia')
            ->set('search', '')
            ->set('categoryFilter', 'musica')
            ->assertSee('Banda Harmonia')
            ->assertDontSee('Atelier Flor & Folha')
            ->set('categoryFilter', 'all')
            ->set('statusFilter', WeddingVendor::STATUS_CONTRACTED)
            ->assertSee('Atelier Flor & Folha')
            ->assertDontSee('Banda Harmonia');

        // Couple B cannot edit, delete, or regenerate invite token for Couple A's vendor
        $this->actingAs($coupleB);

        Livewire::test(CoupleDashboard::class)
            ->call('editVendor', $vendorA->id)
            ->assertNotFound();

        Livewire::test(CoupleDashboard::class)
            ->call('deleteVendor', $vendorA->id)
            ->assertNotFound();

        Livewire::test(CoupleDashboard::class)
            ->call('regenerateVendorInviteToken', $vendorA->id)
            ->assertNotFound();

        $this->assertDatabaseHas('wedding_vendors', ['id' => $vendorA->id]);

        // Vendor role cannot access /painel
        $vendorUser = User::factory()->vendor()->create();
        $this->actingAs($vendorUser)
            ->get('/painel')
            ->assertForbidden();
    }

    public function test_wedding_state_and_city_dropdowns_update_reactively_and_validate_region(): void
    {
        $couple = User::factory()->couple()->create(['name' => 'Júlia & Marcos']);
        $this->actingAs($couple);

        $component = Livewire::test(CoupleDashboard::class)
            ->assertSeeHtml('<select id="onboard_state"')
            ->assertSeeHtml('<select id="onboard_city"')
            ->assertSee('SP — São Paulo')
            ->set('partner_1_name', 'Júlia')
            ->set('partner_2_name', 'Marcos')
            ->set('state', 'SP')
            ->assertSee('Campos do Jordão')
            ->set('city', 'Campos do Jordão')
            ->set('state', 'RJ')
            ->assertSet('city', '')
            ->assertSee('Armação dos Búzios')
            ->assertDontSeeHtml('<option value="Campos do Jordão">Campos do Jordão</option>');

        // City from another state is rejected by validation
        $component
            ->set('city', 'Campos do Jordão')
            ->call('saveWedding')
            ->assertHasErrors(['city']);

        // Selecting a valid city for RJ succeeds and modal also uses dropdowns
        $component
            ->set('city', 'Armação dos Búzios')
            ->set('estimated_budget', '60000.00')
            ->call('saveWedding')
            ->assertHasNoErrors()
            ->assertSee('Armação dos Búzios - RJ')
            ->call('openWeddingModal')
            ->assertSeeHtml('<select id="modal_state"')
            ->assertSeeHtml('<select id="modal_city"')
            ->set('state', 'SC')
            ->assertSet('city', '')
            ->assertSee('Balneário Camboriú')
            ->set('city', 'Balneário Camboriú')
            ->call('saveWedding')
            ->assertHasNoErrors()
            ->assertSee('Balneário Camboriú - SC');

        $wedding = $couple->fresh()->wedding;
        $this->assertNotNull($wedding);
        $this->assertSame('SC', $wedding->state);
        $this->assertSame('Balneário Camboriú', $wedding->city);
    }

    public function test_couple_dashboard_renders_multiple_vendors_within_four_queries(): void
    {
        $couple = User::factory()->couple()->create();
        $wedding = Wedding::factory()->create([
            'user_id' => $couple->id,
            'estimated_budget' => 80000.00,
        ]);

        WeddingVendor::factory()->count(3)->create([
            'wedding_id' => $wedding->id,
        ]);

        $this->actingAs($couple);

        DB::enableQueryLog();
        DB::flushQueryLog();

        Livewire::test(CoupleDashboard::class)
            ->assertOk();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(4, count($queries));
    }
}
