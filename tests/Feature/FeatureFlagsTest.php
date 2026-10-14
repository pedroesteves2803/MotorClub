<?php

use App\Models\FeatureSetting;
use App\Models\User;

it('oculta os módulos fora da V1 por rotas protegidas', function () {
    config([
        'features.workshops' => false,
        'features.events' => false,
        'features.subscriptions' => false,
        'features.ai_reports' => false,
        'features.demo' => false,
    ]);

    $this->get('/oficinas')->assertNotFound();
    $this->get('/eventos')->assertNotFound();
    $this->get('/plano-oficina')->assertNotFound();
    $this->get('/como-testar')->assertNotFound();
});

it('mantém garagem shots e explorar disponíveis na V1', function () {
    config(['features.garage' => true, 'features.shots' => true, 'features.explore' => true]);

    $this->get('/projetos')->assertOk();
    $this->get('/shots')->assertOk();
    $this->get('/explorar')->assertOk();
});

it('permite reativar um módulo pelo banco sem alterar código', function () {
    config(['features.events' => false]);
    FeatureSetting::create(['feature' => 'events', 'enabled' => true]);

    $this->get('/eventos')->assertOk();
});

it('bloqueia o painel administrativo para usuários comuns', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertForbidden();
});

it('não exibe atalhos de módulos desativados na área autenticada', function () {
    config(['features.workshops' => false, 'features.events' => false]);
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)
        ->get('/garagem')
        ->assertOk()
        ->assertDontSee('Meus orçamentos')
        ->assertDontSee('Meus eventos');

    $this->actingAs($user)
        ->get('/meu-perfil')
        ->assertOk()
        ->assertDontSee('Oficina e orçamentos');
});
