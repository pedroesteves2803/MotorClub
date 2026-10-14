<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ShotGallery;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\VehicleImages;
use App\Services\VehicleSlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function updatePhoto(Request $request, VehicleImages $images): RedirectResponse
    {
        $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('motorclub.photo_max_upload_kb', 5120), 'dimensions:max_width=4096,max_height=4096'],
        ], [
            'profile_photo.image' => 'Escolha uma imagem válida.',
            'profile_photo.mimes' => 'Use JPG, PNG ou WebP.',
            'profile_photo.max' => 'A foto deve ter até '.((int) config('motorclub.photo_max_upload_kb', 5120) / 1024).' MB.',
            'profile_photo.dimensions' => 'A foto deve ter no máximo 4096 × 4096 pixels.',
        ]);

        $user = $request->user();
        $previousPath = $user->profile_photo_path;
        $path = $images->store($request->file('profile_photo'), 'profiles', 'profile_photo');
        $user->forceFill(['profile_photo_path' => $path])->save();

        if ($previousPath && $previousPath !== $path) {
            Storage::disk(config('filesystems.private_disk'))->delete($previousPath);
        }

        return back()->with('status', 'Foto de perfil atualizada.');
    }

    public function removePhoto(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->profile_photo_path) {
            Storage::disk(config('filesystems.private_disk'))->delete($user->profile_photo_path);
            $user->forceFill(['profile_photo_path' => null])->save();
        }

        return back()->with('status', 'Foto de perfil removida.');
    }

    public function photo(Request $request, User $person): Response|StreamedResponse
    {
        abort_unless($person->profile_public || $request->user()?->id === $person->id, 404);
        abort_unless($person->profile_photo_path, 404);

        $disk = Storage::disk(config('filesystems.private_disk'));
        abort_unless($disk->exists($person->profile_photo_path), 404);

        return $disk->response($person->profile_photo_path, null, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function show(Request $request, User $person)
    {
        abort_unless($person->profile_public || $request->user()?->id === $person->id, 404);

        $person->unsetRelation('photographerProfile');
        $person->load(['photographerProfile' => fn ($query) => $query->where('is_public', true)]);

        $vehicles = Vehicle::with([
            'previewPhoto',
            'magazineSetting.coverPhoto',
            'projects' => fn ($query) => $query->where('is_public', true)->latest('id'),
        ])->where('user_id', $person->id)->where('visibility', 'public')->get();
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
