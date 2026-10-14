<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\DemoCommunityController;
use App\Http\Controllers\DiscoveryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MagazineController;
use App\Http\Controllers\MockBillingController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\PublicVehicleController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\ShotsController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\VehicleEvolutionController;
use App\Http\Controllers\VehicleShareController;
use App\Http\Controllers\WorkshopController;
use App\Livewire\Garage\Details;
use App\Livewire\Garage\Index;
use App\Models\MediaPhoto;
use App\Models\ShotDownloadGrant;
use App\Models\ShotPhoto;
use App\Models\VehicleEntry;
use App\Models\VehiclePhoto;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

Route::get('/', fn () => view('welcome'))->name('home');
Route::redirect('central-mc-gestao', '/admin', 301)->name('admin.legacy');
Route::get('robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('garagem', Index::class)->middleware(['auth', 'verified', 'feature:garage'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::permanentRedirect('settings', 'configuracoes/perfil');
    Route::permanentRedirect('settings/profile', 'configuracoes/perfil');
    Route::permanentRedirect('settings/password', 'configuracoes/perfil');
    Route::permanentRedirect('settings/appearance', 'configuracoes/aparencia');
    Volt::route('configuracoes/perfil', 'settings.profile')->name('settings.profile');
    Route::redirect('configuracoes/senha', '/configuracoes/perfil')->name('settings.password');
    Volt::route('configuracoes/aparencia', 'settings.appearance')->name('settings.appearance');
});
require __DIR__.'/auth.php';

