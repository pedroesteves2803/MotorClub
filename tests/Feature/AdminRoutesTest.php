<?php

use App\Models\User;

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
        ->assertSee('Entrar com Google')
        ->assertSee('E-mail');
});
