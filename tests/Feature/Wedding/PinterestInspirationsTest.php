<?php

namespace Tests\Feature\Wedding;

use App\Livewire\Admin\PlatformSettings;
use App\Livewire\Wedding\PinterestBoard;
use App\Livewire\Wedding\PwaShareTarget;
use App\Livewire\Wedding\VendorWeddingAccess;
use App\Models\PinterestInspiration;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingVendor;
use App\Services\PinterestMetadataResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PinterestInspirationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_couple_can_save_filter_edit_and_delete_inspirations_across_all_categories_on_web_panel(): void
    {
        $couple = User::factory()->couple()->create(['name' => 'Clara & Rafael']);
        $wedding = Wedding::factory()->create(['user_id' => $couple->id]);

        $vendorUser = User::factory()->vendor()->create();
        $this->actingAs($vendorUser)->get('/inspiracoes')->assertForbidden();
        auth()->logout();

        $this->get('/inspiracoes')->assertRedirect('/login');

        $this->actingAs($couple);

        $this->get('/inspiracoes')
            ->assertOk()
            ->assertSee('Inspirações do Pinterest')
            ->assertSee($couple->api_token)
            ->assertSee('chrome-extension/')
            ->assertDontSee('100% Gratuito')
            ->assertDontSee('Sem mensalidades');

        $component = Livewire::test(PinterestBoard::class)
            ->set('category', PinterestInspiration::CATEGORY_DECORACAO)
            ->set('type', PinterestInspiration::TYPE_BOARD)
            ->set('title', 'Pasta Arco Orgânico Terracota')
            ->set('pinterest_url', 'https://br.pinterest.com/clara/arco-terracota/')
            ->set('bride_notes', 'Usar flores secas e vasos em cerâmica.')
            ->call('saveInspiration')
            ->assertHasNoErrors()
            ->assertDispatched('toast', message: 'Inspiração salva no moodboard com sucesso.')
            ->assertSeeHtml('data-toast')
            ->set('category', PinterestInspiration::CATEGORY_MAQUIAGEM)
            ->set('type', PinterestInspiration::TYPE_PIN)
            ->set('title', 'Make Iluminada Rosê')
            ->set('pinterest_url', 'https://br.pinterest.com/pin/111222333/')
            ->set('bride_notes', 'Olhos esfumados suaves.')
            ->call('saveInspiration')
            ->assertHasNoErrors()
            ->set('category', PinterestInspiration::CATEGORY_PENTEADO)
            ->set('type', PinterestInspiration::TYPE_PIN)
            ->set('title', 'Coque Desconstruído')
            ->set('pinterest_url', 'https://br.pinterest.com/pin/444555666/')
            ->set('bride_notes', 'Fios soltos na frente.')
            ->call('saveInspiration')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('pinterest_inspirations', 3);
        $this->assertDatabaseHas('pinterest_inspirations', [
            'wedding_id' => $wedding->id,
            'category' => PinterestInspiration::CATEGORY_DECORACAO,
            'type' => PinterestInspiration::TYPE_BOARD,
            'source' => PinterestInspiration::SOURCE_WEB,
        ]);

        // Filter by category tabs
        $component
            ->call('selectCategory', PinterestInspiration::CATEGORY_MAQUIAGEM)
            ->assertSee('Make Iluminada Rosê')
            ->assertDontSee('Pasta Arco Orgânico Terracota')
            ->assertDontSee('Coque Desconstruído')
            ->call('selectCategory', 'all')
            ->assertSee('Pasta Arco Orgânico Terracota')
            ->assertSee('Make Iluminada Rosê')
            ->assertSee('Coque Desconstruído');

        // Edit and delete
        $makeupPin = $wedding->pinterestInspirations()
            ->where('category', PinterestInspiration::CATEGORY_MAQUIAGEM)
            ->firstOrFail();

        $component
            ->call('editInspiration', $makeupPin->id)
            ->assertSet('editingInspirationId', $makeupPin->id)
            ->set('title', 'Make Glow Editorial')
            ->call('saveInspiration')
            ->assertHasNoErrors()
            ->assertDispatched('toast', message: 'Inspiração atualizada com sucesso.')
            ->assertSee('Make Glow Editorial')
            ->call('deleteInspiration', $makeupPin->id)
            ->assertDispatched('toast', message: 'Inspiração removida do moodboard.')
            ->assertDontSee('Make Glow Editorial');

        $this->assertDatabaseMissing('pinterest_inspirations', ['id' => $makeupPin->id]);

        // Cross-couple isolation
        $otherCouple = User::factory()->couple()->create();
        Wedding::factory()->create(['user_id' => $otherCouple->id]);
        $decoBoard = $wedding->pinterestInspirations()->firstOrFail();

        $this->actingAs($otherCouple);

        Livewire::test(PinterestBoard::class)
            ->call('editInspiration', $decoBoard->id)
            ->assertNotFound();

        Livewire::test(PinterestBoard::class)
            ->call('deleteInspiration', $decoBoard->id)
            ->assertNotFound();
    }

    public function test_api_clip_endpoint_authenticates_via_api_token_enforces_cors_and_saves_chrome_extension_pins(): void
    {
        $couple = User::factory()->couple()->create(['name' => 'Lívia & Thiago']);
        $vendor = User::factory()->vendor()->create();

        // CORS preflight OPTIONS
        $this->options('/api/v1/inspirations/clip')
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');

        // Unauthenticated requests fail with 401 + CORS headers
        $this->postJson('/api/v1/inspirations/clip', [
            'category' => PinterestInspiration::CATEGORY_DECORACAO,
            'pinterest_url' => 'https://br.pinterest.com/pin/99887766/',
        ])->assertUnauthorized()
            ->assertHeader('Access-Control-Allow-Origin', '*');

        $this->postJson('/api/v1/inspirations/clip', [
            'category' => PinterestInspiration::CATEGORY_DECORACAO,
            'pinterest_url' => 'https://br.pinterest.com/pin/99887766/',
        ], [
            'Authorization' => 'Bearer token-invalido',
        ])->assertUnauthorized()
            ->assertHeader('Access-Control-Allow-Origin', '*');

        // Vendor token is forbidden
        $this->postJson('/api/v1/inspirations/clip', [
            'category' => PinterestInspiration::CATEGORY_DECORACAO,
            'pinterest_url' => 'https://br.pinterest.com/pin/99887766/',
        ], [
            'X-Api-Token' => $vendor->api_token,
        ])->assertForbidden()
            ->assertHeader('Access-Control-Allow-Origin', '*');

        // Couple saves Pin using Authorization: Bearer
        $response = $this->postJson('/api/v1/inspirations/clip', [
            'category' => PinterestInspiration::CATEGORY_MAQUIAGEM,
            'title' => 'Delineado Gatinho Clássico',
            'pinterest_url' => 'https://www.pinterest.com/pin/1234567890/',
            'image_url' => 'https://i.pinimg.com/736x/aa/bb/cc/123456.jpg',
            'bride_notes' => 'Batom terracota matte.',
        ], [
            'Authorization' => 'Bearer '.$couple->api_token,
        ]);

        $response->assertCreated()
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertJsonPath('data.category', PinterestInspiration::CATEGORY_MAQUIAGEM)
            ->assertJsonPath('data.type', PinterestInspiration::TYPE_PIN)
            ->assertJsonPath('data.source', PinterestInspiration::SOURCE_CHROME_EXTENSION);

        // Couple saves Board using X-Api-Token without explicit type (auto-inferred)
        $boardResponse = $this->postJson('/api/v1/inspirations/clip', [
            'category' => PinterestInspiration::CATEGORY_DECORACAO,
            'pinterest_url' => 'https://br.pinterest.com/liviathiago/mesas-rusticas/',
        ], [
            'X-Api-Token' => $couple->api_token,
        ]);

        $boardResponse->assertCreated()
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertJsonPath('data.type', PinterestInspiration::TYPE_BOARD)
            ->assertJsonPath('data.source', PinterestInspiration::SOURCE_CHROME_EXTENSION);

        $wedding = $couple->fresh()->wedding;
        $this->assertNotNull($wedding);
        $this->assertDatabaseHas('pinterest_inspirations', [
            'wedding_id' => $wedding->id,
            'category' => PinterestInspiration::CATEGORY_MAQUIAGEM,
            'pinterest_url' => 'https://www.pinterest.com/pin/1234567890/',
            'source' => PinterestInspiration::SOURCE_CHROME_EXTENSION,
        ]);
        $this->assertDatabaseHas('pinterest_inspirations', [
            'wedding_id' => $wedding->id,
            'category' => PinterestInspiration::CATEGORY_DECORACAO,
            'type' => PinterestInspiration::TYPE_BOARD,
            'source' => PinterestInspiration::SOURCE_CHROME_EXTENSION,
        ]);

        // Validation failure returns 422 + CORS headers
        $this->postJson('/api/v1/inspirations/clip', [
            'category' => 'buffet',
            'pinterest_url' => 'url-invalida',
        ], [
            'Authorization' => 'Bearer '.$couple->api_token,
        ])->assertUnprocessable()
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertJsonValidationErrors(['category', 'pinterest_url']);
    }

    public function test_chrome_extension_mv3_files_exist_with_valid_manifest_and_clip_integration(): void
    {
        $extensionDir = base_path('chrome-extension');

        $this->assertFileExists($extensionDir.'/manifest.json');
        $this->assertFileExists($extensionDir.'/content.js');
        $this->assertFileExists($extensionDir.'/content.css');
        $this->assertFileExists($extensionDir.'/popup.html');
        $this->assertFileExists($extensionDir.'/popup.js');

        $manifest = json_decode((string) file_get_contents($extensionDir.'/manifest.json'), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(3, $manifest['manifest_version']);
        $this->assertContains('storage', $manifest['permissions']);
        $this->assertSame('popup.html', $manifest['action']['default_popup']);
        $this->assertContains('*://*.pinterest.com/*', $manifest['content_scripts'][0]['matches']);
        $this->assertContains('*://*.pinterest.com.br/*', $manifest['content_scripts'][0]['matches']);
        $this->assertContains('content.js', $manifest['content_scripts'][0]['js']);
        $this->assertContains('content.css', $manifest['content_scripts'][0]['css']);

        $contentJs = (string) file_get_contents($extensionDir.'/content.js');
        $popupJs = (string) file_get_contents($extensionDir.'/popup.js');

        $this->assertStringContainsString('Salvar no Nosso Voto', $contentJs);
        $this->assertStringContainsString('/api/v1/inspirations/clip', $contentJs);
        $this->assertStringContainsString('X-Api-Token', $contentJs);
        $this->assertStringContainsString('/api/v1/inspirations/clip', $popupJs);
    }

    public function test_pwa_share_target_extracts_pinterest_url_and_saves_in_one_tap(): void
    {
        $couple = User::factory()->couple()->create(['name' => 'Camila & Bruno']);
        $wedding = Wedding::factory()->create(['user_id' => $couple->id]);

        $vendor = User::factory()->vendor()->create();
        $this->actingAs($vendor)->get('/compartilhar-pin')->assertForbidden();

        $this->actingAs($couple);

        // Mobile Pinterest app commonly puts the link inside the `text` parameter
        $this->get('/compartilhar-pin?title=Coque+Cl%C3%A1ssico&text=Olha+esse+penteado+lindo+https://pin.it/terracota99!')
            ->assertOk()
            ->assertSee('Salvar Pin do Celular')
            ->assertSee('https://pin.it/terracota99')
            ->assertDontSee('100% Gratuito');

        Livewire::withQueryParams([
            'title' => 'Coque Clássico com Pérolas',
            'text' => 'Confira esse pin no Pinterest: https://br.pinterest.com/pin/777888999/',
            'url' => '',
        ])->test(PwaShareTarget::class)
            ->assertSet('pinterest_url', 'https://br.pinterest.com/pin/777888999/')
            ->assertSet('type', PinterestInspiration::TYPE_PIN)
            ->set('bride_notes', 'Quero testar na prévia do cabelo.')
            ->call('quickSave', PinterestInspiration::CATEGORY_PENTEADO)
            ->assertHasNoErrors()
            ->assertDispatched('toast', message: 'Inspiração salva em Penteado com sucesso!');

        $this->assertDatabaseHas('pinterest_inspirations', [
            'wedding_id' => $wedding->id,
            'category' => PinterestInspiration::CATEGORY_PENTEADO,
            'type' => PinterestInspiration::TYPE_PIN,
            'title' => 'Coque Clássico com Pérolas',
            'pinterest_url' => 'https://br.pinterest.com/pin/777888999/',
            'bride_notes' => 'Quero testar na prévia do cabelo.',
            'source' => PinterestInspiration::SOURCE_PWA_SHARE,
        ]);

        // Direct board URL in `url` param
        Livewire::withQueryParams([
            'title' => 'Pasta Flores Secas',
            'url' => 'https://br.pinterest.com/camilabruno/decoracao-areia/',
        ])->test(PwaShareTarget::class)
            ->assertSet('pinterest_url', 'https://br.pinterest.com/camilabruno/decoracao-areia/')
            ->assertSet('type', PinterestInspiration::TYPE_BOARD)
            ->call('quickSave', PinterestInspiration::CATEGORY_DECORACAO)
            ->assertHasNoErrors()
            ->assertDispatched('toast', message: 'Inspiração salva em Decoração com sucesso!');

        $this->assertDatabaseHas('pinterest_inspirations', [
            'wedding_id' => $wedding->id,
            'category' => PinterestInspiration::CATEGORY_DECORACAO,
            'type' => PinterestInspiration::TYPE_BOARD,
            'source' => PinterestInspiration::SOURCE_PWA_SHARE,
        ]);
    }

    public function test_segmented_vendor_access_respects_specialty_and_admin_paywall_feature_flag(): void
    {
        $couple = User::factory()->couple()->create(['name' => 'Bianca & Felipe']);
        $wedding = Wedding::factory()->create(['user_id' => $couple->id]);

        PinterestInspiration::factory()->decoracao()->create([
            'wedding_id' => $wedding->id,
            'title' => 'Altar Minimalista Terracota',
            'pinterest_url' => 'https://br.pinterest.com/pin/100001/',
            'bride_notes' => 'Flores secas e velas baixas.',
        ]);
        PinterestInspiration::factory()->maquiagem()->create([
            'wedding_id' => $wedding->id,
            'title' => 'Pele Glow Natural',
            'pinterest_url' => 'https://br.pinterest.com/pin/200002/',
            'bride_notes' => 'Iluminador suave nas têmporas.',
        ]);
        PinterestInspiration::factory()->penteado()->create([
            'wedding_id' => $wedding->id,
            'title' => 'Semi-Preso Ondulado',
            'pinterest_url' => 'https://br.pinterest.com/pin/300003/',
            'bride_notes' => 'Acessório dourado lateral.',
        ]);

        $decorador = WeddingVendor::factory()->create([
            'wedding_id' => $wedding->id,
            'vendor_name' => 'Ateliê Botânica Decor',
            'category' => 'decoracao',
            'is_pinterest_authorized' => true,
            'has_paid_pinterest_unlock' => false,
        ]);

        $maquiadora = WeddingVendor::factory()->create([
            'wedding_id' => $wedding->id,
            'vendor_name' => 'Studio Bela Noiva Make',
            'category' => 'maquiagem',
            'is_pinterest_authorized' => true,
            'has_paid_pinterest_unlock' => false,
        ]);

        $penteadista = WeddingVendor::factory()->create([
            'wedding_id' => $wedding->id,
            'vendor_name' => 'Atelier Fios de Seda',
            'category' => 'penteado',
            'is_pinterest_authorized' => true,
            'has_paid_pinterest_unlock' => false,
        ]);

        $fotografo = WeddingVendor::factory()->create([
            'wedding_id' => $wedding->id,
            'vendor_name' => 'Olhar Eterno Fotografia',
            'category' => 'fotografia',
            'is_pinterest_authorized' => true,
            'has_paid_pinterest_unlock' => false,
        ]);

        // MVP default: paywall_pinterest_inspirations_enabled = false
        $this->assertFalse(PlatformSetting::current()->paywall_pinterest_inspirations_enabled);

        // Decorador sees only decoracao
        $this->get(route('vendor.wedding.access', $decorador->invite_token))
            ->assertOk()
            ->assertSee('Altar Minimalista Terracota')
            ->assertSee('Flores secas e velas baixas.')
            ->assertDontSee('Pele Glow Natural')
            ->assertDontSee('Semi-Preso Ondulado')
            ->assertDontSeeHtml('data-pinterest-paywall');

        // Maquiadora and Penteadista see maquiagem and penteado, never decoracao
        $this->get(route('vendor.wedding.access', $maquiadora->invite_token))
            ->assertOk()
            ->assertSee('Pele Glow Natural')
            ->assertSee('Semi-Preso Ondulado')
            ->assertDontSee('Altar Minimalista Terracota')
            ->assertDontSeeHtml('data-pinterest-paywall');

        Livewire::test(VendorWeddingAccess::class, ['token' => $penteadista->invite_token])
            ->assertSee('Pele Glow Natural')
            ->assertSee('Semi-Preso Ondulado')
            ->assertDontSee('Altar Minimalista Terracota')
            ->call('selectInspirationCategory', PinterestInspiration::CATEGORY_PENTEADO)
            ->assertSee('Semi-Preso Ondulado')
            ->assertDontSee('Pele Glow Natural');

        // Fotógrafo does not see Pinterest moodboard
        $this->get(route('vendor.wedding.access', $fotografo->invite_token))
            ->assertOk()
            ->assertDontSee('Altar Minimalista Terracota')
            ->assertDontSee('Pele Glow Natural')
            ->assertDontSee('Semi-Preso Ondulado')
            ->assertDontSee('Moodboard Segmentado');

        // Admin turns paywall ON
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(PlatformSettings::class)
            ->call('togglePinterestPaywall')
            ->assertSet('paywall_pinterest_inspirations_enabled', true);

        auth()->logout();
        $this->assertTrue(PlatformSetting::current()->fresh()->paywall_pinterest_inspirations_enabled);

        // Unpaid vendors see paywall preview with reference count, without leaking pin details
        $this->get(route('vendor.wedding.access', $decorador->invite_token))
            ->assertOk()
            ->assertSeeHtml('data-pinterest-paywall')
            ->assertSee('1 referência pronta')
            ->assertSee('Desbloquear Inspirações do Pinterest')
            ->assertDontSee('Altar Minimalista Terracota')
            ->assertDontSee('https://br.pinterest.com/pin/100001/');

        $this->get(route('vendor.wedding.access', $maquiadora->invite_token))
            ->assertOk()
            ->assertSeeHtml('data-pinterest-paywall')
            ->assertSee('2 referências prontas')
            ->assertDontSee('Pele Glow Natural')
            ->assertDontSee('Semi-Preso Ondulado');

        // Unlocking a vendor reveals the segmented moodboard
        Livewire::test(VendorWeddingAccess::class, ['token' => $decorador->invite_token])
            ->call('unlockPinterestMoodboard')
            ->assertDispatched('toast', message: 'Acesso às inspirações do Pinterest desbloqueado com sucesso.')
            ->assertSee('Altar Minimalista Terracota')
            ->assertDontSeeHtml('data-pinterest-paywall');

        $this->assertTrue($decorador->fresh()->has_paid_pinterest_unlock);
        $this->assertFalse($maquiadora->fresh()->has_paid_pinterest_unlock);

        // Turn paywall back OFF and test is_pinterest_authorized = false
        $this->actingAs($admin);
        Livewire::test(PlatformSettings::class)
            ->call('togglePinterestPaywall')
            ->assertSet('paywall_pinterest_inspirations_enabled', false);
        auth()->logout();

        $this->actingAs($couple);
        Livewire::test(PinterestBoard::class)
            ->call('toggleVendorPinterestAuthorization', $penteadista->id)
            ->assertDispatched('toast', message: 'Acesso às inspirações pausado para Atelier Fios de Seda.');
        auth()->logout();

        $this->assertFalse($penteadista->fresh()->is_pinterest_authorized);

        $this->get(route('vendor.wedding.access', $penteadista->invite_token))
            ->assertOk()
            ->assertSee('Acesso ao moodboard não autorizado no momento')
            ->assertDontSee('Semi-Preso Ondulado')
            ->assertDontSee('Pele Glow Natural');
    }

    public function test_early_inspiration_auto_provisions_wedding_and_aliases_and_filters_work(): void
    {
        $couple = User::factory()->couple()->create(['name' => 'Fernanda & Diego']);
        $this->assertNull($couple->wedding);

        $originalToken = $couple->api_token;
        $this->actingAs($couple);

        // Couple without a wedding record yet saves a board via alias component
        $component = Livewire::test(\App\Livewire\PinterestBoard::class)
            ->set('category', PinterestInspiration::CATEGORY_DECORACAO)
            ->set('type', PinterestInspiration::TYPE_BOARD)
            ->set('pinterest_url', 'https://br.pinterest.com/fernandadiego/terracota-boho/')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast', message: 'Inspiração salva no moodboard com sucesso.');

        $wedding = $couple->fresh()->wedding;
        $this->assertNotNull($wedding);
        $this->assertSame('Fernanda & Diego', $wedding->coupleTitle());
        $this->assertDatabaseHas('pinterest_inspirations', [
            'wedding_id' => $wedding->id,
            'title' => 'Pasta de Decoração',
        ]);

        // Search, type filter, token copy & token regeneration
        $component
            ->set('category', PinterestInspiration::CATEGORY_MAQUIAGEM)
            ->set('type', PinterestInspiration::TYPE_PIN)
            ->set('title', 'Batom Terracota')
            ->set('pinterest_url', 'https://br.pinterest.com/pin/555666777/')
            ->call('saveInspiration')
            ->assertHasNoErrors()
            ->set('typeFilter', PinterestInspiration::TYPE_PIN)
            ->assertSee('Batom Terracota')
            ->assertDontSee('Pasta de Decoração')
            ->set('typeFilter', 'all')
            ->set('search', 'Batom')
            ->assertSee('Batom Terracota')
            ->assertDontSee('Pasta de Decoração')
            ->set('search', '')
            ->call('notifyTokenCopied')
            ->assertDispatched('toast', message: 'Token de API copiado para a área de transferência.')
            ->call('regenerateToken')
            ->assertDispatched('toast', message: 'Novo token de integração gerado com sucesso.');

        $this->assertNotSame($originalToken, $couple->fresh()->api_token);

        // Aliases render cleanly
        Livewire::test(\App\Livewire\Dashboard\PinterestBoard::class)
            ->assertSee('Batom Terracota');
        Livewire::test(\App\Livewire\Pinterest\PinterestBoard::class)
            ->assertSee('Batom Terracota');

        // API clip supports body api_token and url/notes aliases
        $this->postJson('/api/v1/inspirations/clip', [
            'api_token' => $couple->fresh()->api_token,
            'category' => PinterestInspiration::CATEGORY_PENTEADO,
            'url' => 'https://br.pinterest.com/pin/888999000/',
            'notes' => 'Trança boho lateral.',
        ])->assertCreated()
            ->assertJsonPath('data.title', 'Pin de Penteado')
            ->assertJsonPath('data.bride_notes', 'Trança boho lateral.');

        // PWA Share Target validation & aliases
        Livewire::test(\App\Livewire\PwaShareTarget::class)
            ->call('saveInspiration')
            ->assertHasErrors(['pinterest_url']);

        Livewire::test(\App\Livewire\Dashboard\PwaShareTarget::class)
            ->assertSee('Salvar Pin do Celular');
        Livewire::test(\App\Livewire\Pinterest\PwaShareTarget::class)
            ->assertSee('Salvar Pin do Celular');
    }

    public function test_pinterest_board_renders_initial_load_within_three_queries(): void
    {
        $couple = User::factory()->couple()->create();
        $wedding = Wedding::factory()->create(['user_id' => $couple->id]);

        PinterestInspiration::factory()->decoracao()->count(2)->create([
            'wedding_id' => $wedding->id,
        ]);
        PinterestInspiration::factory()->maquiagem()->create([
            'wedding_id' => $wedding->id,
        ]);

        $this->actingAs($couple);

        DB::enableQueryLog();
        DB::flushQueryLog();

        Livewire::test(PinterestBoard::class)
            ->assertOk()
            ->assertViewHas('countsByCategory', [
                PinterestInspiration::CATEGORY_DECORACAO => 2,
                PinterestInspiration::CATEGORY_MAQUIAGEM => 1,
                PinterestInspiration::CATEGORY_PENTEADO => 0,
            ])
            ->assertViewHas('totalCount', 3);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(3, count($queries));
    }

    public function test_pwa_share_target_automatically_resolves_and_saves_image_url_from_pin_it_redirect_and_opengraph(): void
    {
        config()->set('services.pinterest.fetch_metadata', true);

        Http::fake([
            'https://pin.it/terracota42' => Http::response('', 302, [
                'Location' => 'https://br.pinterest.com/pin/999111222/sent/?invite_code=xyz',
            ]),
            'https://br.pinterest.com/pin/999111222/sent*' => Http::response(
                '<!doctype html><html><head>'
                .'<meta property="og:title" content="Arco Floral Terracota | Pinterest">'
                .'<meta content="https://i.pinimg.com/736x/ab/cd/ef/decor-terracota.jpg" property="og:image">'
                .'</head><body></body></html>',
                200,
            ),
        ]);

        $couple = User::factory()->couple()->create();
        $wedding = Wedding::factory()->create(['user_id' => $couple->id]);
        $this->actingAs($couple);

        Livewire::withQueryParams([
            'text' => 'Olha esse pin https://pin.it/terracota42',
        ])->test(PwaShareTarget::class)
            ->assertSet('pinterest_url', 'https://pin.it/terracota42')
            ->assertSet('image_url', 'https://i.pinimg.com/736x/ab/cd/ef/decor-terracota.jpg')
            ->assertSet('title', 'Arco Floral Terracota')
            ->assertSeeHtml('src="https://i.pinimg.com/736x/ab/cd/ef/decor-terracota.jpg"')
            ->call('quickSave', PinterestInspiration::CATEGORY_DECORACAO)
            ->assertHasNoErrors()
            ->assertDispatched('toast', message: 'Inspiração salva em Decoração com sucesso!');

        $this->assertDatabaseHas('pinterest_inspirations', [
            'wedding_id' => $wedding->id,
            'category' => PinterestInspiration::CATEGORY_DECORACAO,
            'title' => 'Arco Floral Terracota',
            'pinterest_url' => 'https://pin.it/terracota42',
            'image_url' => 'https://i.pinimg.com/736x/ab/cd/ef/decor-terracota.jpg',
            'source' => PinterestInspiration::SOURCE_PWA_SHARE,
        ]);
    }

    public function test_pinterest_metadata_resolver_falls_back_to_oembed_and_handles_failures(): void
    {
        config()->set('services.pinterest.fetch_metadata', true);

        $couple = User::factory()->couple()->create();
        $wedding = Wedding::factory()->create(['user_id' => $couple->id]);
        $this->actingAs($couple);

        Http::fake([
            'https://br.pinterest.com/pin/555444333/' => Http::response(
                '<html><head><meta property="og:image" content="https://s.pinimg.com/webapp/pinterest_logo.png"></head></html>',
                200,
            ),
            'https://www.pinterest.com/oembed.json?url=https%3A%2F%2Fwww.pinterest.com%2Fpin%2F555444333%2F' => Http::response([
                'thumbnail_url' => 'https://i.pinimg.com/236x/11/22/33/oembed-make.jpg',
                'title' => 'Make Noiva Clássica',
            ], 200),
            'https://br.pinterest.com/pin/666777888/' => Http::response('Error', 500),
            'https://www.pinterest.com/oembed.json?url=https%3A%2F%2Fwww.pinterest.com%2Fpin%2F666777888%2F' => Http::response('Error', 500),
            'https://br.pinterest.com/pin/888111222/' => Http::response(
                '<html><head><meta name="twitter:image" content="https://i.pinimg.com/736x/99/88/77/board-auto.jpg"></head></html>',
                200,
            ),
        ]);

        // 1. Generic s.pinimg.com logo is rejected and triggers oEmbed fallback
        Livewire::withQueryParams([
            'url' => 'https://br.pinterest.com/pin/555444333/',
        ])->test(PwaShareTarget::class)
            ->assertSet('image_url', 'https://i.pinimg.com/236x/11/22/33/oembed-make.jpg')
            ->assertSet('title', 'Make Noiva Clássica')
            ->call('quickSave', PinterestInspiration::CATEGORY_MAQUIAGEM)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pinterest_inspirations', [
            'wedding_id' => $wedding->id,
            'pinterest_url' => 'https://br.pinterest.com/pin/555444333/',
            'image_url' => 'https://i.pinimg.com/236x/11/22/33/oembed-make.jpg',
        ]);

        // 2. Pinterest 500 error never blocks saving the pin
        Livewire::withQueryParams([
            'title' => 'Penteado Resiliente',
            'url' => 'https://br.pinterest.com/pin/666777888/',
        ])->test(PwaShareTarget::class)
            ->assertSet('image_url', null)
            ->call('quickSave', PinterestInspiration::CATEGORY_PENTEADO)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pinterest_inspirations', [
            'wedding_id' => $wedding->id,
            'pinterest_url' => 'https://br.pinterest.com/pin/666777888/',
            'image_url' => null,
        ]);

        // 3. Direct image_url query param is preserved without hitting HTTP
        Livewire::withQueryParams([
            'url' => 'https://br.pinterest.com/pin/777111222/',
            'image_url' => 'https://i.pinimg.com/564x/aa/bb/cc/direct-query.jpg',
        ])->test(PwaShareTarget::class)
            ->assertSet('image_url', 'https://i.pinimg.com/564x/aa/bb/cc/direct-query.jpg')
            ->call('quickSave', PinterestInspiration::CATEGORY_DECORACAO)
            ->assertHasNoErrors();

        // 4. PinterestBoard also resolves image_url automatically when omitted
        Livewire::test(PinterestBoard::class)
            ->set('category', PinterestInspiration::CATEGORY_DECORACAO)
            ->set('type', PinterestInspiration::TYPE_PIN)
            ->set('pinterest_url', 'https://br.pinterest.com/pin/888111222/')
            ->call('saveInspiration')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pinterest_inspirations', [
            'wedding_id' => $wedding->id,
            'pinterest_url' => 'https://br.pinterest.com/pin/888111222/',
            'image_url' => 'https://i.pinimg.com/736x/99/88/77/board-auto.jpg',
        ]);
    }

    public function test_pinterest_metadata_resolver_blocks_non_pinterest_hosts_and_unsafe_redirects(): void
    {
        config()->set('services.pinterest.fetch_metadata', true);

        Http::fake([
            'https://pin.it/evil-redirect' => Http::response('', 302, [
                'Location' => 'http://127.0.0.1/internal-secret',
            ]),
        ]);

        $resolver = app(PinterestMetadataResolver::class);

        $this->assertSame(
            ['image_url' => null, 'title' => null, 'canonical_url' => null],
            $resolver->resolve('http://127.0.0.1/admin'),
        );
        $this->assertSame(
            ['image_url' => null, 'title' => null, 'canonical_url' => null],
            $resolver->resolve('https://evil.example/pin/1'),
        );
        $this->assertSame(
            ['image_url' => null, 'title' => null, 'canonical_url' => null],
            $resolver->resolve('https://pin.it/evil-redirect'),
        );

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->url() === 'https://pin.it/evil-redirect');
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '127.0.0.1'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'evil.example'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'oembed.json'));
    }
}
