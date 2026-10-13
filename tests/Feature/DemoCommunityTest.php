<?php

use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\DemoCommunitySeeder;

it('seeds repeatable demo profiles without exposing real accounts', function () {
    User::factory()->create(['name' => 'Pessoa real confidencial']);
    $this->seed(DemoCommunitySeeder::class);
    $this->seed(DemoCommunitySeeder::class);
    expect(User::where('is_demo', true)->count())->toBe(8);
    expect(Vehicle::count())->toBe(8);
    $this->get('/comunidade')->assertOk()->assertSee('Perfis fictícios e garagens demonstrativas inspiradas em criadores reais')->assertSee('Marina Costa')->assertDontSee('Pessoa real confidencial')->assertDontSee('demo-1@motorclub.example');
});
it('labels the public demo vehicle', function () {
    $this->seed(DemoCommunitySeeder::class);
    $this->get(route('vehicles.show', Vehicle::first()))->assertOk()->assertSee('Veículo fictício de demonstração');
});

it('shows demo photo covers and creator garages in the community', function () {
    $this->seed(DemoCommunitySeeder::class);
    $vehicle = Vehicle::first();
    $photo = $vehicle->photos()->create(['path' => 'vehicles/demo-test.webp']);
    $this->get('/comunidade')->assertOk()->assertSee(route('vehicle-photos.show', $photo), false)
        ->assertSee('Imagem gerada por IA')->assertSee('GB Ciate')->assertSee('Ricardinho ACF')->assertSee('Alemão da Caravan')
        ->assertDontSee('Perfis para conhecer')->assertSee('Perfil de demonstração · Não oficial')
        ->assertSee(route('community.garage', 'gb-ciate'), false);
    expect(User::where('name', 'Rubens Barrichello')->exists())->toBeFalse();
});

it('opens creator garages with credited Instagram photos and no invented image', function () {
    foreach (config('demo_garages') as $slug => $garage) {
        $response = $this->get(route('community.garage', $slug))->assertOk()
            ->assertSee($garage['name'])->assertSee('sem participação ou vínculo com o criador')
            ->assertSee('noindex', false)->assertDontSee('Ilustração por IA');
        expect(is_file(public_path('images/demo/'.$garage['avatar'])))->toBeTrue();
        foreach ($garage['cars'] as $car) {
            $response->assertSee($car['model']);
            if ($car['image']) {
                expect(is_file(public_path('images/demo/'.$car['image'])))->toBeTrue();
                $response->assertSee($car['post'], false)->assertSee($car['credit']);
            } else {
                $response->assertSee('Foto do carro pendente de confirmação');
            }
        }
    }
    expect(User::whereIn('name', ['GB Ciate', 'Ricardinho ACF', 'Alemão da Caravan'])->exists())->toBeFalse();
});

it('returns not found for an unknown creator garage', function () {
    $this->get(route('community.garage', 'perfil-inexistente'))->assertNotFound();
});

it('mostra referências com fontes sem inventar histórico particular', function () {
    $this->seed(DemoCommunitySeeder::class);
    foreach (Vehicle::all() as $vehicle) {
        $vehicle->entries()->create(['title' => 'Histórico particular confidencial', 'category' => 'maintenance', 'occurred_on' => '2026-01-01', 'description' => 'Segredo']);
        $this->get(route('vehicles.show', $vehicle))->assertOk()->assertSee('Dados públicos verificados')
            ->assertSee('Fonte:')->assertDontSee('Diário da garagem')->assertDontSee('Histórico particular confidencial');
    }
    foreach (config('demo_garages') as $slug => $garage) {
        $this->get(route('community.garage', $slug))->assertSee('Dados públicos verificados')
            ->assertDontSee('Custo fictício')->assertDontSee('Foto do carro pendente de confirmação');
    }
});

it('does not add simulated data to a real public vehicle', function () {
    $vehicle = Vehicle::factory()->create(['model' => 'Golf', 'trim' => 'TSI', 'visibility' => 'public']);
    $this->get(route('vehicles.show', $vehicle))->assertOk()->assertDontSee('Dados simulados para teste')->assertDontSee('Custo fictício');
});
