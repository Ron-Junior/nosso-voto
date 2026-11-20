<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\UserPanel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_couple_and_vendor_panels_render_responsive_terracota_and_areia_layout(): void
    {
        $couple = User::factory()->couple()->create(['name' => 'Clara & Rafael']);
        $this->actingAs($couple)
            ->get('/painel')
            ->assertOk()
            ->assertSee('#FBF9F7')
            ->assertSee('#FFFFFF')
            ->assertSee('#EAE0DA')
            ->assertSee('#C25E38')
            ->assertSee('#2C2523')
            ->assertSee('Clara & Rafael')
            ->assertDontSee('100% Gratuito')
            ->assertDontSee('Sem mensalidades')
            ->assertSee('Gerar Novo Token')
            ->assertSee('lg:hidden')
            ->assertSee('hidden lg:flex')
            ->assertSee('Navegação móvel')
            ->assertSee('Menu principal');

        $vendor = User::factory()->vendor()->create(['name' => 'Buffet Horizonte']);
        $this->actingAs($vendor)
            ->get('/fornecedor/painel')
            ->assertOk()
            ->assertSee('Buffet Horizonte')
            ->assertSee('Fornecedor')
            ->assertSee('Gerar Novo Token')
            ->assertSee('lg:hidden')
            ->assertSee('hidden lg:flex');
    }

    public function test_authenticated_user_can_regenerate_api_token(): void
    {
        $user = User::factory()->couple()->create();
        $originalToken = $user->api_token;

        $this->actingAs($user);

        Livewire::test(UserPanel::class)
            ->assertSee($originalToken)
            ->call('regenerateToken')
            ->assertSee('Novo token de integração gerado com sucesso.');

        $user->refresh();
        $secondToken = $user->api_token;

        $this->assertNotSame($originalToken, $secondToken);
        $this->assertGreaterThanOrEqual(40, strlen($secondToken));

        $this->post(route('api-token.regenerate'))
            ->assertRedirect();

        $user->refresh();
        $this->assertNotSame($secondToken, $user->api_token);
        $this->assertGreaterThanOrEqual(40, strlen($user->api_token));
    }
}
