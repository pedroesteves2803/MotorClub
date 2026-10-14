<?php

use App\Models\User;

it('aplica cabeçalhos de segurança nas páginas públicas', function () {
    $this->get('/')->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Content-Security-Policy-Report-Only');
});

it('não oferece perfis de demonstração quando desativados', function () {
    config(['production.demo_enabled' => false]);
    $demo = User::factory()->create(['name' => 'Pessoa Fictícia Exclusiva', 'profile_public' => true, 'is_demo' => true]);
    $this->get('/comunidade')->assertOk()->assertDontSee($demo->name)->assertDontSee('GB Ciate');
    $slug = array_key_first(config('demo_garages'));
    $this->get('/comunidade/garagens/'.$slug)->assertNotFound();
});

it('recusa prontidão de uma instalação local sem requisitos de produção', function () {
    $this->artisan('motorclub:production-check')->assertExitCode(1);
});

it('aceita requisitos configurados e rejeita base de demonstração', function (string $environment) {
    app()->instance('env', $environment);
    $command = 'motorclub:production-check'.($environment === 'staging' ? ' --staging' : '');
    config([
        'app.url' => 'https://motorclub.example',
        'app.debug' => false,
        'app.key' => 'base64:'.base64_encode(str_repeat('a', 32)),
        'session.secure' => true,
        'session.encrypt' => true,
        'production.demo_enabled' => false,
        'production.test_mode' => $environment === 'staging',
        'production.staging_user' => 'avaliador',
        'production.staging_password' => 'senha-de-teste-com-mais-de-16-caracteres',
        'production.support_email' => 'suporte@motorclub.example',
        'production.storage_persistent' => true,
        'production.backups_verified' => true,
        'services.google.client_id' => 'test-client',
        'services.google.client_secret' => 'test-secret',
        'services.google.redirect' => 'https://motorclub.example/auth/google/callback',
    ]);
    $this->artisan($command)->assertExitCode(0);
    User::factory()->create(['is_demo' => true]);
    $this->artisan($command)->assertExitCode(1);
})->with(['production', 'staging']);
