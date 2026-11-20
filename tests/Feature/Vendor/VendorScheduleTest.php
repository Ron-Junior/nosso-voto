<?php

namespace Tests\Feature\Vendor;

use App\Livewire\Dashboard\UserPanel;
use App\Livewire\Vendor\VendorDashboard;
use App\Livewire\Vendor\VendorSchedule;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\Wedding;
use App\Models\WeddingVendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VendorScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_create_and_edit_vendor_profile_with_unique_slug_and_region_validation(): void
    {
        $couple = User::factory()->couple()->create();
        $this->actingAs($couple)
            ->get(route('vendor.schedule'))
            ->assertForbidden();

        $vendorUser = User::factory()->vendor()->create(['name' => 'Ateliê Luz & Arte']);
        $this->actingAs($vendorUser);

        $this->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertSee('Token de Integração')
            ->assertSee('Casamentos Vinculados à Sua Conta')
            ->assertSee('Abrir Agenda de Noivos')
            ->assertDontSee('Eventos Passados (')
            ->assertDontSeeHtml('id="schedule_search"')
            ->assertDontSee('100% Gratuito')
            ->assertDontSee('Sem mensalidades');

        $this->get(route('vendor.schedule'))
            ->assertOk()
            ->assertSee('Agenda de Próximos Noivos')
            ->assertSee('+ Novo Casamento na Agenda')
            ->assertDontSee('Token de Integração')
            ->assertDontSee('Casamentos Vinculados à Sua Conta')
            ->assertDontSee('Destaque Regional')
            ->assertDontSee('100% Gratuito')
            ->assertDontSee('Sem mensalidades');

        $component = Livewire::test(UserPanel::class)
            ->call('openProfileModal')
            ->assertSet('showProfileModal', true)
            ->set('business_name', 'Ateliê Luz & Arte')
            ->set('profile_category', 'fotografia')
            ->set('state', 'SP')
            ->set('city', 'São Paulo')
            ->set('state', 'mg')
            ->assertSet('city', '')
            ->set('city', 'Campos do Jordão')
            ->set('whatsapp', '(31) 99876-5432')
            ->call('saveVendorProfile')
            ->assertHasErrors(['city']);

        $component
            ->set('city', 'Belo Horizonte')
            ->set('instagram', '@atelieluzarte')
            ->set('price_from', '4200.00')
            ->set('description', 'Fotografia documental e afetiva para casamentos.')
            ->call('saveVendorProfile')
            ->assertHasNoErrors()
            ->assertSet('showProfileModal', false)
            ->assertDispatched('toast', message: 'Perfil profissional criado com sucesso!')
            ->assertSeeHtml('data-toast')
            ->assertSee('Ateliê Luz & Arte')
            ->assertSee('Belo Horizonte - MG')
            ->assertSee('/vitrine/atelie-luz-arte')
            ->assertSee('R$ 4.200,00');

        $profile = $vendorUser->fresh()->vendorProfile;

        $this->assertNotNull($profile);
        $this->assertSame('atelie-luz-arte', $profile->slug);
        $this->assertSame('MG', $profile->state);
        $this->assertSame('https://wa.me/5531998765432', $profile->whatsappUrl());

        // Second vendor with identical business name gets a distinct slug
        $secondVendor = User::factory()->vendor()->create();
        $secondProfile = VendorProfile::factory()->create([
            'user_id' => $secondVendor->id,
            'business_name' => 'Ateliê Luz & Arte',
            'slug' => null,
        ]);

        $this->assertSame('atelie-luz-arte-2', $secondProfile->slug);

        // Editing existing profile via UserPanel/VendorDashboard alias works
        $this->actingAs($vendorUser);
        Livewire::test(VendorDashboard::class)
            ->call('openProfileModal')
            ->set('business_name', 'Ateliê Luz & Arte Studio')
            ->set('price_from', '4800.00')
            ->call('saveProfile')
            ->assertHasNoErrors()
            ->assertDispatched('toast', message: 'Perfil profissional atualizado com sucesso.')
            ->assertSee('Ateliê Luz & Arte Studio')
            ->assertSee('R$ 4.800,00');

        $this->assertSame('Ateliê Luz & Arte Studio', $profile->fresh()->business_name);
    }

    public function test_vendor_can_autonomously_create_schedule_entry_without_any_wedding_record(): void
    {
        $vendorUser = User::factory()->vendor()->create(['name' => 'Buffet Horizonte']);
        $this->actingAs($vendorUser);

        $eventDate = now()->addDays(18)->toDateString();

        Livewire::test(VendorSchedule::class)
            ->call('openNewScheduleModal')
            ->assertSet('showScheduleModal', true)
            ->set('couple_names', 'Ana & Lucas')
            ->set('event_date', $eventDate)
            ->set('event_city', 'São Paulo - SP')
            ->set('event_venue', 'Fazenda Vila Rica')
            ->set('contact_name', 'Ana Paula')
            ->set('contact_phone', '(11) 99123-4567')
            ->set('contact_email', 'ana@exemplo.com.br')
            ->set('contracted_amount', '8500.00')
            ->set('paid_amount', '2500.00')
            ->set('status', WeddingVendor::STATUS_CONTRACTED)
            ->set('notes', 'Serviço completo para 150 convidados.')
            ->call('saveScheduleEntry')
            ->assertHasNoErrors()
            ->assertSet('showScheduleModal', false)
            ->assertDispatched('toast', message: 'Casamento adicionado à agenda com sucesso.')
            ->assertSee('Em 18 dias • Ana & Lucas • São Paulo - SP')
            ->assertSee('Cadastro Manual')
            ->assertSee('Fazenda Vila Rica')
            ->assertSee('https://wa.me/5511991234567')
            ->assertSee('R$ 8.500,00')
            ->assertSee('R$ 2.500,00')
            ->assertSee('R$ 6.000,00')
            ->assertDontSee('100% Gratuito')
            ->assertDontSee('Sem mensalidades');

        $this->assertDatabaseCount('weddings', 0);

        $profile = $vendorUser->fresh()->vendorProfile;
        $this->assertNotNull($profile);

        $entry = WeddingVendor::query()->where('vendor_profile_id', $profile->id)->first();
        $this->assertNotNull($entry);
        $this->assertNull($entry->wedding_id);
        $this->assertSame(WeddingVendor::SIDE_VENDOR, $entry->created_by_side);
        $this->assertSame('Ana & Lucas', $entry->couple_names);
        $this->assertGreaterThanOrEqual(40, strlen($entry->invite_token));

        $originalToken = $entry->invite_token;

        Livewire::test(VendorSchedule::class)
            ->assertSee($originalToken)
            ->call('notifyInviteCopied', $entry->resolvedCoupleNames())
            ->assertDispatched('toast', message: 'Código de vínculo de Ana & Lucas copiado para a área de transferência.')
            ->call('regenerateEntryInviteToken', $entry->id)
            ->assertDispatched('toast', message: 'Novo código de vínculo gerado para Ana & Lucas.');

        $entry->refresh();
        $this->assertNotSame($originalToken, $entry->invite_token);
        $this->assertGreaterThanOrEqual(40, strlen($entry->invite_token));

        // Editing and deleting the manual entry works with zero couple users and zero weddings in DB
        $this->assertSame(0, User::query()->where('role', User::ROLE_COUPLE)->count());
        $this->assertDatabaseCount('weddings', 0);

        Livewire::test(VendorSchedule::class)
            ->call('editScheduleEntry', $entry->id)
            ->set('paid_amount', '8500.00')
            ->set('status', WeddingVendor::STATUS_PAID)
            ->set('notes', 'Contrato integralmente quitado.')
            ->call('saveScheduleEntry')
            ->assertHasNoErrors()
            ->assertSee('Contrato integralmente quitado.')
            ->call('deleteScheduleEntry', $entry->id)
            ->assertDispatched('toast', message: 'Casamento removido da sua agenda.')
            ->assertDontSee('Ana & Lucas');

        $this->assertDatabaseMissing('wedding_vendors', ['id' => $entry->id]);
    }

    public function test_unified_chronological_agenda_orders_platform_and_manual_weddings_and_supports_filters(): void
    {
        $vendorUser = User::factory()->vendor()->create(['name' => 'Decoradora Flor de Lis']);
        $profile = VendorProfile::factory()->create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Decoradora Flor de Lis',
            'category' => 'decoracao',
        ]);

        // Platform couple in 10 days (date comes from weddings.wedding_date via COALESCE)
        $weddingUpcoming = Wedding::factory()->create([
            'partner_1_name' => 'Beatriz',
            'partner_2_name' => 'Henrique',
            'wedding_date' => now()->addDays(10)->toDateString(),
            'city' => 'Curitiba',
            'state' => 'PR',
            'venue_name' => 'Castelo do Batel',
        ]);
        WeddingVendor::factory()->create([
            'wedding_id' => $weddingUpcoming->id,
            'vendor_profile_id' => $profile->id,
            'created_by_side' => WeddingVendor::SIDE_COUPLE,
            'vendor_name' => $profile->business_name,
            'category' => 'decoracao',
            'couple_names' => null,
            'event_date' => null,
            'event_city' => null,
            'contracted_amount' => 12000.00,
            'paid_amount' => 6000.00,
            'status' => WeddingVendor::STATUS_CONTRACTED,
        ]);

        // Manual couple in 5 days (closest upcoming)
        WeddingVendor::factory()->autonomousForVendor($profile)->create([
            'couple_names' => 'Camila & Bruno',
            'event_date' => now()->addDays(5)->toDateString(),
            'event_city' => 'Campinas - SP',
            'status' => WeddingVendor::STATUS_QUOTING,
            'contracted_amount' => 5000.00,
            'paid_amount' => 0.00,
        ]);

        // Manual couple in 25 days (furthest upcoming)
        WeddingVendor::factory()->autonomousForVendor($profile)->create([
            'couple_names' => 'Laura & Tiago',
            'event_date' => now()->addDays(25)->toDateString(),
            'event_city' => 'Florianópolis - SC',
            'status' => WeddingVendor::STATUS_PAID,
            'contracted_amount' => 9000.00,
            'paid_amount' => 9000.00,
        ]);

        // Past manual event (12 days ago)
        WeddingVendor::factory()->autonomousForVendor($profile)->create([
            'couple_names' => 'Juliana & Marcos',
            'event_date' => now()->subDays(12)->toDateString(),
            'event_city' => 'Santos - SP',
            'status' => WeddingVendor::STATUS_PAID,
            'contracted_amount' => 7000.00,
            'paid_amount' => 7000.00,
        ]);

        // Past platform event (30 days ago)
        $weddingPast = Wedding::factory()->create([
            'partner_1_name' => 'Patrícia',
            'partner_2_name' => 'André',
            'wedding_date' => now()->subDays(30)->toDateString(),
            'city' => 'Gramado',
            'state' => 'RS',
        ]);
        WeddingVendor::factory()->create([
            'wedding_id' => $weddingPast->id,
            'vendor_profile_id' => $profile->id,
            'created_by_side' => WeddingVendor::SIDE_COUPLE,
            'vendor_name' => $profile->business_name,
            'category' => 'decoracao',
            'couple_names' => null,
            'event_date' => null,
            'contracted_amount' => 8000.00,
            'paid_amount' => 8000.00,
            'status' => WeddingVendor::STATUS_PAID,
        ]);

        $this->actingAs($vendorUser);

        $component = Livewire::test(VendorSchedule::class)
            ->assertSee('Próximos Casamentos (3)')
            ->assertSee('Eventos Passados (2)')
            ->assertSee('Todos (5)')
            ->assertSee('Casal na Plataforma')
            ->assertSee('Cadastro Manual')
            ->assertSeeHtmlInOrder([
                'Em 5 dias • Camila &amp; Bruno • Campinas - SP',
                'Em 10 dias • Beatriz &amp; Henrique • Curitiba - PR',
                'Em 25 dias • Laura &amp; Tiago • Florianópolis - SC',
            ])
            ->assertDontSee('Juliana & Marcos')
            ->assertDontSee('Patrícia & André');

        // Past events tab shows past weddings newest-first
        $component
            ->call('setPeriodFilter', 'past')
            ->assertSeeHtmlInOrder([
                'Há 12 dias • Juliana &amp; Marcos • Santos - SP',
                'Há 30 dias • Patrícia &amp; André • Gramado - RS',
            ])
            ->assertDontSee('Camila & Bruno')
            ->assertDontSee('Beatriz & Henrique');

        // All events tab + status filter + case-insensitive search across platform & manual couples
        $component
            ->call('setPeriodFilter', 'all')
            ->assertSeeHtmlInOrder([
                'Há 30 dias • Patrícia &amp; André • Gramado - RS',
                'Há 12 dias • Juliana &amp; Marcos • Santos - SP',
                'Em 5 dias • Camila &amp; Bruno • Campinas - SP',
                'Em 10 dias • Beatriz &amp; Henrique • Curitiba - PR',
                'Em 25 dias • Laura &amp; Tiago • Florianópolis - SC',
            ])
            ->set('statusFilter', WeddingVendor::STATUS_QUOTING)
            ->assertSee('Camila & Bruno')
            ->assertDontSee('Laura & Tiago')
            ->set('statusFilter', 'all')
            ->set('search', 'beatriz & henrique')
            ->assertSee('Beatriz & Henrique')
            ->assertDontSee('Camila & Bruno')
            ->set('search', 'campinas')
            ->assertSee('Camila & Bruno')
            ->assertDontSee('Beatriz & Henrique');
    }

    public function test_vendor_can_update_received_amounts_status_notes_and_delete_entries_with_isolation(): void
    {
        $vendorUserA = User::factory()->vendor()->create();
        $profileA = VendorProfile::factory()->create(['user_id' => $vendorUserA->id]);

        $manualEntry = WeddingVendor::factory()->autonomousForVendor($profileA)->create([
            'couple_names' => 'Marina & Felipe',
            'event_date' => now()->addDays(20)->toDateString(),
            'contracted_amount' => 10000.00,
            'paid_amount' => 3000.00,
            'status' => WeddingVendor::STATUS_CONTRACTED,
            'notes' => 'Entrada de 30% paga.',
        ]);

        $platformCouple = User::factory()->couple()->create();
        $platformWedding = Wedding::factory()->create([
            'user_id' => $platformCouple->id,
            'partner_1_name' => 'Clara',
            'partner_2_name' => 'Rodrigo',
            'wedding_date' => now()->addDays(40)->toDateString(),
        ]);
        $platformEntry = WeddingVendor::factory()->create([
            'wedding_id' => $platformWedding->id,
            'vendor_profile_id' => $profileA->id,
            'created_by_side' => WeddingVendor::SIDE_COUPLE,
            'vendor_name' => $profileA->business_name,
            'estimated_amount' => 15000.00,
            'contracted_amount' => 12000.00,
            'paid_amount' => 4000.00,
            'status' => WeddingVendor::STATUS_CONTRACTED,
        ]);

        $this->actingAs($vendorUserA);

        // Update paid_amount, status, and notes on manual entry
        Livewire::test(VendorSchedule::class)
            ->call('editScheduleEntry', $manualEntry->id)
            ->assertSet('showScheduleModal', true)
            ->assertSet('editingEntryId', $manualEntry->id)
            ->set('paid_amount', '10000.00')
            ->set('status', WeddingVendor::STATUS_PAID)
            ->set('notes', 'Saldo final quitado via Pix.')
            ->call('saveScheduleEntry')
            ->assertHasNoErrors()
            ->assertSet('showScheduleModal', false)
            ->assertDispatched('toast', message: 'Casamento na agenda atualizado com sucesso.')
            ->assertSee('Saldo final quitado via Pix.')
            ->assertSee('Quitado');

        $manualEntry->refresh();
        $this->assertSame('10000.00', (string) $manualEntry->paid_amount);
        $this->assertSame(WeddingVendor::STATUS_PAID, $manualEntry->status);
        $this->assertSame('Saldo final quitado via Pix.', $manualEntry->notes);

        // Quick status update
        Livewire::test(VendorSchedule::class)
            ->call('updateScheduleStatus', $platformEntry->id, WeddingVendor::STATUS_PAID)
            ->assertDispatched('toast', message: 'Status de Clara & Rodrigo atualizado para Quitado.');

        // Edit platform entry preserves couple's private estimated_amount
        Livewire::test(VendorSchedule::class)
            ->call('editScheduleEntry', $platformEntry->id)
            ->set('paid_amount', '12000.00')
            ->set('notes', 'Pagamento integral confirmado.')
            ->call('saveScheduleEntry')
            ->assertHasNoErrors();

        $platformEntry->refresh();
        $this->assertSame('15000.00', (string) $platformEntry->estimated_amount);
        $this->assertSame('12000.00', (string) $platformEntry->paid_amount);
        $this->assertSame('Pagamento integral confirmado.', $platformEntry->notes);

        // Cross-vendor isolation: Vendor B cannot mutate or delete Vendor A's entries
        $vendorUserB = User::factory()->vendor()->create();
        VendorProfile::factory()->create(['user_id' => $vendorUserB->id]);
        $this->actingAs($vendorUserB);

        Livewire::test(VendorSchedule::class)
            ->call('editScheduleEntry', $manualEntry->id)
            ->assertNotFound();

        Livewire::test(VendorSchedule::class)
            ->call('updateScheduleStatus', $manualEntry->id, WeddingVendor::STATUS_QUOTING)
            ->assertNotFound();

        Livewire::test(VendorSchedule::class)
            ->call('deleteScheduleEntry', $manualEntry->id)
            ->assertNotFound();

        Livewire::test(VendorSchedule::class)
            ->call('regenerateEntryInviteToken', $manualEntry->id)
            ->assertNotFound();

        // Vendor A deletes manual entry (without requiring any couple user or Wedding)
        $this->actingAs($vendorUserA);
        Livewire::test(VendorSchedule::class)
            ->call('deleteScheduleEntry', $manualEntry->id)
            ->assertDispatched('toast', message: 'Casamento removido da sua agenda.')
            ->assertDontSee('Marina & Felipe');

        $this->assertDatabaseMissing('wedding_vendors', ['id' => $manualEntry->id]);

        // Unlinking a couple-created entry clears vendor_profile_id while preserving the couple's record
        Livewire::test(VendorSchedule::class)
            ->call('deleteScheduleEntry', $platformEntry->id)
            ->assertDispatched('toast', message: 'Casamento desvinculado da sua agenda.')
            ->assertDontSee('Clara & Rodrigo');

        $this->assertDatabaseHas('wedding_vendors', [
            'id' => $platformEntry->id,
            'wedding_id' => $platformWedding->id,
            'vendor_profile_id' => null,
        ]);
    }

    public function test_vendor_entry_supports_future_link_to_platform_wedding_and_email_quick_link(): void
    {
        $vendorUser = User::factory()->vendor()->create([
            'name' => 'Fotografia Aurora',
            'email' => 'contato@aurora.com.br',
        ]);
        $profile = VendorProfile::factory()->create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Fotografia Aurora',
            'category' => 'fotografia',
        ]);

        // Manual entry created before the couple joins the platform
        $manualEntry = WeddingVendor::factory()->autonomousForVendor($profile)->create([
            'couple_names' => 'Fernanda & Gabriel',
            'event_date' => null,
            'event_city' => null,
            'event_venue' => null,
            'contracted_amount' => 7800.00,
            'paid_amount' => 2000.00,
            'status' => WeddingVendor::STATUS_CONTRACTED,
            'notes' => 'Cobertura completa da cerimônia e recepção.',
        ]);

        $wedding = Wedding::factory()->create([
            'partner_1_name' => 'Fernanda',
            'partner_2_name' => 'Gabriel',
            'wedding_date' => now()->addDays(60)->toDateString(),
            'city' => 'Florianópolis',
            'state' => 'SC',
            'venue_name' => 'Casa da Praia',
            'estimated_budget' => 60000.00,
        ]);

        $manualEntry->linkToWedding($wedding);
        $manualEntry->refresh();

        $this->assertSame($wedding->id, $manualEntry->wedding_id);
        $this->assertTrue($manualEntry->isPlatformCouple());
        $this->assertSame('7800.00', (string) $manualEntry->contracted_amount);
        $this->assertSame('2000.00', (string) $manualEntry->paid_amount);
        $this->assertSame('Cobertura completa da cerimônia e recepção.', $manualEntry->notes);
        $this->assertSame('Florianópolis - SC', $manualEntry->resolvedEventCity());
        $this->assertSame('Casa da Praia', $manualEntry->resolvedEventVenue());

        // Email-matched wedding from a platform couple can be added to the vendor's agenda with 1 click
        $emailWedding = Wedding::factory()->create([
            'partner_1_name' => 'Renata',
            'partner_2_name' => 'Eduardo',
            'wedding_date' => now()->addDays(35)->toDateString(),
            'city' => 'Porto Alegre',
            'state' => 'RS',
        ]);
        $emailVendorRecord = WeddingVendor::factory()->create([
            'wedding_id' => $emailWedding->id,
            'vendor_profile_id' => null,
            'created_by_side' => WeddingVendor::SIDE_COUPLE,
            'vendor_name' => 'Fotografia Aurora',
            'contact_email' => 'CONTATO@aurora.com.br',
            'contracted_amount' => 9500.00,
            'paid_amount' => 3000.00,
            'status' => WeddingVendor::STATUS_CONTRACTED,
        ]);

        $this->actingAs($vendorUser);

        Livewire::test(UserPanel::class)
            ->assertSee('Adicionar à Agenda')
            ->call('linkEmailWeddingToAgenda', $emailVendorRecord->id)
            ->assertDispatched('toast', message: 'Casamento de Renata & Eduardo adicionado à sua agenda.')
            ->assertSee('Na sua agenda');

        Livewire::test(VendorSchedule::class)
            ->assertSee('Em 35 dias • Renata & Eduardo • Porto Alegre - RS');

        $this->assertSame($profile->id, $emailVendorRecord->fresh()->vendor_profile_id);
    }

    public function test_schedule_validation_and_countdown_edge_cases(): void
    {
        $vendorUser = User::factory()->vendor()->create();
        $profile = VendorProfile::factory()->create([
            'user_id' => $vendorUser->id,
            'is_regional_highlight' => true,
            'highlight_expires_at' => now()->addDays(10),
        ]);

        $this->assertTrue($profile->hasActiveRegionalHighlight());
        $profile->update(['highlight_expires_at' => now()->subDay()]);
        $this->assertFalse($profile->fresh()->hasActiveRegionalHighlight());

        $todayEntry = WeddingVendor::factory()->autonomousForVendor($profile)->make([
            'couple_names' => 'Hoje & Sempre',
            'event_date' => now()->toDateString(),
            'event_city' => 'Curitiba - PR',
        ]);
        $this->assertSame('Hoje • Hoje & Sempre • Curitiba - PR', $todayEntry->timelineHeadline());

        $tomorrowEntry = WeddingVendor::factory()->autonomousForVendor($profile)->make([
            'couple_names' => 'Aline & Bruno',
            'event_date' => now()->addDay()->toDateString(),
            'event_city' => null,
        ]);
        $this->assertSame('Em 1 dia • Aline & Bruno', $tomorrowEntry->timelineHeadline());

        $yesterdayEntry = WeddingVendor::factory()->autonomousForVendor($profile)->make([
            'couple_names' => 'Lara & João',
            'event_date' => now()->subDay()->toDateString(),
            'event_city' => null,
        ]);
        $this->assertSame('Há 1 dia • Lara & João', $yesterdayEntry->timelineHeadline());

        $tbdEntry = WeddingVendor::factory()->autonomousForVendor($profile)->make([
            'couple_names' => null,
            'event_date' => null,
            'event_city' => null,
        ]);
        $this->assertSame('Data a definir • Casal a definir', $tbdEntry->timelineHeadline());

        $this->actingAs($vendorUser);

        Livewire::test(VendorSchedule::class)
            ->call('openNewScheduleModal')
            ->set('couple_names', '')
            ->set('contact_email', 'email-invalido')
            ->set('contracted_amount', '-50.00')
            ->set('paid_amount', '-10.00')
            ->call('saveScheduleEntry')
            ->assertHasErrors(['couple_names', 'contact_email', 'contracted_amount', 'paid_amount'])
            ->call('closeScheduleModal')
            ->assertSet('showScheduleModal', false);
    }
}
