<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleSlugHistory;
use App\Services\EvolutionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class VehicleEvolutionController extends Controller
{
    public function owner(Vehicle $vehicle, EvolutionService $evolution): Response
    {
        Gate::authorize('update', $vehicle);
        $project = $vehicle->projects()->latest('id')->first();

        return response()->view('vehicles.evolution', [
            'vehicle' => $vehicle->load('photos'),
            'project' => $project,
            'stages' => $project ? $evolution->stages($project, true) : [],
            'public' => false,
        ]);
    }

    public function showPublic(string $slug, EvolutionService $evolution): Response|RedirectResponse
    {
        $vehicle = Vehicle::query()->where('slug', $slug)->first();
        if (! $vehicle) {
            $history = VehicleSlugHistory::query()->with('vehicle')->where('slug', $slug)->first();
            abort_unless($history?->vehicle && $history->vehicle->visibility === 'public', 404);

            return redirect()->route('vehicles.slug.evolution', ['slug' => $history->vehicle->slug], 301);
        }
        abort_unless($vehicle->visibility === 'public', 404);
        $project = $vehicle->projects()->where('is_public', true)->latest('id')->first();
        abort_unless($project, 404);

        return response()->view('vehicles.evolution', [
            'vehicle' => $vehicle->load('photos'),
            'project' => $project,
            'stages' => $evolution->stages($project),
            'public' => true,
        ])->header('Cache-Control', 'private, no-store');
    }
}
