<?php

namespace App\Services;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EvolutionService
{
    public function stages(Project $project, bool $ownerCanEdit = false): array
    {
        $stages = collect();
        $project->loadMissing(['vehicle', 'modifications.parts.photos', 'modifications.photos', 'vehicle.entries']);

        foreach ($project->modifications as $modification) {
            if (! $ownerCanEdit && ! $modification->is_public) {
                continue;
            }
            $photos = $this->publicPhotos($modification->photos, $ownerCanEdit)
                ->concat($modification->parts->flatMap(fn ($part) => $this->publicPhotos($part->photos, $ownerCanEdit)))
                ->values();
            $stages->push([
                'id' => 'modification-'.$modification->id,
                'kind' => 'modification',
                'title' => $modification->title,
                'description' => $modification->description,
                'status' => $this->status($modification->status),
                'status_key' => $modification->status,
                'category' => $modification->category,
                'date' => $modification->completed_on?->format('d/m/Y') ?: $modification->started_on?->format('d/m/Y'),
                'photos' => $photos->all(),
                'parts' => $modification->parts->filter(fn ($part) => $ownerCanEdit || $part->is_public)->map(fn ($part) => [
                    'name' => $part->name,
                    'brand' => $part->brand,
                    'model' => $part->model,
                    'status' => $part->status,
                ])->values()->all(),
            ]);
        }

        foreach ($project->vehicle->entries->sortByDesc(fn ($entry) => [$entry->occurred_on?->timestamp ?? 0, $entry->id]) as $entry) {
            if (! $ownerCanEdit && ! $entry->is_public) {
                continue;
            }
            $stages->push([
                'id' => 'entry-'.$entry->id,
                'kind' => $entry->category === 'maintenance' ? 'maintenance' : 'history',
                'title' => $entry->title,
                'description' => $entry->description,
                'status' => $entry->category === 'maintenance' ? 'Manutenção' : 'Registro',
                'status_key' => 'completed',
                'category' => $entry->category,
                'date' => $entry->occurred_on?->format('d/m/Y'),
                'photos' => [],
                'parts' => collect([$entry->parts, $entry->services])->filter()->implode(' · '),
            ]);
        }

        return $stages->sortBy(function (array $stage) {
            return [$stage['date'] ? Carbon::createFromFormat('d/m/Y', $stage['date'])->timestamp : 0, $stage['id']];
        })->values()->all();
    }

    private function publicPhotos(Collection $photos, bool $ownerCanEdit): Collection
    {
        return $photos->filter(fn ($photo) => $ownerCanEdit || $photo->is_public)->map(fn ($photo) => [
            'url' => route('projects.media.image', $photo),
            'alt' => $photo->caption ?: 'Foto da evolução',
            'kind' => $photo->kind,
        ]);
    }

    private function status(?string $status): string
    {
        return ['planned' => 'Planejada', 'in_progress' => 'Em andamento', 'completed' => 'Concluída', 'cancelled' => 'Cancelada'][$status] ?? 'Registrada';
    }
}
