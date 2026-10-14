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
    public function edit(Vehicle $vehicle, MagazineService $magazines): Response
    {
        Gate::authorize('update', $vehicle);

        return response()->view('magazine.customize', [
            'vehicle' => $vehicle->load('photos'),
            'settings' => $magazines->settingsFor($vehicle),
        ]);
    }

    public function update(Vehicle $vehicle, MagazineService $magazines): RedirectResponse
    {
        Gate::authorize('update', $vehicle);
        $data = $magazines->validate(request()->all());
        if (($data['cover_photo_id'] ?? null) !== null) {
            abort_unless($vehicle->photos()->whereKey($data['cover_photo_id'])->exists(), 422);
        }
        $magazines->settingsFor($vehicle)->update($data);

        return back()->with('status', 'Personalização da revista salva.');
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
        $project = Project::query()->where('vehicle_id', $vehicle->id)->where('is_public', true)->latest('id')->first();
        abort_unless($project, 404);
        $project->load('photos', 'mediaPhotos', 'modifications.parts.photos', 'modifications.photos', 'vehicle.user', 'vehicle.photos', 'vehicle.entries');

        return response()->view('magazine.public', [
            'vehicle' => $vehicle,
            'project' => $project,
            'magazine' => $presenter->present($project),
            'settings' => $magazines->settingsFor($vehicle),
        ])->header('Cache-Control', 'private, no-store');
    }
}
