<?php

use App\Livewire\Garage\Index;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleSlugHistory;
use App\Services\VehicleSlugService;
use Livewire\Livewire;

it('cadastra uma moto e gera um slug público estável', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(Index::class)
        ->call('create')
        ->set('vehicle_type', 'motorcycle')
        ->set('brand', 'Yamaha')
        ->set('model', 'MT-07')
        ->set('public_name', 'Minha MT')
        ->set('trim', '689 cc')
        ->set('model_year', '2024')
        ->set('engine', '689 cc bicilíndrico')
        ->set('visibility', 'public')
        ->call('save')
        ->assertHasNoErrors();

    $vehicle = Vehicle::sole();
    expect($vehicle->vehicle_type)->toBe('motorcycle')
        ->and($vehicle->slug)->toBe('minha-mt')
        ->and($this->get('/veiculos/'.$vehicle->slug)->status())->toBe(200);
});

it('mantém a rota numérica e publica a URL canônica com slug', function () {
    $vehicle = Vehicle::factory()->create(['visibility' => 'public']);

    $response = $this->get(route('vehicles.show', $vehicle));

    $response->assertOk()->assertSee('rel="canonical" href="'.route('vehicles.slug.show', ['slug' => $vehicle->fresh()->slug]).'"', false);
    $this->get(route('vehicles.slug.show', ['slug' => $vehicle->fresh()->slug]))->assertOk();
});

it('redireciona slugs antigos para o slug atual', function () {
    $vehicle = Vehicle::factory()->create(['visibility' => 'public']);
    $slugs = app(VehicleSlugService::class);
    $slugs->ensure($vehicle);
    $slugs->change($vehicle->fresh(), 'venom-black');

    expect(VehicleSlugHistory::where('slug', 'volkswagen-golf-gti')->exists())->toBeTrue();

    $this->get('/veiculos/volkswagen-golf-gti')
        ->assertRedirect(route('vehicles.slug.show', ['slug' => 'venom-black']))
        ->assertStatus(301);
});

it('não revela histórico de slug de veículo privado', function () {
    $vehicle = Vehicle::factory()->create(['visibility' => 'private']);
    $slugs = app(VehicleSlugService::class);
    $slugs->ensure($vehicle);
    $slugs->change($vehicle->fresh(), 'privado-agora');

    $this->get('/veiculos/volkswagen-golf-gti')->assertNotFound();
});

it('cria sufixos para slugs de veículos com o mesmo nome', function () {
    $first = Vehicle::factory()->create(['visibility' => 'public']);
    $second = Vehicle::factory()->create(['visibility' => 'public']);
    $slugs = app(VehicleSlugService::class);

    $slugs->ensure($first);
    $slugs->ensure($second);

    expect($first->fresh()->slug)->toBe('volkswagen-golf-gti')
        ->and($second->fresh()->slug)->toBe('volkswagen-golf-gti-2');
});
