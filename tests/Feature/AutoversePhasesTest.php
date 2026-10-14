<?php

use App\Models\PhotographerProfile;
use App\Models\Project;
use App\Models\ShotGallery;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleShotAsset;
use App\Services\VehicleSlugService;
use Illuminate\Support\Facades\Storage;

function phaseProject(Vehicle $vehicle, bool $public = true): Project
{
    return $vehicle->projects()->create([
        'title' => 'Projeto de evolução',
        'category' => 'performance',
        'status' => 'ongoing',
        'description' => 'História pública do projeto.',
        'parts' => null,
        'services' => 'Próxima revisão documentada.',
        'cost' => null,
        'show_cost' => false,
        'is_public' => $public,
        'workshop_status' => 'pending',
    ]);
}

it('monta evolução pública somente com modificações e registros públicos', function () {
    $vehicle = Vehicle::factory()->create(['visibility' => 'public']);
    $project = phaseProject($vehicle);
    $public = $project->modifications()->create(['title' => 'Suspensão documentada', 'status' => 'completed', 'description' => 'Instalação concluída.', 'is_public' => true, 'sort_order' => 1]);
    $project->modifications()->create(['title' => 'Plano privado', 'status' => 'planned', 'description' => 'Ainda não publicado.', 'is_public' => false, 'sort_order' => 2]);

    $this->get(route('vehicles.slug.evolution', ['slug' => app(VehicleSlugService::class)->ensure($vehicle)]))
        ->assertOk()->assertSee('Suspensão documentada')->assertDontSee('Plano privado');
    expect($public->is_public)->toBeTrue();
});

it('salva personalização da revista e abre a leitura pública', function () {
    $vehicle = Vehicle::factory()->create(['visibility' => 'public']);
    phaseProject($vehicle);
    $this->actingAs($vehicle->user)->put(route('garage.magazine.update', $vehicle), [
        'primary_color' => '#123456',
        'theme' => 'light',
        'editorial_style' => 'minimal',
    ])->assertRedirect();

    expect($vehicle->fresh()->magazineSetting->primary_color)->toBe('#123456');
    $slug = app(VehicleSlugService::class)->ensure($vehicle->fresh());
    $this->get(route('vehicles.slug.magazine', ['slug' => $slug]))->assertOk()->assertSee('Projeto de evolução');
});

it('gera QR Code público e downloads autenticados sem expor o ID na URL', function () {
    $vehicle = Vehicle::factory()->create(['visibility' => 'public']);
    $slug = app(VehicleSlugService::class)->ensure($vehicle);

    $this->get(route('vehicles.slug.qr', ['slug' => $slug]))->assertOk()->assertHeader('Content-Type', 'image/svg+xml; charset=UTF-8')->assertSee('<svg', false);
    $this->actingAs($vehicle->user)->get(route('garage.share.qr.download', [$vehicle, 'format' => 'png']))->assertOk()->assertHeader('Content-Type', 'image/png')->assertHeader('Content-Disposition');
});

it('não expõe localização privada na página pública do veículo', function () {
    $vehicle = Vehicle::factory()->create(['visibility' => 'public', 'location' => 'Rua privada, 123']);
    $slug = app(VehicleSlugService::class)->ensure($vehicle);

    $this->get(route('vehicles.slug.show', ['slug' => $slug]))
        ->assertOk()
        ->assertDontSee('Rua privada, 123');
});

it('lista somente peças públicas em projetos públicos', function () {
    $publicVehicle = Vehicle::factory()->create(['visibility' => 'public']);
    $publicProject = phaseProject($publicVehicle);
    $modification = $publicProject->modifications()->create(['title' => 'Freios', 'status' => 'completed', 'is_public' => true, 'sort_order' => 1]);
    $modification->parts()->create(['name' => 'Pastilha cerâmica', 'brand' => 'Brembo', 'model' => 'X', 'status' => 'installed', 'is_public' => true, 'sort_order' => 1]);
    $modification->parts()->create(['name' => 'Peça privada', 'status' => 'planned', 'is_public' => false, 'sort_order' => 2]);

    $this->get(route('explore.parts'))->assertOk()->assertSee('Pastilha cerâmica')->assertDontSee('Peça privada');
    $this->get(route('explore.projects'))->assertOk()->assertSee('Projeto de evolução');
});

it('associa foto gratuita licenciada do Shots e preserva o crédito', function () {
    Storage::fake('local');
    $owner = User::factory()->create();
    $photographer = User::factory()->create();
    PhotographerProfile::create(['user_id' => $photographer->id, 'professional_name' => 'Foto Studio', 'description' => 'Perfil', 'city' => 'São Paulo', 'state' => 'SP', 'is_public' => true]);
    $gallery = ShotGallery::create(['user_id' => $photographer->id, 'title' => 'Encontro', 'description' => 'Fotos', 'city' => 'São Paulo', 'state' => 'SP', 'shot_on' => now()->toDateString(), 'is_public' => true]);
    $photo = $gallery->photos()->create(['original_path' => 'shots/original.webp', 'preview_path' => 'shots/preview.webp', 'license' => 'Uso pessoal na MotorClub', 'price' => null, 'is_published' => true]);
    $vehicle = Vehicle::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->post(route('shots.photos.attach', $photo), ['vehicle_id' => $vehicle->id, 'is_public' => true])->assertRedirect();
    expect(VehicleShotAsset::where('vehicle_id', $vehicle->id)->where('shot_photo_id', $photo->id)->value('credit'))->toBe('Foto Studio');
});
