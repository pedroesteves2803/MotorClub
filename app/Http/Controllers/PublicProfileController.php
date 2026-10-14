<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ShotGallery;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\VehicleSlugService;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profiles.edit', ['person' => $request->user()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(['bio' => 'nullable|string|max:1500', 'city' => 'nullable|string|max:100', 'profile_public' => 'required|boolean']);
        $request->user()->forceFill($data)->save();

        return back()->with('status', 'Perfil e privacidade atualizados.');
    }

    public function show(Request $request, User $person)
    {
        abort_unless($person->profile_public || $request->user()?->id === $person->id, 404);

        $person->unsetRelation('photographerProfile');
        $person->load(['photographerProfile' => fn ($query) => $query->where('is_public', true)]);

        $vehicles = Vehicle::with('previewPhoto')->where('user_id', $person->id)->where('visibility', 'public')->get();
        $slugs = app(VehicleSlugService::class);
        $vehicles->each(fn (Vehicle $vehicle) => $slugs->ensure($vehicle));

        return view('profiles.show', [
            'person' => $person,
            'vehicles' => $vehicles,
            'projects' => Project::with(['vehicle.previewPhoto', 'previewPhoto'])->published()->whereHas('vehicle', fn ($q) => $q->where('user_id', $person->id))->get(),
            'shotGalleries' => ShotGallery::with('previewPhoto')->where('user_id', $person->id)->where('is_public', true)->whereHas('user.photographerProfile', fn ($profile) => $profile->where('is_public', true))->get(),
        ]);
    }
}
