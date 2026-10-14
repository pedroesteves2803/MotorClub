<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleSlugHistory;
use App\Services\VehicleSlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class PublicVehicleController extends Controller
{
    public function showById(Vehicle $vehicle, VehicleSlugService $slugs): Response
    {
        $slugs->ensure($vehicle);

        return $this->render($vehicle);
    }

    public function showBySlug(string $slug, VehicleSlugService $slugs): Response|RedirectResponse
    {
        $vehicle = Vehicle::query()->where('slug', $slug)->first();
        if (! $vehicle) {
            $history = VehicleSlugHistory::query()->with('vehicle')->where('slug', $slug)->first();
            abort_unless($history?->vehicle && $history->vehicle->visibility === 'public', 404);

            $slugs->ensure($history->vehicle);

            return redirect()->route('vehicles.slug.show', ['slug' => $history->vehicle->slug], 301);
        }

        return $this->render($vehicle);
    }

    private function render(Vehicle $vehicle): Response
    {
        abort_unless($vehicle->visibility === 'public', 404);
        $vehicle->load(['photos', 'user', 'entries' => fn ($query) => $query->where('is_public', true)->orderByDesc('occurred_on')->orderByDesc('id')]);

        return response()->view('vehicle', compact('vehicle'))->header('Cache-Control', 'private, no-store');
    }
}
