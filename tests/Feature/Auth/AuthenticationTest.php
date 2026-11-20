<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_without_dashboard_chrome(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('#FBF9F7')
            ->assertSee('#FFFFFF')
            ->assertSee('#EAE0DA')
            ->assertSee('#C25E38')
            ->assertSee('#2C2523')
            ->assertSee('Funcionalidades')
            ->assertSee('Como Funciona')
            ->assertSee('Para Noivos')
            ->assertSee('Para Fornecedores')
            ->assertDontSee('Navegação móvel')
            ->assertDontSee('Menu principal');
    }

    public function test_login_and_register_pages_use_guest_layout_without_dashboard_chrome(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Bem-vindo de volta')
            ->assertSee('Voltar para a página inicial')
            ->assertDontSee('Navegação móvel')
            ->assertDontSee('Menu principal');

        $this->get('/cadastro')
            ->assertOk()
            ->assertSee('Sou Noivo/Noiva')
            ->assertSee('Sou Fornecedor')
            ->assertSee('Voltar para a página inicial')
            ->assertDontSee('Navegação móvel')
            ->assertDontSee('Menu principal');
    }

    public function test_user_can_register_as_couple_with_free_access_and_api_token(): void
    {
        Livewire::test(Register::class)
            ->set('role', User::ROLE_COUPLE)
            ->set('name', 'Mariana & Pedro')
            ->set('email', 'mariana.pedro@exemplo.com.br')
            ->set('password', 'segredo123')
            ->set('password_confirmation', 'segredo123')
            ->call('register')
            ->assertRedirect('/painel');

        $user = User::query()->where('email', 'mariana.pedro@exemplo.com.br')->first();

        $this->assertNotNull($user);
        $this->assertSame(User::ROLE_COUPLE, $user->role);
        $this->assertTrue($user->hasFreeAccess());
        $this->assertNotNull($user->api_token);
        $this->assertGreaterThanOrEqual(40, strlen($user->api_token));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_register_as_vendor_and_redirect_to_vendor_panel(): void
    {
        Livewire::test(Register::class)
            ->call('selectRole', User::ROLE_VENDOR)
            ->set('name', 'Flores do Campo Decoração')
            ->set('email', 'contato@floresdocampo.com.br')
            ->set('password', 'segredo123')
            ->set('password_confirmation', 'segredo123')
            ->call('register')
            ->assertRedirect('/fornecedor/painel');

        $user = User::query()->where('email', 'contato@floresdocampo.com.br')->first();

        $this->assertNotNull($user);
        $this->assertSame(User::ROLE_VENDOR, $user->role);
        $this->assertFalse($user->hasFreeAccess());
        $this->assertGreaterThanOrEqual(40, strlen($user->api_token));
        $this->assertAuthenticatedAs($user);
    }

    public function test_public_registration_blocks_admin_role(): void
    {
        Livewire::test(Register::class)
            ->set('role', User::ROLE_ADMIN)
            ->set('name', 'Tentativa Admin')
            ->set('email', 'invasor@exemplo.com.br')
            ->set('password', 'segredo123')
            ->set('password_confirmation', 'segredo123')
            ->call('register')
            ->assertHasErrors(['role']);

        $this->assertDatabaseMissing('users', ['email' => 'invasor@exemplo.com.br']);
        $this->assertGuest();
    }

    public function test_users_can_login_and_are_redirected_by_role(): void
    {
        $couple = User::factory()->couple()->create(['password' => 'senha1234']);
        $vendor = User::factory()->vendor()->create(['password' => 'senha1234']);
        $admin = User::factory()->admin()->create(['password' => 'senha1234']);

        Livewire::test(Login::class)
            ->set('email', $couple->email)
            ->set('password', 'senha1234')
            ->call('login')
            ->assertRedirect('/painel');
        $this->post('/logout')->assertRedirect('/');

        Livewire::test(Login::class)
            ->set('email', $vendor->email)
            ->set('password', 'senha1234')
            ->call('login')
            ->assertRedirect('/fornecedor/painel');
        $this->post('/logout')->assertRedirect('/');

        Livewire::test(Login::class)
            ->set('email', $admin->email)
            ->set('password', 'senha1234')
            ->call('login')
            ->assertRedirect('/admin/configuracoes');
    }

    public function test_login_fails_gracefully_with_invalid_credentials(): void
    {
        $couple = User::factory()->couple()->create(['password' => 'senha1234']);

        Livewire::test(Login::class)
            ->set('email', $couple->email)
            ->set('password', 'senha-errada')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }
}