Route::get('veiculos/{slug}', [PublicVehicleController::class, 'showBySlug'])->where('slug', '[a-z][a-z0-9-]*')->middleware('feature:garage')->name('vehicles.slug.show');
Route::get('v/{key}', [PublicVehicleController::class, 'showByKey'])->where('key', '[a-z0-9-]+')->middleware('feature:garage')->name('vehicles.public.show');
Route::get('v/{key}/revista', [MagazineController::class, 'showPublicByKey'])->where('key', '[a-z0-9-]+')->middleware('feature:garage')->name('vehicles.public.magazine');
Route::get('veiculos/{slug}/evolucao', [VehicleEvolutionController::class, 'showPublic'])->where('slug', '[a-z][a-z0-9-]*')->middleware('feature:garage')->name('vehicles.slug.evolution');
Route::get('veiculos/{slug}/revista', [MagazineController::class, 'showPublic'])->where('slug', '[a-z][a-z0-9-]*')->middleware('feature:garage')->name('vehicles.slug.magazine');
Route::get('veiculos/{slug}/revista/leitura', [MagazineController::class, 'showPublic'])->where('slug', '[a-z][a-z0-9-]*')->middleware('feature:garage')->name('vehicles.slug.magazine.reading');
Route::get('veiculos/{slug}/qr-code', [VehicleShareController::class, 'publicQr'])->where('slug', '[a-z][a-z0-9-]*')->middleware('feature:garage')->name('vehicles.slug.qr');
Route::get('veiculos/{vehicle}', [PublicVehicleController::class, 'showById'])->whereNumber('vehicle')->middleware('feature:garage')->name('vehicles.show');
Route::get('garagem/{vehicle}', Details::class)->whereNumber('vehicle')->middleware(['auth', 'verified', 'feature:garage'])->name('garage.details');
Route::get('garagem/novo', fn () => redirect()->to('/garagem?novo=1'))->middleware(['auth', 'verified', 'feature:garage'])->name('garage.create');
Route::get('garagem/{slug}', [PublicVehicleController::class, 'showBySlug'])->where('slug', '[a-z][a-z0-9-]*')->middleware('feature:garage')->name('public.garage.show');
Route::get('garagem/{slug}/evolucao', [VehicleEvolutionController::class, 'showPublic'])->where('slug', '[a-z][a-z0-9-]*')->middleware('feature:garage')->name('public.garage.evolution');
Route::get('garagem/{slug}/revista', [MagazineController::class, 'showPublic'])->where('slug', '[a-z][a-z0-9-]*')->middleware('feature:garage')->name('public.garage.magazine');
Route::get('garagem/{slug}/revista/leitura', [MagazineController::class, 'showPublic'])->where('slug', '[a-z][a-z0-9-]*')->middleware('feature:garage')->name('public.garage.magazine.reading');
Route::get('garagem/{slug}/fotos', [PublicVehicleController::class, 'showBySlug'])->where('slug', '[a-z][a-z0-9-]*')->middleware('feature:garage')->name('public.garage.photos');
Route::get('garagem/{slug}/qr-code', [VehicleShareController::class, 'publicQr'])->where('slug', '[a-z][a-z0-9-]*')->middleware('feature:garage')->name('public.garage.qr');
Route::get('fotos/{photo}', function (VehiclePhoto $photo) {
    abort_unless(Gate::allows('view', $photo->vehicle), 404);
    abort_unless(Storage::disk(config('filesystems.private_disk'))->exists($photo->path), 404);

    return Storage::disk(config('filesystems.private_disk'))->response($photo->path, null, ['Content-Type' => 'image/webp', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
})->middleware('feature:garage')->name('vehicle-photos.show');

Route::get('explorar', [DemoCommunityController::class, 'index'])->middleware('feature:explore')->name('explore');
Route::get('explorar/projetos', [DiscoveryController::class, 'projects'])->middleware('feature:explore')->name('explore.projects');
Route::get('explorar/pecas', [DiscoveryController::class, 'parts'])->middleware('feature:explore')->name('explore.parts');
Route::get('explorar/pecas/{part}', [DiscoveryController::class, 'part'])->middleware('feature:explore')->name('explore.parts.show');
Route::get('comunidade', [DemoCommunityController::class, 'index'])->middleware('feature:explore')->name('community');
Route::get('comunidade/garagens/{slug}', [DemoCommunityController::class, 'garage'])->middleware(['feature:explore', 'feature:demo'])->name('community.garage');

Route::get('shots', [ShotsController::class, 'index'])->middleware('feature:shots')->name('shots.index');
Route::get('shots/galerias/{gallery}', [ShotsController::class, 'show'])->middleware('feature:shots')->name('shots.galleries.show');
Route::get('shots/fotos/{photo}/previa', [ShotsController::class, 'preview'])->middleware('feature:shots')->name('shots.photos.preview');
Route::get('shots/fotos/{photo}/download', function (ShotPhoto $photo) {
    $grant = ShotDownloadGrant::query()
        ->where('shot_photo_id', $photo->id)
        ->whereNull('revoked_at')
        ->whereHas('payment', fn ($query) => $query->where('buyer_id', auth()->id())->where('status', 'approved'))
        ->first();
    abort_unless($grant, 404);

    return Storage::disk(config('filesystems.private_disk'))->response($photo->original_path, null, ['Content-Type' => $photo->mime_type ?? 'application/octet-stream', 'Cache-Control' => 'private, no-store']);
})->middleware(['auth', 'feature:shots'])->name('shots.photos.download');
Route::get('projetos', [ProjectController::class, 'index'])->middleware('feature:garage')->name('projects.index');
Route::get('oficinas', [WorkshopController::class, 'index'])->middleware('feature:workshops')->name('workshops.index');
Route::get('eventos', [EventController::class, 'index'])->middleware('feature:events')->name('events.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('meu-perfil', [PublicProfileController::class, 'edit'])->name('profiles.edit');
    Route::put('meu-perfil', [PublicProfileController::class, 'update'])->name('profiles.update');
});
Route::middleware(['auth', 'verified', 'feature:garage'])->group(function () {
    Route::get('garagem/{vehicle}/evolucao', [VehicleEvolutionController::class, 'owner'])->whereNumber('vehicle')->name('garage.evolution');
    Route::get('garagem/{vehicle}/revista/personalizar', [MagazineController::class, 'edit'])->whereNumber('vehicle')->name('garage.magazine.edit');
    Route::put('garagem/{vehicle}/revista/personalizar', [MagazineController::class, 'update'])->whereNumber('vehicle')->name('garage.magazine.update');
    Route::get('garagem/{vehicle}/compartilhar', [VehicleShareController::class, 'edit'])->whereNumber('vehicle')->name('garage.share.edit');
    Route::post('garagem/{vehicle}/slug', [VehicleShareController::class, 'updateSlug'])->whereNumber('vehicle')->name('garage.share.slug');
    Route::put('garagem/{vehicle}/qr-code/settings', [VehicleShareController::class, 'updateQrSettings'])->whereNumber('vehicle')->name('garage.share.qr.settings');
    Route::get('garagem/{vehicle}/qr-code', [VehicleShareController::class, 'qrCode'])->whereNumber('vehicle')->name('garage.share.qr');
    Route::get('garagem/{vehicle}/qr-code/download', [VehicleShareController::class, 'download'])->whereNumber('vehicle')->name('garage.share.qr.download');
    Route::get('projetos/novo', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('projetos', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('projetos/{project}/editar', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('projetos/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('projetos/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('projetos/{project}/fotos', [ProjectController::class, 'photo'])->middleware('throttle:20,1')->name('projects.photo');
    Route::delete('fotos-projetos/{photo}', [ProjectController::class, 'deletePhoto'])->name('projects.photo.delete');
    Route::put('fotos-projetos/{photo}', [ProjectController::class, 'updatePhoto'])->name('projects.photo.update');
    Route::post('projetos/{project}/modificacoes', [ProjectController::class, 'storeModification'])->name('projects.modifications.store');
    Route::put('projetos/{project}/modificacoes/{modification}', [ProjectController::class, 'updateModification'])->name('projects.modifications.update');
    Route::delete('projetos/{project}/modificacoes/{modification}', [ProjectController::class, 'deleteModification'])->name('projects.modifications.destroy');
    Route::post('projetos/{project}/modificacoes/{modification}/pecas', [ProjectController::class, 'storePart'])->name('projects.parts.store');
    Route::put('projetos/{project}/modificacoes/{modification}/pecas/{part}', [ProjectController::class, 'updatePart'])->name('projects.parts.update');
    Route::delete('projetos/{project}/modificacoes/{modification}/pecas/{part}', [ProjectController::class, 'deletePart'])->name('projects.parts.destroy');
    Route::post('projetos/{project}/modificacoes/{modification}/fotos', [ProjectController::class, 'modificationPhoto'])->middleware('throttle:20,1')->name('projects.modifications.photos.store');
    Route::post('projetos/{project}/modificacoes/{modification}/pecas/{part}/fotos', [ProjectController::class, 'partPhoto'])->middleware('throttle:20,1')->name('projects.parts.photos.store');
    Route::put('midias-projetos/{photo}', [ProjectController::class, 'updateMedia'])->name('projects.media.update');
    Route::delete('midias-projetos/{photo}', [ProjectController::class, 'deleteMedia'])->name('projects.media.destroy');
});
Route::middleware(['auth', 'verified', 'feature:ai_reports'])->group(function () {
    Route::post('projetos/{project}/relatorios', [ProjectController::class, 'generateReport'])->name('projects.reports.generate');
    Route::get('projetos/{project}/relatorios/{report}/editar', [ProjectController::class, 'editReport'])->name('projects.reports.edit');
    Route::put('projetos/{project}/relatorios/{report}', [ProjectController::class, 'updateReport'])->name('projects.reports.update');
});
Route::middleware(['auth', 'verified', 'feature:workshops'])->group(function () {
    Route::get('minha-oficina', [WorkshopController::class, 'edit'])->name('workshops.edit');
    Route::post('minha-oficina', [WorkshopController::class, 'save'])->name('workshops.save');
    Route::delete('minha-oficina', [WorkshopController::class, 'destroy'])->name('workshops.destroy');
    Route::post('vinculos/{project}', [WorkshopController::class, 'association'])->name('workshops.association');
    Route::get('orcamentos', [QuoteController::class, 'index'])->name('quotes.index');
    Route::post('oficinas/{workshop}/orcamentos', [QuoteController::class, 'store'])->middleware('throttle:10,1')->name('quotes.store');
    Route::put('orcamentos/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
});
Route::middleware(['auth', 'verified', 'feature:events'])->group(function () {
    Route::get('eventos/novo', [EventController::class, 'create'])->name('events.create');
    Route::post('eventos', [EventController::class, 'store'])->name('events.store');
    Route::get('eventos/{event}/editar', [EventController::class, 'edit'])->name('events.edit');
    Route::put('eventos/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('eventos/{event}', [EventController::class, 'destroy'])->name('events.destroy');
});
Route::middleware(['auth', 'verified', 'feature:shots'])->group(function () {
    Route::get('meus-shots/perfil', [ShotsController::class, 'profile'])->name('shots.profile');
    Route::put('meus-shots/perfil', [ShotsController::class, 'saveProfile'])->name('shots.profile.save');
    Route::get('meus-shots', [ShotsController::class, 'myGalleries'])->name('shots.manage');
    Route::get('meus-shots/galerias/nova', [ShotsController::class, 'createGallery'])->name('shots.galleries.create');
    Route::post('meus-shots/galerias', [ShotsController::class, 'storeGallery'])->name('shots.galleries.store');
    Route::get('meus-shots/galerias/{gallery}/editar', [ShotsController::class, 'editGallery'])->name('shots.galleries.edit');
    Route::put('meus-shots/galerias/{gallery}', [ShotsController::class, 'updateGallery'])->name('shots.galleries.update');
    Route::delete('meus-shots/galerias/{gallery}', [ShotsController::class, 'destroyGallery'])->name('shots.galleries.destroy');
    Route::post('meus-shots/galerias/{gallery}/fotos', [ShotsController::class, 'uploadPhoto'])->middleware('throttle:20,1')->name('shots.photos.upload');
    Route::delete('meus-shots/fotos/{photo}', [ShotsController::class, 'deletePhoto'])->name('shots.photos.destroy');
    Route::post('meus-shots/fotos/{photo}/substituir', [ShotsController::class, 'replacePhoto'])->middleware('throttle:20,1')->name('shots.photos.replace');
    Route::post('shots/fotos/{photo}/solicitar', [ShotsController::class, 'requestPurchase'])->middleware('throttle:10,1')->name('shots.requests.store');
    Route::post('shots/fotos/{photo}/adicionar-a-veiculo', [ShotsController::class, 'attachToVehicle'])->middleware('throttle:10,1')->name('shots.photos.attach');
    Route::post('shots/fotos/{photo}/denunciar', [ShotsController::class, 'report'])->middleware('throttle:5,1')->name('shots.reports.store');
    Route::get('shots/solicitacoes', [ShotsController::class, 'requests'])->name('shots.requests');
    Route::put('shots/solicitacoes/{purchase}', [ShotsController::class, 'updateRequest'])->name('shots.requests.update');
});

Route::get('projetos/{project}', [ProjectController::class, 'show'])->middleware('feature:garage')->name('projects.show');
Route::get('projetos/{project}/modificacoes/{modification}', [ProjectController::class, 'showModification'])->middleware('feature:garage')->name('projects.modifications.show');
Route::get('projetos/{project}/modificacoes/{modification}/pecas/{part}', [ProjectController::class, 'showPart'])->middleware('feature:garage')->name('projects.parts.show');
Route::get('projetos/{project}/relatorios/{report}', [ProjectController::class, 'showReport'])->middleware('feature:ai_reports')->name('projects.reports.show');
Route::get('fotos-projetos/{photo}', [ProjectController::class, 'image'])->middleware('feature:garage')->name('projects.image');
Route::get('midias-projetos/{photo}', [ProjectController::class, 'mediaImage'])->middleware('feature:garage')->name('projects.media.image');
Route::get('midias-historico/{photo}', function (MediaPhoto $photo) {
    $entry = $photo->imageable;
    abort_unless($entry instanceof VehicleEntry && Gate::allows('update', $entry->vehicle), 404);
    abort_unless(Storage::disk(config('filesystems.private_disk'))->exists($photo->path), 404);

    return Storage::disk(config('filesystems.private_disk'))->response($photo->path, null, ['Content-Type' => 'image/webp', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
})->middleware('feature:garage')->name('vehicle-entry-media.show');
Route::get('oficinas/{workshop}', [WorkshopController::class, 'show'])->middleware('feature:workshops')->name('workshops.show');
Route::get('eventos/{event}', [EventController::class, 'show'])->middleware('feature:events')->name('events.show');
Route::get('pessoas/{person}', [PublicProfileController::class, 'show'])->middleware('feature:explore')->name('profiles.show');

Route::get('como-testar', fn () => abort_unless(config('production.test_mode'), 404) ?: view('testing-guide'))->middleware('feature:demo')->name('testing.guide');
Route::get('plano-oficina', [BillingController::class, 'index'])->middleware('feature:subscriptions')->name('billing.index');
Route::middleware(['auth', 'verified', 'throttle:10,1', 'feature:subscriptions'])->group(function () {
    Route::post('assinatura/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('assinatura/portal', [BillingController::class, 'portal'])->name('billing.portal');
});
Route::post('stripe/webhook', StripeWebhookController::class)->middleware('feature:subscriptions')->name('stripe.webhook');
Route::post('assinatura/simular', [MockBillingController::class, 'action'])->middleware(['auth', 'verified', 'throttle:30,1', 'feature:subscriptions'])->name('billing.mock');
