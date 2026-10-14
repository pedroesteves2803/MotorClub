<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Vehicle;
use App\Models\VehicleSlugHistory;
use App\Presenters\ProjectMagazinePresenter;
use App\Services\MagazineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class MagazineController extends Controller
{
    public function edit(Vehicle $vehicle, MagazineService $magazines, ProjectMagazinePresenter $presenter): Response
    {
        Gate::authorize('update', $vehicle);

        $vehicle->load('photos');
        $project = $vehicle->projects()
            ->with('photos', 'mediaPhotos', 'modifications.parts.photos', 'modifications.photos', 'vehicle.user', 'vehicle.photos', 'vehicle.entries')
            ->latest('id')
            ->first();

        return response()->view('magazine.customize', [
            'vehicle' => $vehicle,
            'project' => $project,
            'settings' => $settings = $magazines->settingsFor($vehicle),
            'magazine' => $project ? $presenter->present($project, true) : null,
        ]);
    }

    public function update(Vehicle $vehicle, MagazineService $magazines): RedirectResponse
    {
        Gate::authorize('update', $vehicle);
        $data = $magazines->validate(request()->all());
        if (($data['cover_photo_id'] ?? null) !== null) {
            abort_unless($vehicle->photos()->whereKey($data['cover_photo_id'])->exists(), 422);
        }
        if (array_key_exists('highlight_photo_ids', $data)) {
            $allowedPhotoIds = $vehicle->photos()->whereIn('id', $data['highlight_photo_ids'] ?? [])->pluck('id');
            abort_unless($allowedPhotoIds->count() === count($data['highlight_photo_ids'] ?? []), 422);
        }
        $magazines->settingsFor($vehicle)->update($data);

        return back()->with('status', 'Personalização da revista salva.');
    }

    public function showPublicByKey(string $key, ProjectMagazinePresenter $presenter, MagazineService $magazines): Response
    {
        $vehicle = Vehicle::query()->where('public_key', $key)->firstOrFail();

        return $this->renderPublic($vehicle, $presenter, $magazines);
    }

    public function showPublic(string $slug, ProjectMagazinePresenter $presenter, MagazineService $magazines): Response|RedirectResponse
    {
        $vehicle = Vehicle::query()->where('slug', $slug)->first();
        if (! $vehicle) {
            $history = VehicleSlugHistory::query()->with('vehicle')->where('slug', $slug)->first();
            abort_unless($history?->vehicle && $history->vehicle->visibility === 'public', 404);

            return redirect()->route('vehicles.slug.magazine', ['slug' => $history->vehicle->slug], 301);
        }
        abort_unless($vehicle->visibility === 'public', 404);

        return $this->renderPublic($vehicle, $presenter, $magazines);
    }

    private function renderPublic(Vehicle $vehicle, ProjectMagazinePresenter $presenter, MagazineService $magazines): Response
    {
        abort_unless($vehicle->visibility === 'public', 404);
        $project = Project::query()->where('vehicle_id', $vehicle->id)->where('is_public', true)->latest('id')->first();
        abort_unless($project, 404);
        $settings = $magazines->settingsFor($vehicle);
        abort_unless($settings->is_published, 404);
        $project->load('photos', 'mediaPhotos', 'modifications.parts.photos', 'modifications.photos', 'vehicle.user', 'vehicle.photos', 'vehicle.entries');

        return response()->view('magazine.public', [
            'vehicle' => $vehicle,
            'project' => $project,
            'magazine' => $presenter->present($project),
            'settings' => $settings,
        ])->header('Cache-Control', 'private, no-store');
    }
}
