<?php

namespace App\Services;

use App\Models\ShotDownloadGrant;
use App\Models\ShotPhoto;
use App\Models\Vehicle;
use App\Models\VehicleShotAsset;
use Illuminate\Validation\ValidationException;

class ShotIntegrationService
{
    public function attach(Vehicle $vehicle, ShotPhoto $photo, bool $makePublic = false): VehicleShotAsset
    {
        abort_unless($photo->publiclyVisible(), 404);
        abort_if($photo->gallery->user_id === $vehicle->user_id, 422, 'Associe fotos de outro fotógrafo para preservar a autoria.');
        $authorized = ShotDownloadGrant::query()
            ->where('shot_photo_id', $photo->id)
            ->whereNull('revoked_at')
            ->whereHas('payment', fn ($query) => $query->where('buyer_id', $vehicle->user_id)->where('vehicle_id', $vehicle->id)->where('status', 'approved'))
            ->exists();
        abort_if($photo->price !== null && ! $authorized, 422, 'Esta fotografia exige autorização de compra antes de ser associada.');

        if (! filled($photo->license)) {
            throw ValidationException::withMessages(['photo' => 'A fotografia não informa uma licença de uso.']);
        }

        return VehicleShotAsset::updateOrCreate(
            ['vehicle_id' => $vehicle->id, 'shot_photo_id' => $photo->id],
            [
                'license_snapshot' => $photo->license,
                'credit' => $photo->gallery->user->photographerProfile?->professional_name,
                'is_public' => $makePublic,
                'revoked_at' => null,
            ],
        );
    }
}
