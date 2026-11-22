<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\PlatformSettings;
use App\Models\PlatformSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_setting_current_creates_singleton_with_default_values(): void
    {
        $this->assertDatabaseCount('platform_settings', 0);

        $setting = PlatformSetting::current();
        $secondCall = PlatformSetting::current();

        $this->assertSame($setting->id, $secondCall->id);
        $this->assertDatabaseCount('platform_settings', 1);
        $this->assertFalse($setting->paywall_pinterest_inspirations_enabled);
        $this->assertSame('4.99', (string) $setting->platform_gift_fee_percent);
        $this->assertSame('69.90', (string) $setting->regional_highlight_monthly_price);
        $this->assertSame('39.90', (string) $setting->portfolio_monthly_price);
        $this->assertSame(2, $setting->banner_cycle_months);
        $this->assertSame(5, $setting->banner_max_slots);
        $this->assertSame('149.90', (string) $setting->banner_cycle_price);
    }

    public function test_only_admin_can_access_admin_settings_route(): void
    {
        $this->get('/admin/configuracoes')->assertRedirect('/login');

        $couple = User::factory()->couple()->create();
        $this->actingAs($couple)->get('/admin/configuracoes')->assertForbidden();

        $vendor = User::factory()->vendor()->create();
        $this->actingAs($vendor)->get('/admin/configuracoes')->assertForbidden();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->get('/admin/configuracoes')
            ->assertOk()
            ->assertSee('Configurações Globais da Plataforma')
            ->assertSee('Paywall das Inspirações do Pinterest');
    }

    public function test_admin_can_toggle_pinterest_paywall_and_update_platform_settings(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(PlatformSettings::class)
            ->assertDontSee('Desativado (Padrão MVP)')
            ->call('togglePinterestPaywall')
            ->assertSet('paywall_pinterest_inspirations_enabled', true)
            ->assertDispatched('toast', message: 'Paywall de inspirações do Pinterest ativado.')
            ->assertSeeHtml('data-toast');

        $this->assertTrue(PlatformSetting::current()->paywall_pinterest_inspirations_enabled);

        Livewire::test(PlatformSettings::class)
            ->set('paywall_pinterest_inspirations_enabled', false)
            ->set('platform_gift_fee_percent', '3.99')
            ->set('regional_highlight_monthly_price', '89.90')
            ->set('portfolio_monthly_price', '49.90')
            ->set('banner_cycle_months', 3)
            ->set('banner_max_slots', 5)
            ->set('banner_cycle_price', '199.90')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast', message: 'Configurações globais da plataforma atualizadas com sucesso.')
            ->assertSeeHtml('data-toast')
            ->assertSee('Configurações globais da plataforma atualizadas com sucesso.');

        $updated = PlatformSetting::current()->fresh();

        $this->assertFalse($updated->paywall_pinterest_inspirations_enabled);
        $this->assertSame('3.99', (string) $updated->platform_gift_fee_percent);
        $this->assertSame('89.90', (string) $updated->regional_highlight_monthly_price);
        $this->assertSame('49.90', (string) $updated->portfolio_monthly_price);
        $this->assertSame(3, $updated->banner_cycle_months);
        $this->assertSame(5, $updated->banner_max_slots);
        $this->assertSame('199.90', (string) $updated->banner_cycle_price);
    }

    public function test_database_seeder_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('platform_settings', 1);
        $this->assertDatabaseHas('users', ['email' => 'noivos@nossovoto.com.br', 'role' => User::ROLE_COUPLE]);
        $this->assertDatabaseHas('users', ['email' => 'fornecedor@nossovoto.com.br', 'role' => User::ROLE_VENDOR]);
        $this->assertDatabaseHas('users', ['email' => 'admin@nossovoto.com.br', 'role' => User::ROLE_ADMIN]);
    }

    public function test_platform_setting_current_caches_per_request_and_invalidates_on_update(): void
    {
        DB::table('platform_settings')->insert([
            'paywall_pinterest_inspirations_enabled' => false,
            'platform_gift_fee_percent' => 4.99,
            'regional_highlight_monthly_price' => 69.90,
            'portfolio_monthly_price' => 39.90,
            'banner_cycle_months' => 2,
            'banner_max_slots' => 5,
            'banner_cycle_price' => 149.90,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::enableQueryLog();
        DB::flushQueryLog();

        $first = PlatformSetting::current();
        $second = PlatformSetting::current();
        $third = PlatformSetting::current();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(1, $queries);
        $this->assertSame($first->id, $second->id);
        $this->assertSame($first->id, $third->id);

        $cachedPayload = Cache::memo()->get('nossovoto.platform_setting.current');
        $this->assertIsArray($cachedPayload);
        $this->assertSame(
            $cachedPayload,
            unserialize(serialize($cachedPayload), ['allowed_classes' => false]),
        );

        $first->update(['platform_gift_fee_percent' => '6.50']);

        $this->app->forgetScopedInstances();

        $this->assertSame('6.50', (string) PlatformSetting::current()->platform_gift_fee_percent);
    }
}
