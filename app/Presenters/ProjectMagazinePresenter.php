<?php

namespace App\Presenters;

use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Converts the existing project, vehicle and history records into a small
 * editorial data structure. It deliberately does not persist magazine data:
 * edits to the garage are reflected on the next request.
 */
class ProjectMagazinePresenter
{
    public function present(Project $project, bool $ownerCanEdit = false): array
    {
        $vehicle = $project->vehicle;
        $disk = Storage::disk(config('filesystems.private_disk'));
        $projectPhotos = $project->photos->filter(fn ($photo) => $disk->exists($photo->path))->map(fn ($photo) => [
            'url' => route('projects.image', $photo),
            'alt' => 'Foto do projeto '.$project->title,
            'source' => 'projeto',
        ]);
        $vehiclePhotos = $vehicle->photos->filter(fn ($photo) => $disk->exists($photo->path))->map(fn ($photo) => [
            'url' => route('vehicle-photos.show', $photo),
            'alt' => 'Foto de '.$this->vehicleName($vehicle),
            'source' => 'carro',
        ]);
        $photos = $projectPhotos->isNotEmpty() ? $projectPhotos : $vehiclePhotos;

        $entries = $vehicle->entries
            ->filter(fn ($entry) => $ownerCanEdit || $entry->is_public)
            ->sortByDesc(fn ($entry) => [$entry->occurred_on?->timestamp ?? 0, $entry->id])
            ->values();

        $chapters = collect();
        $chapterNumber = 1;

        if (filled($project->description)) {
            $chapters->push($this->chapter(
                $chapterNumber++,
                'historia',
                'A história',
                'O ponto de partida',
                'photo-text',
                $project->description,
                $photos->take(2),
                ['status' => $this->statusLabel($project->status)]
            ));
        }

        if ($entries->isNotEmpty()) {
            $chapters->push($this->chapter(
                $chapterNumber++,
                'evolucao',
                'Evolução documentada',
                'O que já foi registrado',
                'timeline',
                null,
                $photos->take(1),
                ['entries' => $entries->map(fn ($entry) => $this->entry($entry, $ownerCanEdit))->all()]
            ));
        }

        if (filled($project->parts)) {
            $chapters->push($this->chapter(
                $chapterNumber++,
                'componentes',
                'Peças e modificações',
                'O conjunto em detalhe',
                'technical',
                $project->parts,
                $photos->slice(1, 2),
                ['status' => $this->statusLabel($project->status)]
            ));
        }

        if (filled($project->services)) {
            $chapters->push($this->chapter(
                $chapterNumber++,
                'servicos',
                $project->status === 'completed' ? 'Serviços realizados' : 'Serviços e próximos capítulos',
                $project->status === 'completed' ? 'O trabalho documentado' : 'O que vem a seguir',
                'photo-text',
                $project->services,
                $photos->slice(2, 2),
                ['status' => $this->statusLabel($project->status)]
            ));
        }

        $costs = $this->costs($project, $entries, $ownerCanEdit);
        if ($costs['total'] !== null) {
            $chapters->push($this->chapter(
                $chapterNumber++,
                'investimento',
                'Investimento',
                'Valores autorizados',
                'spec-sheet',
                null,
                collect(),
                ['costs' => $costs['items'], 'total' => $costs['total']]
            ));
        }

        if ($project->status !== 'completed' && (filled($project->services) || filled($project->parts))) {
            $chapters->push($this->chapter(
                $chapterNumber++,
                'proximos-passos',
                'Próximos capítulos',
                'Registrado como '.$this->statusLabel($project->status),
                'quote',
                'O projeto continua em evolução. As informações abaixo são registros do proprietário e não representam etapas concluídas.',
                collect(),
                ['planned' => collect([$project->services, $project->parts])->filter()->implode("\n\n")]
            ));
        }

        if ($photos->isNotEmpty()) {
            $chapters->push($this->chapter(
                $chapterNumber,
                'galeria',
                'Galeria',
                'Visto de perto',
                'gallery',
                null,
                $photos,
                []
            ));
        }

        $pages = collect([
            [
                'type' => 'cover',
                'id' => 'capa',
                'number' => null,
                'chapter' => null,
                'kicker' => 'Autoverse Magazine · Project Edition',
                'title' => $project->title,
                'subtitle' => $this->vehicleName($vehicle),
                'description' => $this->coverDescription($project),
                'photos' => $photos->take(1)->values()->all(),
                'meta' => $this->coverMeta($project, $vehicle),
            ],
            [
                'type' => 'contents',
                'id' => 'sumario',
                'number' => null,
                'chapter' => null,
                'kicker' => 'Nesta edição',
                'title' => 'Sumário',
                'subtitle' => 'Uma leitura do projeto, página por página.',
                'description' => null,
                'photos' => [],
                'meta' => [],
                'chapters' => $chapters->map(fn ($chapter) => [
                    'id' => $chapter['id'],
                    'number' => $chapter['number'],
                    'title' => $chapter['title'],
                    'layout' => $chapter['layout'],
                ])->all(),
            ],
        ])->merge($chapters)->values();
        $pages = $pages->map(function (array $page, int $index) use ($chapters) {
            if ($page['type'] === 'contents') {
                $page['chapters'] = $chapters->map(function (array $chapter) use ($index) {
                    return [
                        'id' => $chapter['id'],
                        'number' => $chapter['number'],
                        'title' => $chapter['title'],
                        'layout' => $chapter['layout'],
                        'page' => $chapter['number'] + $index,
                    ];
                })->all();
            }

            return $page;
        })->values();

        return [
            'title' => $project->title,
            'vehicleName' => $this->vehicleName($vehicle),
            'ownerName' => $vehicle->user?->name,
            'status' => $this->statusLabel($project->status),
            'category' => config('motorclub.categories.'.$project->category, Str::headline((string) $project->category)),
            'isOwner' => $ownerCanEdit,
            'photos' => $photos->values()->all(),
            'chapters' => $chapters->values()->all(),
            'pages' => $pages->all(),
            'pageCount' => $pages->count(),
        ];
    }

