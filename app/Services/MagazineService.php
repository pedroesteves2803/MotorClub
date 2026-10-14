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
            'palette' => 'carbon',
            'is_published' => true,
            'qr_foreground' => '#111111',
            'qr_background' => '#ffffff',
        ]);
    }

    public function validate(array $data): array
    {
        $validated = validator($data, [
            'primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme' => ['required', Rule::in(['light', 'dark'])],
            'editorial_style' => ['required', Rule::in(['editorial', 'technical', 'minimal', 'premium', 'racing', 'classic'])],
            'title' => ['sometimes', 'nullable', 'string', 'max:160'],
            'subtitle' => ['sometimes', 'nullable', 'string', 'max:240'],
            'palette' => ['sometimes', Rule::in(['carbon', 'minimal', 'racing', 'performance', 'sporty', 'custom'])],
            'is_published' => ['sometimes', 'boolean'],
            'cover_photo_id' => ['sometimes', 'nullable', 'integer', 'exists:vehicle_photos,id'],
            'highlight_photo_ids' => ['sometimes', 'nullable', 'array', 'max:12'],
            'highlight_photo_ids.*' => ['integer', 'distinct', 'exists:vehicle_photos,id'],
            'chapter_order' => ['sometimes', 'nullable', 'array', 'max:30'],
            'chapter_order.*' => ['string', 'max:80', 'distinct'],
        ])->validate();

        if (array_key_exists('highlight_photo_ids', $validated)) {
            $validated['highlight_photo_ids'] = array_values(array_unique(array_map('intval', $validated['highlight_photo_ids'] ?? [])));
        }
        if (array_key_exists('chapter_order', $validated)) {
            $validated['chapter_order'] = array_values($validated['chapter_order'] ?? []);
        }

        return $validated;
    }

    public function validateQr(array $data): array
    {
        return validator($data, [
            'qr_foreground' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'qr_background' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'qr_label' => ['nullable', 'string', 'max:120'],
            'qr_brand' => ['nullable', 'string', 'max:120'],
        ])->validate();
    }
}
