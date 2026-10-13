<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\DemoCommunityController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MockBillingController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ShotsController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\WorkshopController;
use App\Livewire\Garage\Details;
use App\Livewire\Garage\Index;
use App\Models\ShotDownloadGrant;
use App\Models\ShotPhoto;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

Route::get('/', fn () => view('welcome'))->name('home');
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

Route::get('veiculos/{vehicle}', function (Vehicle $vehicle) {
    abort_unless($vehicle->visibility === 'public', 404);

    return response()->view('vehicle', compact('vehicle'))->header('Cache-Control', 'private, no-store');
})->middleware('feature:garage')->name('vehicles.show');
Route::get('garagem/{vehicle}', Details::class)->middleware(['auth', 'verified', 'feature:garage'])->name('garage.details');
Route::get('fotos/{photo}', function (VehiclePhoto $photo) {
    abort_unless(Gate::allows('view', $photo->vehicle), 404);

    return Storage::disk(config('filesystems.private_disk'))->response($photo->path, null, ['Content-Type' => 'image/webp', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
})->middleware('feature:garage')->name('vehicle-photos.show');

Route::get('explorar', [DemoCommunityController::class, 'index'])->middleware('feature:explore')->name('explore');
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
    Route::get('projetos/novo', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('projetos', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('projetos/{project}/editar', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('projetos/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('projetos/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('projetos/{project}/fotos', [ProjectController::class, 'photo'])->middleware('throttle:20,1')->name('projects.photo');
    Route::delete('fotos-projetos/{photo}', [ProjectController::class, 'deletePhoto'])->name('projects.photo.delete');
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
    Route::post('shots/fotos/{photo}/denunciar', [ShotsController::class, 'report'])->middleware('throttle:5,1')->name('shots.reports.store');
    Route::get('shots/solicitacoes', [ShotsController::class, 'requests'])->name('shots.requests');
    Route::put('shots/solicitacoes/{purchase}', [ShotsController::class, 'updateRequest'])->name('shots.requests.update');
});

Route::get('projetos/{project}', [ProjectController::class, 'show'])->middleware('feature:garage')->name('projects.show');
Route::get('projetos/{project}/relatorios/{report}', [ProjectController::class, 'showReport'])->middleware('feature:ai_reports')->name('projects.reports.show');
Route::get('fotos-projetos/{photo}', [ProjectController::class, 'image'])->middleware('feature:garage')->name('projects.image');
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