    private function chapter(int $number, string $id, string $title, string $kicker, string $layout, ?string $body, Collection $photos, array $meta): array
    {
        return [
            'type' => 'chapter',
            'id' => $id,
            'number' => $number,
            'chapter' => sprintf('%02d', $number),
            'kicker' => $kicker,
            'title' => $title,
            'subtitle' => null,
            'description' => $body,
            'photos' => $photos->values()->all(),
            'meta' => $meta,
            'layout' => $layout,
        ];
    }

    private function entry($entry, bool $ownerCanEdit): array
    {
        $category = $entry->category === 'maintenance' ? 'Manutenção' : 'Modificação';

        return [
            'date' => $entry->occurred_on?->format('d/m/Y') ?? 'Data não informada',
            'title' => $entry->title,
            'category' => $category,
            'description' => $entry->description,
            'parts' => $entry->parts,
            'services' => $entry->services,
            'cost' => $ownerCanEdit && $entry->cost !== null ? (float) $entry->cost : null,
            'visibility' => $ownerCanEdit && ! $entry->is_public ? 'Privado' : null,
        ];
    }

    private function costs(Project $project, Collection $entries, bool $ownerCanEdit): array
    {
        if (! $project->show_cost && ! $ownerCanEdit) {
            return ['items' => [], 'total' => null];
        }

        $items = collect();
        if ($project->cost !== null) {
            $items->push(['label' => 'Projeto', 'value' => (float) $project->cost]);
        }
        foreach ($entries as $entry) {
            if ($entry->cost !== null && ($ownerCanEdit || $entry->is_public)) {
                $items->push(['label' => $entry->title, 'value' => (float) $entry->cost]);
            }
        }

        return ['items' => $items->all(), 'total' => $items->isEmpty() ? null : $items->sum('value')];
    }

    private function vehicleName($vehicle): string
    {
        return $vehicle->public_name ?: trim($vehicle->brand.' '.$vehicle->model.($vehicle->trim ? ' '.$vehicle->trim : ''));
    }

    private function statusLabel(?string $status): string
    {
        return config('motorclub.project_status.'.$status, Str::headline((string) $status));
    }

    private function coverDescription(Project $project): string
    {
        return $project->status === 'completed'
            ? 'Uma edição sobre um projeto concluído, documentado em detalhes.'
            : 'Uma história em evolução, com trabalho realizado e próximos capítulos claramente identificados.';
    }

    private function coverMeta(Project $project, $vehicle): array
    {
        return array_values(array_filter([
            $vehicle->brand.' '.$vehicle->model,
            $vehicle->model_year,
            config('motorclub.categories.'.$project->category),
            $this->statusLabel($project->status),
        ], fn ($value) => filled($value)));
    }
}
