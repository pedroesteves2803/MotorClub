<?php

use App\Models\PhotographerProfile;
use App\Models\ShotGallery;
use App\Models\ShotPhoto;
use App\Models\ShotPurchaseRequest;
use App\Models\ShotReport;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function shotsProfile(User $user, array $data = []): PhotographerProfile
{
    return PhotographerProfile::create($data + ['user_id' => $user->id, 'professional_name' => 'Lente Automotiva', 'description' => 'Fotografia automotiva autoral', 'city' => 'Curitiba', 'state' => 'PR', 'is_public' => true]);
}

function shotsGallery(User $user, array $data = []): ShotGallery
{
    shotsProfile($user);

    return ShotGallery::create($data + ['user_id' => $user->id, 'title' => 'Encontro de domingo', 'description' => 'Galeria de teste', 'city' => 'Curitiba', 'state' => 'PR', 'shot_on' => now()->toDateString(), 'is_public' => true]);
}

it('cria perfil, galeria e prévia sem expor o original', function () {
    Storage::fake('local');
    $photographer = User::factory()->create();
    $this->actingAs($photographer)->put(route('shots.profile.save'), ['professional_name' => 'Pedro Shots', 'description' => 'Carros em movimento', 'city' => 'São Paulo', 'state' => 'SP', 'is_public' => 1])->assertSessionHasNoErrors();
    $this->post(route('shots.galleries.store'), ['title' => 'Pista', 'description' => 'Fotos da pista', 'city' => 'São Paulo', 'state' => 'SP', 'shot_on' => now()->toDateString(), 'is_public' => 1])->assertSessionHasNoErrors();
    $gallery = ShotGallery::sole();
    $this->post(route('shots.photos.upload', $gallery), ['photo' => UploadedFile::fake()->image('carro.jpg', 1800, 1200), 'brand' => 'Volkswagen', 'model' => 'Golf', 'license' => 'Uso pessoal', 'is_published' => 1])->assertSessionHasNoErrors();
    $photo = ShotPhoto::sole();
    Storage::disk('local')->assertExists($photo->original_path)->assertExists($photo->preview_path);
    $this->get(route('shots.photos.preview', $photo))->assertOk()->assertHeader('Content-Type', 'image/webp');
    $previewSize = getimagesize(Storage::disk('local')->path($photo->preview_path));
    expect($photo->original_path)->toContain('shots/originals/')
        ->and($photo->preview_path)->toContain('shots/previews/')
        ->and($photo->uuid)->not->toBeNull()
        ->and($photo->original_sha256)->toHaveLength(64)
        ->and(max($previewSize[0], $previewSize[1]))->toBeLessThanOrEqual(1200)
        ->and($photo->processing_status)->toBe('ready');
});

it('aceita várias fotos na mesma seleção da galeria', function () {
    Storage::fake('local');
    $photographer = User::factory()->create();
    $gallery = shotsGallery($photographer);

    $this->actingAs($photographer)->post(route('shots.photos.upload', $gallery), [
        'photo' => [
            UploadedFile::fake()->image('frente.jpg', 1200, 800),
            UploadedFile::fake()->image('lateral.jpg', 1200, 800),
        ],
        'brand' => 'Volkswagen',
        'model' => 'Golf',
        'license' => 'Uso pessoal',
        'is_published' => 1,
    ])->assertSessionHasNoErrors();

    expect($gallery->photos()->count())->toBe(2);
    $gallery->photos->each(function (ShotPhoto $photo) {
        Storage::disk('local')->assertExists($photo->original_path);
        Storage::disk('local')->assertExists($photo->preview_path);
    });
});

it('mantém galerias privadas e prévias inacessíveis a visitantes', function () {
    Storage::fake('local');
    $gallery = shotsGallery(User::factory()->create(), ['is_public' => false]);
    $photo = $gallery->photos()->create(['original_path' => 'shots/originals/a.jpg', 'preview_path' => 'shots/previews/a.webp', 'is_published' => true, 'license' => 'Uso pessoal']);
    Storage::disk('local')->put($photo->preview_path, 'preview');

    $this->get(route('shots.galleries.show', $gallery))->assertNotFound();
    $this->get(route('shots.photos.preview', $photo))->assertNotFound();
});

it('registra pedido associado ao veículo e calcula a comissão sem pagamento', function () {
    $photographer = User::factory()->create();
    $gallery = shotsGallery($photographer);
    $photo = $gallery->photos()->create(['original_path' => 'shots/originals/a.jpg', 'preview_path' => 'shots/previews/a.webp', 'price' => 100, 'is_published' => true, 'license' => 'Uso pessoal']);
    $vehicle = Vehicle::factory()->create();
    $buyer = $vehicle->user;
    $this->actingAs($buyer)->post(route('shots.requests.store', $photo), ['vehicle_id' => $vehicle->id, 'message' => 'Quero incluir na garagem'])->assertSessionHasNoErrors();
    $purchase = ShotPurchaseRequest::sole();
    expect($purchase->vehicle_id)->toBe($vehicle->id)->and($purchase->status)->toBe('pending');
    $this->actingAs($photographer)->put(route('shots.requests.update', $purchase), ['status' => 'approved', 'quoted_price' => 100])->assertSessionHasNoErrors();
    expect($purchase->fresh()->photographer_amount)->toBe('90.00')->and($purchase->fresh()->platform_amount)->toBe('10.00')->and($purchase->fresh()->authorized_at)->not->toBeNull();
});

it('impede que terceiros gerenciem galeria ou solicitação de outro fotógrafo', function () {
    $gallery = shotsGallery(User::factory()->create());
    $stranger = User::factory()->create();
    $this->actingAs($stranger)->get(route('shots.galleries.edit', $gallery))->assertForbidden();
    $photo = $gallery->photos()->create(['original_path' => 'shots/originals/a.jpg', 'preview_path' => 'shots/previews/a.webp', 'is_published' => true, 'license' => 'Uso pessoal']);
    $purchase = ShotPurchaseRequest::create(['shot_photo_id' => $photo->id, 'buyer_id' => User::factory()->create()->id]);
    $this->actingAs($stranger)->put(route('shots.requests.update', $purchase), ['status' => 'approved', 'quoted_price' => 20])->assertForbidden();
});

it('registra denúncia sem expor ou alterar o arquivo original', function () {
    $gallery = shotsGallery(User::factory()->create());
    $photo = $gallery->photos()->create(['original_path' => 'shots/originals/a.jpg', 'preview_path' => 'shots/previews/a.webp', 'is_published' => true, 'license' => 'Uso pessoal']);
    $this->actingAs(User::factory()->create())->post(route('shots.reports.store', $photo), ['reason' => 'Solicito análise de direito de imagem'])->assertSessionHasNoErrors();

    expect(ShotReport::sole()->status)->toBe('open')->and($photo->fresh()->is_published)->toBeTrue();
});
