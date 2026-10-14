<?php

namespace App\Services;

use App\Models\MagazineSetting;
use App\Models\Vehicle;
use Illuminate\Validation\Rule;

class MagazineService
{
    public function settingsFor(Vehicle $vehicle): MagazineSetting
    {
        return $vehicle->magazineSetting()->firstOrCreate([], [
            'primary_color' => '#ff5a1f',
            'theme' => 'dark',
            'editorial_style' => 'editorial',
        ]);
    }

    public function validate(array $data): array
    {
        validator($data, [
            'primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme' => ['required', Rule::in(['light', 'dark'])],
            'editorial_style' => ['required', Rule::in(['editorial', 'technical', 'minimal'])],
            'cover_photo_id' => ['nullable', 'integer', 'exists:vehicle_photos,id'],
        ])->validate();

        return $data;
    }
}
