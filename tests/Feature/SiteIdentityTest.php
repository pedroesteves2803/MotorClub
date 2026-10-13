<?php

use App\Models\Vehicle;

it('shares the site identity and navigation across public pages', function () {
    $vehicle = Vehicle::factory()->create(['visibility' => 'public']);
    foreach (['/', '/comunidade', route('vehicles.show', $vehicle)] as $url) {
        $this->get($url)->assertOk()->assertSee('mc-site', false)->assertSee('mc-masthead', false)->assertSee('Navegação principal')->assertSee('Explorar')->assertSee('Entrar com Google');
    }
});

it('oferece menu responsivo acessível nas páginas públicas', function () {
    foreach (['/', '/projetos', '/shots', '/explorar'] as $url) {
        $this->get($url)->assertOk()->assertSee('aria-controls="site-navigation"', false)->assertSee('aria-expanded="false"', false)->assertSee('mc-menu-toggle', false);
    }
});
