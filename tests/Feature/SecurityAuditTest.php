<?php

use App\Filament\Resources\FeatureSettings\FeatureSettingResource;
use App\Filament\Resources\Users\UserResource;
use App\Livewire\Garage\Index;
use App\Models\CarEvent;
use App\Models\FeatureSetting;
use App\Models\PhotographerProfile;
use App\Models\ShotGallery;
use App\Models\ShotPhoto;
use App\Models\ShotPurchaseRequest;
use App\Models\User;
use App\Services\ShotPaymentManager;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

function auditPhoto(): ShotPhoto
{
    $owner = User::factory()->create();
    PhotographerProfile::create(['user_id' => $owner->id, 'professional_name' => 'Fotógrafo', 'description' => 'Descrição privada', 'city' => 'Curitiba', 'state' => 'PR', 'is_public' => true]);
    $gallery = ShotGallery::create(['user_id' => $owner->id, 'title' => 'Galeria', 'description' => 'Fotos', 'city' => 'Curitiba', 'state' => 'PR', 'shot_on' => now(), 'is_public' => true]);

    return $gallery->photos()->create(['original_path' => 'shots/originals/test.jpg', 'preview_path' => 'shots/previews/test.webp', 'price' => 100, 'is_published' => true]);
}

it('nega ações administrativas de usuários e flags ao moderador', function () {
    $this->actingAs(User::factory()->create(['admin_role' => 'moderator']));
    $user = User::factory()->create();
    $flag = FeatureSetting::create(['feature' => 'shots', 'enabled' => true]);
    foreach ([[UserResource::class, $user], [FeatureSettingResource::class, $flag]] as [$resource, $record]) {
        expect($resource::canCreate())->toBeFalse()
            ->and($resource::canEdit($record))->toBeFalse()
            ->and($resource::canDelete($record))->toBeFalse()
            ->and($resource::canDeleteAny())->toBeFalse();
    }
    $this->get('/admin/usuarios/'.$user->id.'/edit')->assertForbidden();
    $this->get('/admin/funcionalidades/create')->assertForbidden();
    $this->get('/admin/garagens')->assertForbidden();
    $this->get('/admin/galerias')->assertForbidden();
    $this->get('/admin/pagamentos')->assertForbidden();
});

it('preserva a gestão de usuários e flags pelo super admin', function () {
    $this->actingAs(User::factory()->create(['admin_role' => 'super_admin']));
    expect(UserResource::canCreate())->toBeTrue()->and(FeatureSettingResource::canCreate())->toBeTrue();
});

it('impede fotógrafo de sobrescrever decisões administrativas', function (string $status) {
    $photo = auditPhoto();
    $payment = ShotPurchaseRequest::create(['shot_photo_id' => $photo->id, 'buyer_id' => User::factory()->create()->id, 'status' => $status, 'decided_at' => now(), 'quoted_price' => 100]);
    $this->actingAs($photo->gallery->user)->put(route('shots.requests.update', $payment), ['status' => 'approved', 'quoted_price' => 1])->assertStatus(422);
    expect($payment->fresh()->status)->toBe($status)->and($payment->fresh()->quoted_price)->toBe('100.00');
})->with(['approved', 'rejected', 'refunded']);

it('aprova pagamento após orçamento e protege downloads e estornos', function () {
    Storage::fake('local');
    Queue::fake();
    $photo = auditPhoto();
    Storage::disk('local')->put($photo->original_path, 'original privado');
    $buyer = User::factory()->create();
    $admin = User::factory()->create(['admin_role' => 'super_admin']);
    $payment = ShotPurchaseRequest::create(['shot_photo_id' => $photo->id, 'buyer_id' => $buyer->id]);
    $this->actingAs($photo->gallery->user)->put(route('shots.requests.update', $payment), ['status' => 'approved', 'quoted_price' => 100])->assertSessionHasNoErrors();
    $this->actingAs($buyer)->get(route('shots.photos.download', $photo))->assertNotFound();
    $manager = app(ShotPaymentManager::class);
    $manager->approve($payment->fresh(), $admin);
    $this->get(route('shots.photos.download', $photo))->assertOk()->assertHeader('Cache-Control', 'no-store, private');
    $this->actingAs(User::factory()->create())->get(route('shots.photos.download', $photo))->assertNotFound();
    expect(fn () => $manager->approve($payment->fresh(), $admin))->toThrow(ValidationException::class);
    expect($payment->grants()->count())->toBe(1);
    $manager->refund($payment->fresh(), $admin, 'Solicitação do comprador');
    $this->actingAs($buyer)->get(route('shots.photos.download', $photo))->assertNotFound();
    $this->get(route('shots.requests'))->assertOk();
    expect(fn () => $manager->refund($payment->fresh(), $admin, 'Repetido'))->toThrow(ValidationException::class);
});

it('nega decisões financeiras ao moderador', function () {
    $photo = auditPhoto();
    $payment = ShotPurchaseRequest::create(['shot_photo_id' => $photo->id, 'buyer_id' => User::factory()->create()->id]);
    expect(fn () => app(ShotPaymentManager::class)->approve($payment, User::factory()->create(['admin_role' => 'moderator'])))->toThrow(ValidationException::class);
    expect($payment->fresh()->status)->toBe('pending')->and($payment->grants()->count())->toBe(0);
});

it('remove os originais privados ao excluir a conta do fotógrafo', function () {
    Storage::fake('local');
    $photo = auditPhoto();
    Storage::disk('local')->put($photo->original_path, 'original');
    Storage::disk('local')->put($photo->preview_path, 'preview');
    $photo->gallery->user->delete();
    Storage::disk('local')->assertMissing($photo->original_path)->assertMissing($photo->preview_path);
});

it('bloqueia componente Livewire de garagem já aberto após desativação', function () {
    $component = Livewire::actingAs(User::factory()->create())->test(Index::class);
    FeatureSetting::create(['feature' => 'garage', 'enabled' => false]);
    $component->call('create')->assertSet('formOpen', false);
    $this->get(route('dashboard'))->assertNotFound();
});

it('não divulga perfil de fotógrafo privado através do perfil público', function () {
    $photo = auditPhoto();
    $owner = $photo->gallery->user;
    $owner->forceFill(['profile_public' => true])->save();
    $owner->photographerProfile->update(['is_public' => false]);
    $this->get(route('profiles.show', $owner))->assertOk()->assertDontSee('Descrição privada');
});

it('não divulga evento privado vinculado a galeria pública', function () {
    $photo = auditPhoto();
    $event = CarEvent::create(['user_id' => $photo->gallery->user_id, 'title' => 'Evento confidencial', 'category' => 'meet', 'starts_at' => now()->addDay(), 'city' => 'Curitiba', 'state' => 'PR', 'venue' => 'Local privado', 'organizer' => 'Organizador', 'description' => 'Privado', 'is_public' => false]);
    $photo->gallery->update(['car_event_id' => $event->id]);
    $this->get(route('shots.galleries.show', $photo->gallery))->assertOk()->assertDontSee('Evento confidencial');
});
