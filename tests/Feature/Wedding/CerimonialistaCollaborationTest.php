<?php

namespace Tests\Feature\Wedding;

use App\Livewire\Wedding\CoupleDashboard;
use App\Livewire\Wedding\VendorWeddingAccess;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingVendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CerimonialistaCollaborationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cerimonialista_token_opens_consolidated_panel_only_for_its_wedding_without_pix_or_promotional_clutter(): void
    {
        $coupleA = User::factory()->couple()->create(['name' => 'Mariana & Pedro']);
        $weddingA = Wedding::factory()->create([
            'user_id' => $coupleA->id,
            'partner_1_name' => 'Mariana',
            'partner_2_name' => 'Pedro',
            'city' => 'Curitiba',
            'state' => 'PR',
            'venue_name' => 'Castelo do Batel',
            'estimated_budget' => 90000.00,
        ]);

        $cerimonialista = WeddingVendor::factory()->create([
            'wedding_id' => $weddingA->id,
            'vendor_name' => 'Assessoria Realize',
            'category' => 'cerimonialista',
            'status' => WeddingVendor::STATUS_CONTRACTED,
            'contact_phone' => '(41) 99123-4567',
            'estimated_amount' => 8000.00,
            'contracted_amount' => 8000.00,
            'paid_amount' => 4000.00,
        ]);

        WeddingVendor::factory()->create([
            'wedding_id' => $weddingA->id,
            'vendor_name' => 'Estúdio Retrato Vivo',
            'category' => 'fotografia',
            'status' => WeddingVendor::STATUS_QUOTING,
            'contact_phone' => '(11) 99876-5432',
            'estimated_amount' => 7000.00,
            'contracted_amount' => 6500.00,
            'paid_amount' => 0.00,
        ]);

        WeddingVendor::factory()->create([
            'wedding_id' => $weddingA->id,
            'vendor_name' => 'Buffet Aromas',
            'category' => 'buffet',
            'status' => WeddingVendor::STATUS_PAID,
            'contact_phone' => '3133334444',
            'estimated_amount' => 25000.00,
            'contracted_amount' => 24000.00,
            'paid_amount' => 24000.00,
        ]);

        // Another wedding whose vendors must never leak into Wedding A's view
        $weddingB = Wedding::factory()->create([
            'partner_1_name' => 'Carla',
            'partner_2_name' => 'Bruno',
        ]);
        $vendorFromWeddingB = WeddingVendor::factory()->create([
            'wedding_id' => $weddingB->id,
            'vendor_name' => 'Banda Secreta Outro Casamento',
            'category' => 'musica',
        ]);

        $this->get(route('vendor.wedding.access', $cerimonialista->invite_token))
            ->assertOk()
            ->assertSee('Visão Consolidada do Cerimonialista')
            ->assertSee('Mariana & Pedro')
            ->assertSee('Curitiba - PR')
            ->assertSee('Castelo do Batel')
            ->assertSee('Assessoria Realize')
            ->assertSee('Estúdio Retrato Vivo')
            ->assertSee('Buffet Aromas')
            ->assertSee('https://wa.me/5511998765432')
            ->assertSee('tel:+5511998765432')
            ->assertSee('https://wa.me/553133334444')
            ->assertSee('tel:+553133334444')
            ->assertSee('R$ 38.500,00')
            ->assertSee('R$ 28.000,00')
            ->assertSee('R$ 10.500,00')
            ->assertDontSee('Banda Secreta Outro Casamento')
            ->assertDontSee('Carla & Bruno')
            ->assertDontSee('100% Gratuito')
            ->assertDontSee('Sem mensalidades')
            ->assertDontSee('Chave Pix')
            ->assertDontSee('Carteira de Presentes');

        // Cross-wedding mutations by ID must return 404
        Livewire::test(VendorWeddingAccess::class, ['token' => $cerimonialista->invite_token])
            ->call('editVendor', $vendorFromWeddingB->id)
            ->assertNotFound();

        Livewire::test(VendorWeddingAccess::class, ['token' => $cerimonialista->invite_token])
            ->call('updateVendorStatus', $vendorFromWeddingB->id, WeddingVendor::STATUS_PAID)
            ->assertNotFound();

        // Unknown token and standalone vendor record (without wedding_id) must return 404
        $this->get('/acesso/profissional/token-inexistente-xyz')
            ->assertNotFound();

        $standaloneVendor = WeddingVendor::factory()->create([
            'wedding_id' => null,
            'category' => 'cerimonialista',
        ]);

        $this->get(route('vendor.wedding.access', $standaloneVendor->invite_token))
            ->assertNotFound();
    }

    public function test_cerimonialista_can_filter_update_vendors_and_add_new_vendor_reflecting_in_couple_dashboard(): void
    {
        $couple = User::factory()->couple()->create(['name' => 'Sofia & Daniel']);
        $wedding = Wedding::factory()->create([
            'user_id' => $couple->id,
            'partner_1_name' => 'Sofia',
            'partner_2_name' => 'Daniel',
            'estimated_budget' => 60000.00,
        ]);

        $cerimonialista = WeddingVendor::factory()->create([
            'wedding_id' => $wedding->id,
            'vendor_name' => 'Cerimonial Elo',
            'category' => 'cerimonialista',
            'status' => WeddingVendor::STATUS_CONTRACTED,
            'estimated_amount' => 6000.00,
            'contracted_amount' => 6000.00,
            'paid_amount' => 2000.00,
        ]);

        $fotografia = WeddingVendor::factory()->create([
            'wedding_id' => $wedding->id,
            'vendor_name' => 'Clique Eterno',
            'category' => 'fotografia',
            'status' => WeddingVendor::STATUS_QUOTING,
            'contact_name' => 'João',
            'contact_phone' => '(11) 98888-1111',
            'contact_email' => 'joao@clique.com.br',
            'estimated_amount' => 7500.00,
            'contracted_amount' => 7000.00,
            'paid_amount' => 0.00,
            'notes' => 'Aguardando retorno de proposta.',
        ]);

        $decoracao = WeddingVendor::factory()->create([
            'wedding_id' => $wedding->id,
            'vendor_name' => 'Atelier Botânica',
            'category' => 'decoracao',
            'status' => WeddingVendor::STATUS_PAID,
            'estimated_amount' => 5000.00,
            'contracted_amount' => 5000.00,
            'paid_amount' => 5000.00,
        ]);

        $component = Livewire::test(VendorWeddingAccess::class, ['token' => $cerimonialista->invite_token]);

        // Filter search, category, and status
        $component
            ->set('search', 'Clique')
            ->assertSee('Clique Eterno')
            ->assertDontSee('Atelier Botânica')
            ->assertDontSeeHtml("wire:key=\"cerimonial-vendor-{$cerimonialista->id}\"")
            ->set('search', '')
            ->set('categoryFilter', 'decoracao')
            ->assertSee('Atelier Botânica')
            ->assertDontSee('Clique Eterno')
            ->assertDontSeeHtml("wire:key=\"cerimonial-vendor-{$cerimonialista->id}\"")
            ->set('categoryFilter', 'all')
            ->set('statusFilter', WeddingVendor::STATUS_QUOTING)
            ->assertSee('Clique Eterno')
            ->assertDontSee('Atelier Botânica')
            ->assertDontSeeHtml("wire:key=\"cerimonial-vendor-{$cerimonialista->id}\"")
            ->set('statusFilter', 'all');

        // Quick status update
        $component
            ->call('updateVendorStatus', $fotografia->id, WeddingVendor::STATUS_CONTRACTED)
            ->assertDispatched('toast', message: 'Status de Clique Eterno atualizado para Contratado.')
            ->assertSeeHtml('data-toast');

        $this->assertSame(WeddingVendor::STATUS_CONTRACTED, $fotografia->fresh()->status);

        // Full edit of another vendor (contacts, values, notes)
        $component
            ->call('editVendor', $fotografia->id)
            ->assertSet('showVendorModal', true)
            ->assertSet('editingVendorId', $fotografia->id)
            ->set('contact_name', 'João Almeida')
            ->set('contact_phone', '(11) 97777-2222')
            ->set('contact_email', 'atendimento@clique.com.br')
            ->set('contracted_amount', '7200.00')
            ->set('paid_amount', '3600.00')
            ->set('status', WeddingVendor::STATUS_CONTRACTED)
            ->set('notes', 'Chegada da equipe às 14h para making of.')
            ->call('saveVendor')
            ->assertHasNoErrors()
            ->assertSet('showVendorModal', false)
            ->assertDispatched('toast', message: 'Fornecedor Clique Eterno atualizado com sucesso.');

        $fotografia->refresh();
        $this->assertSame('João Almeida', $fotografia->contact_name);
        $this->assertSame('(11) 97777-2222', $fotografia->contact_phone);
        $this->assertSame('atendimento@clique.com.br', $fotografia->contact_email);
        $this->assertSame('7200.00', (string) $fotografia->contracted_amount);
        $this->assertSame('3600.00', (string) $fotografia->paid_amount);
        $this->assertSame('Chegada da equipe às 14h para making of.', $fotografia->notes);

        // Add a brand new vendor on behalf of the couple
        $component
            ->call('openNewVendorModal')
            ->assertSet('showVendorModal', true)
            ->set('vendor_name', 'Iluminação Cênica Luz')
            ->set('category', 'musica')
            ->set('status', WeddingVendor::STATUS_CONTRACTED)
            ->set('contact_name', 'Henrique')
            ->set('contact_phone', '(21) 99999-3333')
            ->set('contact_email', 'contato@luzcenica.com.br')
            ->set('estimated_amount', '4500.00')
            ->set('contracted_amount', '4200.00')
            ->set('paid_amount', '1200.00')
            ->set('notes', 'Montagem da pista liberada às 11h.')
            ->call('saveVendor')
            ->assertHasNoErrors()
            ->assertSet('showVendorModal', false)
            ->assertDispatched('toast', message: 'Fornecedor Iluminação Cênica Luz adicionado ao casamento com sucesso.')
            ->assertSee('Iluminação Cênica Luz');

        $addedVendor = $wedding->vendors()->where('vendor_name', 'Iluminação Cênica Luz')->first();
        $this->assertNotNull($addedVendor);
        $this->assertSame(WeddingVendor::SIDE_COUPLE, $addedVendor->created_by_side);
        $this->assertNull($addedVendor->vendor_profile_id);
        $this->assertGreaterThanOrEqual(40, strlen($addedVendor->invite_token));

        // Couple immediately sees every change and the new vendor in /painel
        $this->actingAs($couple);

        Livewire::test(CoupleDashboard::class)
            ->assertSee('Clique Eterno')
            ->assertSee('João Almeida')
            ->assertSee('Chegada da equipe às 14h para making of.')
            ->assertSee('Iluminação Cênica Luz')
            ->assertSee('Montagem da pista liberada às 11h.')
            ->assertSee('R$ 22.400,00')
            ->assertSee('R$ 11.800,00')
            ->assertSee('R$ 10.600,00');
    }

    public function test_non_cerimonialista_token_cannot_access_consolidated_list_or_collaborative_mutations(): void
    {
        $wedding = Wedding::factory()->create([
            'partner_1_name' => 'Beatriz',
            'partner_2_name' => 'Gustavo',
        ]);

        WeddingVendor::factory()->create([
            'wedding_id' => $wedding->id,
            'vendor_name' => 'Cerimonial Premium',
            'category' => 'cerimonialista',
        ]);

        $fotografia = WeddingVendor::factory()->create([
            'wedding_id' => $wedding->id,
            'vendor_name' => 'Luz & Arte Foto',
            'category' => 'fotografia',
            'status' => WeddingVendor::STATUS_CONTRACTED,
            'contracted_amount' => 9500.00,
            'paid_amount' => 4500.00,
            'notes' => 'Ensaio pré-wedding confirmado.',
        ]);

        $decoracao = WeddingVendor::factory()->create([
            'wedding_id' => $wedding->id,
            'vendor_name' => 'Flores do Campo Decorações',
            'category' => 'decoracao',
            'status' => WeddingVendor::STATUS_QUOTING,
        ]);

        $this->get(route('vendor.wedding.access', $fotografia->invite_token))
            ->assertOk()
            ->assertSee('Acesso do Profissional')
            ->assertSee('Fotografia & Vídeo')
            ->assertSee('Beatriz & Gustavo')
            ->assertSee('Luz & Arte Foto')
            ->assertSee('Ensaio pré-wedding confirmado.')
            ->assertDontSee('Cerimonial Premium')
            ->assertDontSee('Flores do Campo Decorações')
            ->assertDontSee('Adicionar Fornecedor para o Casal')
            ->assertDontSee('Visão Consolidada do Cerimonialista');

        Livewire::test(VendorWeddingAccess::class, ['token' => $fotografia->invite_token])
            ->call('openNewVendorModal')
            ->assertForbidden();

        Livewire::test(VendorWeddingAccess::class, ['token' => $fotografia->invite_token])
            ->call('editVendor', $decoracao->id)
            ->assertForbidden();

        Livewire::test(VendorWeddingAccess::class, ['token' => $fotografia->invite_token])
            ->call('updateVendorStatus', $decoracao->id, WeddingVendor::STATUS_PAID)
            ->assertForbidden();

        Livewire::test(VendorWeddingAccess::class, ['token' => $fotografia->invite_token])
            ->set('vendor_name', 'Fornecedor Não Autorizado')
            ->call('saveVendor')
            ->assertForbidden();

        $this->assertSame(WeddingVendor::STATUS_QUOTING, $decoracao->fresh()->status);
        $this->assertDatabaseMissing('wedding_vendors', ['vendor_name' => 'Fornecedor Não Autorizado']);
    }

    public function test_authenticated_vendor_sees_linked_weddings_and_couple_sees_cerimonialista_badge(): void
    {
        $couple = User::factory()->couple()->create(['name' => 'Helena & Mateus']);
        $wedding = Wedding::factory()->create([
            'user_id' => $couple->id,
            'partner_1_name' => 'Helena',
            'partner_2_name' => 'Mateus',
            'city' => 'Florianópolis',
            'state' => 'SC',
        ]);

        $cerimonialista = WeddingVendor::factory()->create([
            'wedding_id' => $wedding->id,
            'vendor_name' => 'Ateliê Cerimonial',
            'category' => 'cerimonialista',
            'contact_email' => 'Cerimonial@Exemplo.com',
        ]);

        $this->actingAs($couple)
            ->get('/painel')
            ->assertOk()
            ->assertSee('Visão consolidada');

        $cerimonialistaUser = User::factory()->vendor()->create([
            'name' => 'Ateliê Cerimonial',
            'email' => 'cerimonial@exemplo.com',
        ]);

        $this->actingAs($cerimonialistaUser)
            ->get('/fornecedor/painel')
            ->assertOk()
            ->assertSee('Casamentos Vinculados à Sua Conta')
            ->assertSee('Helena & Mateus')
            ->assertSee('Abrir Painel Consolidado')
            ->assertSee(route('vendor.wedding.access', $cerimonialista->invite_token));

        $this->actingAs($cerimonialistaUser)
            ->get(route('vendor.wedding.access', $cerimonialista->invite_token))
            ->assertOk()
            ->assertSee('Visão Consolidada do Cerimonialista')
            ->assertSee('Helena & Mateus');

        $otherVendorUser = User::factory()->vendor()->create([
            'email' => 'outro@exemplo.com',
        ]);

        $this->actingAs($otherVendorUser)
            ->get('/fornecedor/painel')
            ->assertOk()
            ->assertDontSee('Helena & Mateus');
    }

    public function test_phone_helpers_and_collaborative_form_validation_handle_edge_cases(): void
    {
        $blankPhone = new WeddingVendor(['contact_phone' => null]);
        $this->assertSame('', $blankPhone->phoneDigits());
        $this->assertNull($blankPhone->phoneHref());
        $this->assertNull($blankPhone->whatsappUrl());

        $shortPhone = new WeddingVendor(['contact_phone' => '123456']);
        $this->assertSame('123456', $shortPhone->phoneDigits());
        $this->assertNull($shortPhone->phoneHref());
        $this->assertNull($shortPhone->whatsappUrl());

        $eightDigitPhone = new WeddingVendor(['contact_phone' => '3333-4444']);
        $this->assertSame('tel:33334444', $eightDigitPhone->phoneHref());
        $this->assertNull($eightDigitPhone->whatsappUrl());

        $ddiPhone = new WeddingVendor(['contact_phone' => '+55 (11) 99999-8888']);
        $this->assertSame('tel:+5511999998888', $ddiPhone->phoneHref());
        $this->assertSame('https://wa.me/5511999998888', $ddiPhone->whatsappUrl());

        $wedding = Wedding::factory()->create();
        $cerimonialista = WeddingVendor::factory()->create([
            'wedding_id' => $wedding->id,
            'category' => 'cerimonialista',
        ]);

        Livewire::test(VendorWeddingAccess::class, ['token' => $cerimonialista->invite_token])
            ->call('openNewVendorModal')
            ->set('vendor_name', '')
            ->set('contact_email', 'email-invalido')
            ->set('contracted_amount', '-10.00')
            ->call('saveVendor')
            ->assertHasErrors(['vendor_name', 'contact_email', 'contracted_amount'])
            ->call('closeVendorModal')
            ->assertSet('showVendorModal', false);

        Livewire::test(\App\Livewire\Vendor\VendorWeddingAccess::class, ['token' => $cerimonialista->invite_token])
            ->assertSee($wedding->coupleTitle());

        Livewire::test(\App\Livewire\Dashboard\VendorWeddingAccess::class, ['token' => $cerimonialista->invite_token])
            ->assertSee($wedding->coupleTitle());
    }
}
