<?php

use App\Livewire\Garage\Index;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

it('creates a private vehicle owned by the authenticated user', function () {
    $user = User::factory()->create();
    Livewire::actingAs($user)->test(Index::class)->call('create')->set('brand', 'Volkswagen')->set('model', 'Golf')->set('trim', 'GTI')->set('model_year', '2017')->set('engine', '2.0 TSI')->call('save')->assertHasNoErrors()->assertSee('Veículo salvo');
    expect(Vehicle::sole()->user_id)->toBe($user->id);
    expect(Vehicle::sole()->visibility)->toBe('private');
});

it('validates required fields year and visibility', function () {
    Livewire::actingAs(User::factory()->create())->test(Index::class)->set('brand', '   ')->set('model_year', '1800')->set('visibility', 'invalid')->call('save')->assertHasErrors(['brand', 'model', 'trim', 'engine', 'model_year', 'visibility']);
    expect(Vehicle::count())->toBe(0);
});

it('rejects future years beyond next year', function () {
    Livewire::actingAs(User::factory()->create())->test(Index::class)->set('model_year', (string) (now()->year + 2))->call('save')->assertHasErrors('model_year');
});

it('lists only the owners vehicles', function () {
    $own = Vehicle::factory()->create(['model' => 'Meu Golf']);
    Vehicle::factory()->create(['model' => 'Carro de outra pessoa']);
    Livewire::actingAs($own->user)->test(Index::class)->assertSee('Meu Golf')->assertDontSee('Carro de outra pessoa');
});

it('updates the owners vehicle', function () {
    $vehicle = Vehicle::factory()->create();
    Livewire::actingAs($vehicle->user)->test(Index::class)->call('edit', $vehicle->id)->set('model', 'Golf atualizado')->set('visibility', 'public')->call('save')->assertHasNoErrors();
    expect($vehicle->fresh()->model)->toBe('Golf atualizado');
    expect($vehicle->fresh()->visibility)->toBe('public');
});

it('denies editing and deleting another persons vehicle', function (string $action) {
    $vehicle = Vehicle::factory()->create();
    Livewire::actingAs(User::factory()->create())->test(Index::class)->call($action, $vehicle->id)->assertForbidden();
    expect($vehicle->fresh())->not->toBeNull();
})->with(['edit', 'delete']);

it('deletes the owners vehicle', function () {
    $vehicle = Vehicle::factory()->create();
    Livewire::actingAs($vehicle->user)->test(Index::class)->call('delete', $vehicle->id)->assertHasNoErrors();
    expect($vehicle->fresh())->toBeNull();
});

it('hides private vehicles and removes access after privacy changes', function () {
    $vehicle = Vehicle::factory()->create();
    $this->get(route('vehicles.show', $vehicle))->assertNotFound();
    $vehicle->update(['visibility' => 'public']);
    $this->get(route('vehicles.show', $vehicle))->assertOk()->assertSee('Golf')->assertDontSee($vehicle->user->email)->assertHeader('Cache-Control', 'no-store, private');
    $vehicle->update(['visibility' => 'private']);
    $this->get(route('vehicles.show', $vehicle))->assertNotFound();
});

it('requires authenticated verified accounts to access the garage', function () {
    $this->get('/garagem')->assertRedirect(route('login'));
    $this->actingAs(User::factory()->unverified()->create())->get('/garagem')->assertRedirect(route('verification.notice'));
});

it('shows the editorial garage with a photo cover and navigation', function () {
    $vehicle = Vehicle::factory()->create();
    $photo = $vehicle->photos()->create(['path' => 'vehicles/cover.webp']);
    $this->actingAs($vehicle->user)->get('/garagem')->assertOk()
        ->assertSee('Mais que um carro.')->assertSee('Minha garagem')
        ->assertSee(route('vehicle-photos.show', $photo), false)
        ->assertDontSee('data-flux-sidebar', false);
});

it('uses the Autoverse visual identity on garage and sign in', function () {
    $this->get('/login')->assertOk()->assertSee('mc-auth', false)->assertSee('O universo');
    $this->actingAs(User::factory()->create())->get('/garagem')->assertOk()->assertSee('mc-masthead', false)->assertSee('mc-collection', false);
});
