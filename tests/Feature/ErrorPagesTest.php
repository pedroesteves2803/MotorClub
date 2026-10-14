<?php

use Symfony\Component\HttpKernel\Exception\HttpException;

it('renders the branded error states with the shared car illustration', function () {
    $states = [
        '403' => ['title' => 'Você não tem acesso a esta garagem', 'variant' => 'blocked'],
        '404' => ['title' => 'Essa rota não foi encontrada', 'variant' => 'detour'],
        '419' => ['title' => 'Sua sessão expirou', 'variant' => 'restart'],
        '422' => ['title' => 'Confira as informações', 'variant' => 'blocked'],
        '429' => ['title' => 'Calma, você acelerou rápido demais', 'variant' => 'speed'],
        '500' => ['title' => 'Algo saiu do trajeto', 'variant' => 'workshop'],
        '503' => ['title' => 'Estamos em manutenção na pista', 'variant' => 'pause'],
    ];

    foreach ($states as $code => $state) {
        $html = view('errors.'.$code, ['exception' => new HttpException((int) $code)])->render();

        expect($html)
            ->toContain('AUTOVERSE')
            ->toContain('av-error-car__body')
            ->toContain('av-error-page--'.$state['variant'])
            ->toContain($state['title'])
            ->toContain('noindex,nofollow')
            ->not->toContain('Whoops')
            ->not->toContain('Stack trace');
    }
});

it('serves the branded not found page with the correct HTTP status', function () {
    $this->get('/rota-que-nao-existe-no-autoverse')
        ->assertNotFound()
        ->assertSee('Essa rota não foi encontrada')
        ->assertSee('av-error-car__body', false)
        ->assertDontSee('Whoops');
});
