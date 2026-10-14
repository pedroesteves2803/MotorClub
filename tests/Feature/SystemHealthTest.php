<?php

use App\Services\SystemHealthService;
use App\Models\User;

it('gera um diagnóstico seguro sem expor segredos', function () {
    config([
        'services.google.client_secret' => 'secret-that-must-stay-hidden',
        'services.supabase.service_key' => 'service-key-that-must-stay-hidden',
        'services.stripe.secret' => 'stripe-secret-that-must-stay-hidden',
    ]);

    $snapshot = app(SystemHealthService::class)->snapshot();
    $serialized = json_encode($snapshot, JSON_THROW_ON_ERROR);

    expect($snapshot)->toHaveKeys(['checked_at', 'summary', 'checks']);
    expect($snapshot['summary'])->toHaveKeys(['ok', 'warning', 'error']);
    expect($snapshot['checks'])->toHaveCount(10);
    expect($serialized)->not->toContain('secret-that-must-stay-hidden');
    expect($serialized)->not->toContain('service-key-that-must-stay-hidden');
    expect($serialized)->not->toContain('stripe-secret-that-must-stay-hidden');

    foreach ($snapshot['checks'] as $check) {
        expect($check)->toHaveKeys(['key', 'label', 'status', 'status_label', 'detail', 'meta']);
    }
});

it('restringe o diagnóstico técnico ao super administrador', function () {
    $superAdmin = User::factory()->create(['admin_role' => 'super_admin']);

    $this->actingAs($superAdmin)
        ->get('/admin/diagnostico-tecnico')
        ->assertOk()
        ->assertSee('Diagnóstico técnico')
        ->assertSee('Banco de dados');

    $moderator = User::factory()->create(['admin_role' => 'moderator']);

    $this->actingAs($moderator)
        ->get('/admin/diagnostico-tecnico')
        ->assertForbidden();
});

it('não renderiza valores de integração na tela administrativa', function () {
    config([
        'services.google.client_secret' => 'secret-that-must-stay-hidden',
        'services.supabase.service_key' => 'service-key-that-must-stay-hidden',
    ]);

    $this->actingAs(User::factory()->create(['admin_role' => 'super_admin']))
        ->get('/admin/diagnostico-tecnico')
        ->assertOk()
        ->assertDontSee('secret-that-must-stay-hidden')
        ->assertDontSee('service-key-that-must-stay-hidden');
});
