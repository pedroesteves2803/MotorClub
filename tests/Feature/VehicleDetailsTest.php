<?php

use App\Livewire\Garage\Details;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('creates edits and deletes history with optional cost', function () {
    $v = Vehicle::factory()->create();
    $c = Livewire::actingAs($v->user)->test(Details::class, ['vehicle' => $v]);
    $c->set('title', 'Troca de óleo')->set('description', 'Óleo e filtro')->set('parts', 'Filtro')->set('services', 'Troca')->call('saveEntry')->assertHasNoErrors();
    $entry = $v->entries()->sole();
    expect($entry->cost)->toBeNull();
    $c->call('editEntry', $entry->id)->set('category', 'modification')->set('cost', '250.50')->call('saveEntry')->assertHasNoErrors();
    expect($entry->fresh()->cost)->toBe('250.50');
    $c->call('deleteEntry', $entry->id);
    expect($v->entries()->count())->toBe(0);
});
it('validates dates category costs and empty descriptions', function () {
    $v = Vehicle::factory()->create();
    Livewire::actingAs($v->user)->test(Details::class, ['vehicle' => $v])->set('category', 'bad')->set('occurred_on', now()->addDay()->toDateString())->set('cost', '-10')->call('saveEntry')->assertHasErrors(['title', 'description', 'category', 'occurred_on', 'cost']);
    expect($v->entries()->count())->toBe(0);
});

it('allows a private history entry without inventing an unknown date', function () {
    $v = Vehicle::factory()->create();

    Livewire::actingAs($v->user)->test(Details::class, ['vehicle' => $v])
        ->set('title', 'Serviço anterior informado pelo proprietário')
        ->set('description', 'A data original não foi informada; manter como histórico privado para atualização posterior.')
        ->set('occurred_on', '')
        ->call('saveEntry')
        ->assertHasNoErrors();

    expect($v->entries()->sole()->occurred_on)->toBeNull();
});
it('blocks other accounts from managing history and photos', function () {
    $v = Vehicle::factory()->create(['visibility' => 'public']);
    Livewire::actingAs(User::factory()->create())->test(Details::class, ['vehicle' => $v])->assertForbidden();
});
it('does not edit or delete entries from another vehicle', function (string $action) {
    $v = Vehicle::factory()->create();
    $other = Vehicle::factory()->create();
    $entry = $other->entries()->create(['category' => 'maintenance', 'title' => 'Privado', 'description' => 'Segredo', 'occurred_on' => '2026-01-01']);
    Livewire::actingAs($v->user)->test(Details::class, ['vehicle' => $v])->call($action, $entry->id)->assertNotFound();
    expect($entry->fresh())->not->toBeNull();
})->with(['editEntry', 'deleteEntry']);
it('stores reencoded photos and revokes public access immediately', function () {
    Storage::fake('local');
    $v = Vehicle::factory()->create();
    Livewire::actingAs($v->user)->test(Details::class, ['vehicle' => $v])->set('photo', UploadedFile::fake()->image('car.jpg', 100, 100))->call('uploadPhoto')->assertHasNoErrors();
    $photo = $v->photos()->sole();
    Storage::disk('local')->assertExists($photo->path);
    expect(substr(Storage::disk('local')->get($photo->path), 0, 4))->toBe('RIFF');
    $this->get(route('vehicle-photos.show', $photo))->assertOk();
    auth()->logout();
    $this->get(route('vehicle-photos.show', $photo))->assertNotFound();
    $v->update(['visibility' => 'public']);
    $this->get(route('vehicle-photos.show', $photo))->assertOk()->assertHeader('Cache-Control', 'no-store, private');
    $v->update(['visibility' => 'private']);
    $this->get(route('vehicle-photos.show', $photo))->assertNotFound();
});
it('rejects files that are not supported photos', function () {
    Storage::fake('local');
    $v = Vehicle::factory()->create();
    Livewire::actingAs($v->user)->test(Details::class, ['vehicle' => $v])->set('photo', UploadedFile::fake()->create('bad.pdf', 20, 'application/pdf'))->call('uploadPhoto')->assertHasErrors('photo');
    expect($v->photos()->count())->toBe(0);
});
it('rejects oversized photos', function () {
    Storage::fake('local');
    $v = Vehicle::factory()->create();
    Livewire::actingAs($v->user)->test(Details::class, ['vehicle' => $v])->set('photo', UploadedFile::fake()->image('car.jpg')->size(5121))->call('uploadPhoto')->assertHasErrors('photo');
});
it('removes stored photos when the vehicle is deleted', function () {
    Storage::fake('local');
    $v = Vehicle::factory()->create();
    Storage::disk('local')->put('vehicles/test.webp', 'test');
    $v->photos()->create(['path' => 'vehicles/test.webp']);
    $v->delete();
    Storage::disk('local')->assertMissing('vehicles/test.webp');
});
it('does not expose history or costs on public pages', function () {
    $v = Vehicle::factory()->create(['visibility' => 'public']);
    $v->entries()->create(['category' => 'maintenance', 'title' => 'Registro secreto', 'description' => 'Segredo', 'occurred_on' => '2026-01-01', 'cost' => '123.45']);
    $this->get(route('vehicles.show', $v))->assertOk()->assertDontSee('Registro secreto')->assertDontSee('123.45');
});

it('cannot delete another vehicles photo', function () {
    Storage::fake('local');
    $own = Vehicle::factory()->create();
    $other = Vehicle::factory()->create();
    Storage::disk('local')->put('vehicles/other.webp', 'test');
    $photo = $other->photos()->create(['path' => 'vehicles/other.webp']);
    Livewire::actingAs($own->user)->test(Details::class, ['vehicle' => $own])->call('deletePhoto', $photo->id)->assertNotFound();
    Storage::disk('local')->assertExists($photo->path);
});

it('deletes the owners photo and physical file', function () {
    Storage::fake('local');
    $v = Vehicle::factory()->create();
    Storage::disk('local')->put('vehicles/own.webp', 'test');
    $photo = $v->photos()->create(['path' => 'vehicles/own.webp']);
    Livewire::actingAs($v->user)->test(Details::class, ['vehicle' => $v])->call('deletePhoto', $photo->id)->assertHasNoErrors();
    Storage::disk('local')->assertMissing($photo->path);
    expect($photo->fresh())->toBeNull();
});

it('enforces the photo limit', function () {
    Storage::fake('local');
    $v = Vehicle::factory()->create();
    foreach (range(1, 12) as $i) {
        $v->photos()->create(['path' => "vehicles/$i.webp"]);
    }
    Livewire::actingAs($v->user)->test(Details::class, ['vehicle' => $v])->set('photo', UploadedFile::fake()->image('car.jpg'))->call('uploadPhoto')->assertHasErrors('photo');
    expect($v->photos()->count())->toBe(12);
});
