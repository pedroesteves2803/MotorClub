<?php

use App\Livewire\Garage\Index;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('cria carro projeto principal e várias fotos em um único fluxo', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    Livewire::actingAs($user)->test(Index::class)
        ->call('create')
        ->set('brand', 'Volkswagen')
        ->set('model', 'Golf')
        ->set('trim', 'Highline')
        ->set('model_year', '2017')
        ->set('engine', '1.4 TSI')
        ->set('project_title', 'Golf de rua')
        ->set('project_description', 'Registro completo do carro e dos próximos passos.')
        ->set('project_category', 'maintenance')
        ->set('project_status', 'ongoing')
        ->set('photos', [UploadedFile::fake()->image('frente.jpg'), UploadedFile::fake()->image('interior.jpg')])
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Veículo salvo com o projeto principal e 2 foto(s).');

    $vehicle = Vehicle::with('projects')->sole();
    expect($vehicle->projects)->toHaveCount(1)
        ->and($vehicle->projects->sole()->title)->toBe('Golf de rua')
        ->and($vehicle->photos()->count())->toBe(2);
    foreach ($vehicle->photos as $photo) {
        Storage::disk('local')->assertExists($photo->path);
    }
});

it('abre o cadastro combinado quando acessado pelo atalho de projetos', function () {
    $this->actingAs(User::factory()->create())->get('/garagem?novo=1')->assertOk()->assertSee('Novo carro e projeto');
});

it('creates a private vehicle owned by the authenticated user', function () {
    $user = User::factory()->create();
    Livewire::actingAs($user)->test(Index::class)->call('create')->set('brand', 'Volkswagen')->set('model', 'Golf')->set('trim', 'GTI')->set('model_year', '2017')->set('engine', '2.0 TSI')->call('save')->assertHasNoErrors()->assertSee('Veículo salvo');
    expect(Vehicle::sole()->user_id)->toBe($user->id);
    expect(Vehicle::sole()->visibility)->toBe('private');
});

it('saves the real vehicle profile details without exposing it by default', function () {
    $user = User::factory()->create();
    Livewire::actingAs($user)->test(Index::class)
        ->call('create')
        ->set('brand', 'Volkswagen')
        ->set('model', 'Golf')
        ->set('public_name', 'Venom Black MK7')
        ->set('trim', 'Highline 1.4 TSI')
        ->set('generation', 'MK7')
        ->set('color', 'Preto')
        ->set('model_year', '2015')
        ->set('engine', '1.4 TSI Turbo · DSG DQ200')
        ->set('mileage', '180000')
        ->set('mileage_approximate', true)
        ->set('acquired_on', '2026-03-14')
        ->set('location', 'São Paulo, SP')
        ->set('instagram_url', 'https://www.instagram.com/venomblackmk7/')
        ->call('save')
        ->assertHasNoErrors();

    $vehicle = Vehicle::sole();
    expect($vehicle->public_name)->toBe('Venom Black MK7')
        ->and($vehicle->mileage)->toBe(180000)
        ->and($vehicle->mileage_approximate)->toBeTrue()
        ->and($vehicle->visibility)->toBe('private');
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
    $this->get('/login')->assertOk()->assertSee('mc-auth', false)->assertSee('Mostre a sua.');
    $this->actingAs(User::factory()->create())->get('/garagem')->assertOk()->assertSee('mc-masthead', false)->assertSee('mc-collection', false);
});
