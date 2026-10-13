<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;

it('expõe o painel somente pela rota administrativa em português', function () {
    $admin = User::factory()->create(['admin_role' => 'super_admin']);

    $this->actingAs($admin)->get('/central-mc-gestao')->assertOk();
    $this->get('/admin')->assertNotFound();
    $this->get('/administracao')->assertNotFound();
});

it('redireciona configurações legadas para a rota em português', function () {
    $this->actingAs(User::factory()->create())
        ->get('/settings')
        ->assertStatus(301)
        ->assertRedirect('/configuracoes/perfil');
});

it('permite acesso do administrador configurado por e-mail', function () {
    config(['motorclub.admin_emails' => ['admin@example.com']]);
    $admin = User::factory()->create(['email' => 'admin@example.com']);

    $this->actingAs($admin)->get('/central-mc-gestao')->assertOk();
});

it('renderiza o login do CMS em português com entrada Google', function () {
    $this->get('/central-mc-gestao/login')
        ->assertOk()
        ->assertSee('Entrar como administrador com Google')
        ->assertSee(route('auth.google.admin'), false)
        ->assertSee('E-mail');
});

it('recusa uma conta Google comum no login administrativo', function () {
    config(['services.google.client_id' => 'test-id', 'services.google.client_secret' => 'test-secret', 'services.google.redirect' => 'http://localhost/auth/google/callback']);
    Socialite::shouldReceive('driver->user')->once()->andReturn(
        (new GoogleUser)->setRaw(['email_verified' => true])->map(['id' => 'google-common', 'name' => 'Pessoa Teste', 'email' => 'pessoa@example.com'])
    );

    $this->withSession(['google_login_context' => 'admin'])
        ->get('/auth/google/callback')
        ->assertRedirect(route('filament.admin.auth.login'))
        ->assertSessionHasErrors('google');

    $this->assertGuest();
    expect(User::count())->toBe(0);
});

it('cria e autentica o administrador autorizado pelo e-mail', function () {
    config([
        'motorclub.admin_emails' => ['admin@example.com'],
        'services.google.client_id' => 'test-id',
        'services.google.client_secret' => 'test-secret',
        'services.google.redirect' => 'http://localhost/auth/google/callback',
    ]);
    Socialite::shouldReceive('driver->user')->once()->andReturn(
        (new GoogleUser)->setRaw(['email_verified' => true])->map(['id' => 'google-admin', 'name' => 'Admin Teste', 'email' => 'admin@example.com'])
    );

    $this->withSession(['google_login_context' => 'admin'])
        ->get('/auth/google/callback')
        ->assertRedirect(route('filament.admin.pages.dashboard'));

    $admin = User::sole();
    expect($admin->google_id)->toBe('google-admin')
        ->and($admin->admin_role)->toBe('super_admin');
    $this->assertAuthenticatedAs($admin);
});
