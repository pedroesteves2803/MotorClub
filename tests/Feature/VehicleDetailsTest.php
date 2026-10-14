<?php

use App\Livewire\Garage\Details;
use App\Models\MediaPhoto;
use App\Models\ProjectModification;
use App\Models\ProjectPart;
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

it('creates a quick modification with an optional photo and keeps its planned status', function () {
    Storage::fake('local');
    $v = Vehicle::factory()->create();

    Livewire::actingAs($v->user)->test(Details::class, ['vehicle' => $v])
        ->set('quick_modification_title', 'Rodas forjadas')
        ->set('quick_modification_status', 'planned')
        ->set('quick_modification_photos', [UploadedFile::fake()->image('rodas.jpg')])
        ->call('saveQuickModification')
        ->assertHasNoErrors();

    $modification = ProjectModification::sole();
    expect($modification->title)->toBe('Rodas forjadas')
        ->and($modification->status)->toBe('planned')
        ->and(MediaPhoto::where('imageable_type', ProjectModification::class)->count())->toBe(1);
});

it('adds a minimal optional-detail part to an existing modification', function () {
    $v = Vehicle::factory()->create();
    $project = $v->projects()->create(['title' => 'Projeto principal', 'category' => 'maintenance', 'status' => 'planning', 'description' => 'Registro']);
    $modification = $project->modifications()->create(['title' => 'Freios', 'category' => 'outro', 'status' => 'planned']);

    Livewire::actingAs($v->user)->test(Details::class, ['vehicle' => $v])
        ->set('quick_part_modification_id', $modification->id)
        ->set('quick_part_name', 'Pastilha cerâmica')
        ->set('quick_part_status', 'planned')
        ->call('saveQuickPart')
        ->assertHasNoErrors();

    $part = ProjectPart::sole();
    expect($part->name)->toBe('Pastilha cerâmica')
        ->and($part->status)->toBe('planned')
        ->and($part->brand)->toBeNull();
});

it('routes maintenance photos to a maintenance entry without orphaning files', function () {
    Storage::fake('local');
    $v = Vehicle::factory()->create();

    Livewire::actingAs($v->user)->test(Details::class, ['vehicle' => $v])
        ->set('photo_context', 'maintenance')
        ->set('photo_context_title', 'Troca de óleo')
        ->set('photo_context_description', 'Óleo e filtro substituídos.')
        ->set('photos', [UploadedFile::fake()->image('oleo.jpg')])
        ->call('uploadPhoto')
        ->assertHasNoErrors();

    $entry = $v->entries()->sole();
    expect($entry->photos()->count())->toBe(1);
    Storage::disk('local')->assertExists($entry->photos()->sole()->path);
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
    $entry = $v->entries()->create(['category' => 'maintenance', 'title' => 'Troca', 'description' => 'Óleo']);
    Storage::disk('local')->put('media/entry.webp', 'entry');
    $entry->photos()->create(['path' => 'media/entry.webp', 'kind' => 'galeria']);
    $v->delete();
    Storage::disk('local')->assertMissing('vehicles/test.webp');
    Storage::disk('local')->assertMissing('media/entry.webp');
});
it('exposes only explicitly public history on public pages', function () {
    $v = Vehicle::factory()->create(['visibility' => 'public']);
    $v->entries()->create(['category' => 'maintenance', 'title' => 'Registro secreto', 'description' => 'Segredo', 'occurred_on' => '2026-01-01', 'cost' => '123.45', 'is_public' => false]);
    $v->entries()->create(['category' => 'maintenance', 'title' => 'Registro público', 'description' => 'Informação compartilhada', 'occurred_on' => '2026-01-02', 'is_public' => true]);
    $this->get(route('vehicles.show', $v))->assertOk()->assertSee('Registro público')->assertDontSee('Registro secreto')->assertDontSee('123.45');
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

it('saves valid photos when one item in a batch fails', function () {
    Storage::fake('local');
    $v = Vehicle::factory()->create();

    Livewire::actingAs($v->user)->test(Details::class, ['vehicle' => $v])
        ->set('photos', [
            UploadedFile::fake()->image('car-ok.jpg'),
            UploadedFile::fake()->create('car-bad.pdf', 20, 'application/pdf'),
        ])
        ->call('uploadPhoto')
        ->assertHasErrors('photos.1');

    expect($v->photos()->count())->toBe(1);
    Storage::disk('local')->assertCount('vehicles', 1);
});

it('sets a cover and persists the visual order of the vehicle library', function () {
    Storage::fake('local');
    $v = Vehicle::factory()->create();
    $photos = collect(['one.webp', 'two.webp', 'three.webp'])->map(function ($name, $index) use ($v) {
        Storage::disk('local')->put("vehicles/{$name}", 'image');

        return $v->photos()->create([
            'path' => "vehicles/{$name}",
            'sort_order' => $index,
            'is_primary' => $index === 0,
        ]);
    });

    $component = Livewire::actingAs($v->user)->test(Details::class, ['vehicle' => $v]);
    $component->call('setPrimaryPhoto', $photos[2]->id)->assertHasNoErrors();
    $component->call('reorderPhotos', [$photos[2]->id, $photos[0]->id, $photos[1]->id])->assertHasNoErrors();

    expect($v->photos()->where('is_primary', true)->value('id'))->toBe($photos[2]->id)
        ->and($v->photos()->orderBy('sort_order')->pluck('id')->all())->toBe([$photos[2]->id, $photos[0]->id, $photos[1]->id]);
});

it('reuses vehicle library photos in a project part without sharing a deletable path', function () {
    Storage::fake('local');
    $v = Vehicle::factory()->create();
    Storage::disk('local')->put('vehicles/library.webp', 'image');
    $source = $v->photos()->create(['path' => 'vehicles/library.webp', 'is_primary' => true]);
    $project = $v->projects()->create(['title' => 'Projeto', 'category' => 'maintenance', 'status' => 'planning', 'description' => 'Registro']);
    $modification = $project->modifications()->create(['title' => 'Freios', 'category' => 'outro', 'status' => 'planned']);
    $part = $modification->parts()->create(['name' => 'Pastilha', 'status' => 'installed']);

    Livewire::actingAs($v->user)->test(Details::class, ['vehicle' => $v])
        ->set('photo_context', 'existing_part')
        ->set('photo_part_id', $part->id)
        ->set('reuse_photo_ids', [$source->id])
        ->call('reuseLibraryPhotos')
        ->assertHasNoErrors();

    $media = $part->photos()->sole();
    expect($media->path)->not->toBe($source->path);
    Storage::disk('local')->assertExists($source->path);
    Storage::disk('local')->assertExists($media->path);
});

it('can place a fresh upload in the main project media library', function () {
    Storage::fake('local');
    $v = Vehicle::factory()->create();
    $project = $v->projects()->create(['title' => 'Projeto principal', 'category' => 'maintenance', 'status' => 'planning', 'description' => 'Registro']);

    Livewire::actingAs($v->user)->test(Details::class, ['vehicle' => $v])
        ->set('photo_context', 'project')
        ->set('photos', [UploadedFile::fake()->image('project.jpg')])
        ->call('uploadPhoto')
        ->assertHasNoErrors();

    expect($project->mediaPhotos()->count())->toBe(1);
    Storage::disk('local')->assertExists($project->mediaPhotos()->sole()->path);
});
