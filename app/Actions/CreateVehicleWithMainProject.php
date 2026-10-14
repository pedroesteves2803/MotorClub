<?php

namespace App\Actions;

use App\Models\Vehicle;
use App\Services\VehicleImages;
use App\Services\VehicleSlugService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CreateVehicleWithMainProject
{
    public function __construct(private VehicleSlugService $slugs) {}

    /**
     * Create the minimum vehicle record and its single main project atomically.
     * Uploaded files are re-encoded before the transaction is considered complete.
     */
    public function handle(int $userId, array $vehicleData, array $projectData, array $uploads, VehicleImages $images): Vehicle
    {
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($userId, $vehicleData, $projectData, $uploads, $images, &$storedPaths): Vehicle {
                $vehicle = new Vehicle;
                $vehicle->fill($vehicleData);
                $vehicle->user_id = $userId;
                $vehicle->save();
                $this->slugs->ensure($vehicle);

                foreach ($uploads as $index => $upload) {
                    $path = $images->store($upload);
                    $storedPaths[] = $path;
                    $vehicle->photos()->create([
                        'path' => $path,
                        'kind' => $vehicle->photos()->exists() ? 'galeria' : 'capa',
                        'sort_order' => $vehicle->photos()->count(),
                        'is_primary' => ! $vehicle->photos()->where('is_primary', true)->exists() && $index === 0,
                    ]);
                }

                $title = trim((string) ($projectData['title'] ?? '')) ?: trim($vehicle->brand.' '.$vehicle->model);
                $description = trim((string) ($projectData['description'] ?? '')) ?: 'Registro principal de '.$vehicle->brand.' '.$vehicle->model.'.';
                $vehicle->projects()->create([
                    'title' => $title,
                    'category' => $projectData['category'] ?? 'maintenance',
                    'status' => $projectData['status'] ?? 'planning',
                    'description' => $description,
                    'parts' => filled($projectData['parts'] ?? null) ? trim((string) $projectData['parts']) : null,
                    'services' => filled($projectData['services'] ?? null) ? trim((string) $projectData['services']) : null,
                    'cost' => ($projectData['cost'] ?? '') === '' ? null : $projectData['cost'],
                    'show_cost' => (bool) ($projectData['show_cost'] ?? false),
                    'is_public' => (bool) ($projectData['is_public'] ?? false),
                    'workshop_status' => 'pending',
                ]);

                return $vehicle;
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk(config('filesystems.private_disk'))->delete($path);
            }

            throw $exception;
        }
    }
}
