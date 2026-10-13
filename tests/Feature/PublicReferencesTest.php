<?php

use App\Models\CarEvent;
use App\Models\User;
use App\Models\Workshop;
use Carbon\Carbon;

it('exibe oficinas reais com fonte sem criar contas ou orçamentos em nome delas', function () {
    $this->get('/oficinas')->assertOk()->assertSee('ACF Performance')->assertSee('Batistinha Garage')->assertSee('Pedidos de teste não são enviados')->assertSee('https://www.acfperformance.com/contato/', false);
    expect(Workshop::count())->toBe(0)->and(User::count())->toBe(0);
    $this->get('/oficinas?q=ACF')->assertSee('ACF Performance')->assertDontSee('Batistinha Garage');
    $this->get('/oficinas?q=Curitiba')->assertDontSee('ACF Performance');
});
it('filtra eventos públicos reais por cidade estado data e área pessoal', function () {
    $this->travelTo(Carbon::parse('2026-10-09'));
    $this->get('/eventos')->assertOk()->assertSee('Grande Prêmio de São Paulo')->assertSee('Salão do Automóvel 2027')->assertSee('6 a 8 de novembro de 2026');
    $this->get('/eventos?state=PR')->assertDontSee('Grande Prêmio de São Paulo');
    $this->get('/eventos?q=Curitiba')->assertDontSee('Salão do Automóvel 2027');
    $this->actingAs(User::factory()->create())->get('/eventos?mine=1')->assertDontSee('Salão do Automóvel 2027');
    expect(CarEvent::count())->toBe(0);
    auth()->logout();
    $this->travelTo(Carbon::parse('2028-01-01'));
    $this->get('/eventos')->assertDontSee('Salão do Automóvel 2027');
});
it('não apresenta números genéricos como ficha confirmada do criador', function () {
    $this->get('/comunidade/garagens/gb-ciate')->assertOk()->assertSee('A versão exata do carro do criador não foi confirmada')->assertDontSee('8.400 km')->assertDontSee('Custo fictício');
    $this->get('/comunidade/garagens/ricardinho-acf')->assertSee('Não foi confirmada a versão')->assertSee('310 cv')->assertSee('350 cv');
    foreach (config('verified_cars') as $record) {
        expect($record['source_url'])->toStartWith('https://');
        expect($record)->toHaveKeys(['title', 'description', 'specs', 'checked_at']);
        expect($record)->not->toHaveKey('history');
    }
});
