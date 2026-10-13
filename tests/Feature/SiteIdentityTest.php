<?php

use App\Models\Vehicle;

it('shares the site identity and navigation across public pages', function () {
    $vehicle = Vehicle::factory()->create(['visibility' => 'public']);
    foreach (['/', '/comunidade', route('vehicles.show', $vehicle)] as $url) {
        $this->get($url)->assertOk()->assertSee('mc-site', false)->assertSee('mc-masthead', false)->assertSee('data-theme-headlight', false)->assertSee('data-theme-toggle', false)->assertSee('Navegação principal')->assertSee('Explorar')->assertSee('Entrar')->assertSee('AUTOVERSE');
    }
});

it('oferece menu responsivo acessível nas páginas públicas', function () {
    foreach (['/', '/projetos', '/shots', '/explorar'] as $url) {
        $this->get($url)->assertOk()->assertSee('aria-controls="site-navigation"', false)->assertSee('aria-expanded="false"', false)->assertSee('mc-menu-toggle', false);
    }
});

it('usa o próprio farol como controle acessível de tema', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('data-theme-toggle-button', false)
        ->assertSee('data-theme-toggle-status', false)
        ->assertSee('mc-theme-toggle--floating', false)
        ->assertSee('data-theme-toggle-floating', false)
        ->assertSee('mc-theme-toggle__icon', false)
        ->assertSee('M36 24h24', false)
        ->assertSee('aria-label="Alternar tema"', false)
        ->assertDontSee('mc-theme-toggle__mark', false)
        ->assertDontSee('<select aria-label="Tema" data-theme-toggle', false);
});
