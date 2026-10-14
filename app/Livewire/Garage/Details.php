<?php

namespace App\Livewire\Garage;

use App\Models\FeatureSetting;
use App\Models\MediaPhoto;
use App\Models\Project;
use App\Models\ProjectPart;
use App\Models\ShotPurchaseRequest;
use App\Models\Vehicle;
use App\Services\VehicleImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Details extends Component
{
    use WithFileUploads, WithPagination;

    #[Locked]
    public int $vehicleId;

    #[Locked]
    public ?int $entryId = null;

    public string $category = 'maintenance';

    public string $title = '';

    public string $description = '';

    public string $occurred_on = '';

    public string $parts = '';

    public string $services = '';

    public string $cost = '';

    public bool $is_public = false;

    public $photo;

    public array $photos = [];

    public string $photo_kind = 'galeria';

    public string $photo_context = 'vehicle';

    public string $photo_context_title = '';

    public string $photo_context_description = '';

    public ?int $photo_modification_id = null;

    public ?int $photo_part_id = null;

    public array $reuse_photo_ids = [];

    public string $quick_modification_title = '';

    public string $quick_modification_status = 'planned';

    public array $quick_modification_photos = [];

    public ?int $quick_part_modification_id = null;

    public string $quick_part_name = '';

    public string $quick_part_status = 'planned';

    public string $quick_part_brand = '';

    public string $quick_part_model = '';

    private function ensureFeatureEnabled(): void
    {
        abort_unless(FeatureSetting::enabled('garage'), 404);
    }

    public function mount(Vehicle $vehicle): void
    {
        $this->ensureFeatureEnabled();
        Gate::authorize('update', $vehicle);
        $this->vehicleId = $vehicle->id;
        $this->occurred_on = now()->toDateString();
    }

    private function vehicle(): Vehicle
    {
        $vehicle = Vehicle::findOrFail($this->vehicleId);
        Gate::authorize('update', $vehicle);

        return $vehicle;
    }

    public function saveEntry(): void
    {
        $this->ensureFeatureEnabled();
        $vehicle = $this->vehicle();
        $this->title = trim($this->title);
        $this->description = trim($this->description);
        $data = $this->validate([
            'category' => ['required', Rule::in(['maintenance', 'modification'])],
            'title' => ['required', 'string', 'max:150'], 'description' => ['required', 'string', 'max:5000'],
            'occurred_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:1886-01-01', 'before_or_equal:today'],
            'parts' => ['nullable', 'string', 'max:2000'], 'services' => ['nullable', 'string', 'max:2000'],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'is_public' => ['required', 'boolean'],
        ], ['required' => 'Preencha este campo.', 'in' => 'Selecione uma opção válida.', 'max' => 'O valor ultrapassa o limite de :max.', 'min' => 'O valor não pode ser negativo.', 'date_format' => 'Informe uma data válida.', 'before_or_equal' => 'A data não pode estar no futuro.', 'after_or_equal' => 'Informe uma data a partir de 1886.', 'numeric' => 'Informe um valor numérico, usando ponto nos centavos.', 'decimal' => 'Use no máximo duas casas decimais.']);
        $data['cost'] = $data['cost'] === '' ? null : $data['cost'];
        $data['occurred_on'] = $data['occurred_on'] === '' ? null : $data['occurred_on'];
        if ($this->entryId) {
            $vehicle->entries()->findOrFail($this->entryId)->update($data);
        } else {
            $vehicle->entries()->create($data);
        }
        $this->clearEntry();
        $this->resetPage();
        session()->flash('status', 'Registro salvo.');
    }

    public function editEntry(int $id): void
    {
        $this->ensureFeatureEnabled();
        $entry = $this->vehicle()->entries()->find($id);
        abort_unless($entry, 404);
        $this->entryId = $entry->id;
        foreach (['category', 'title', 'description', 'parts', 'services', 'cost', 'is_public'] as $field) {
            $this->$field = (string) $entry->$field;
        }
        $this->occurred_on = $entry->occurred_on?->toDateString() ?? '';
        $this->resetValidation();
    }

    public function clearEntry(): void
    {
        $this->ensureFeatureEnabled();
        $this->reset('entryId', 'category', 'title', 'description', 'parts', 'services', 'cost', 'is_public');
        $this->occurred_on = now()->toDateString();
        $this->resetValidation();
    }

    public function deleteEntry(int $id): void
    {
        $this->ensureFeatureEnabled();
        $entry = $this->vehicle()->entries()->find($id);
        abort_unless($entry, 404);
        $entry->delete();
        $this->clearEntry();
        $this->resetPage();
    }

    public function saveQuickModification(VehicleImages $images): void
    {
        $this->ensureFeatureEnabled();
        $vehicle = $this->vehicle();
        $this->quick_modification_title = trim($this->quick_modification_title);
        $this->validate([
            'quick_modification_title' => ['required', 'string', 'max:150'],
            'quick_modification_status' => ['required', Rule::in(['planned', 'in_progress', 'completed'])],
            'quick_modification_photos' => ['array', 'max:'.config('motorclub.photo_max_files', 12)],
            'quick_modification_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('motorclub.photo_max_upload_kb', 5120), 'dimensions:max_width=4096,max_height=4096'],
        ], ['required' => 'Preencha este campo.', 'in' => 'Selecione uma opção válida.']);

        $storedPaths = [];
        try {
            DB::transaction(function () use ($vehicle, $images, &$storedPaths): void {
                $project = $this->mainProject($vehicle);
                $modification = $project->modifications()->create([
                    'title' => $this->quick_modification_title,
                    'category' => 'outro',
                    'status' => $this->quick_modification_status,
                    'description' => null,
                    'sort_order' => ((int) $project->modifications()->max('sort_order')) + 1,
                    'is_public' => false,
                ]);
                $this->storeMediaFiles($modification, $this->quick_modification_photos, $images, $storedPaths);
            });
        } catch (\Throwable $exception) {
            $this->deleteStoredPaths($storedPaths);
            throw $exception;
        }

        $count = count($this->quick_modification_photos);
        $this->reset('quick_modification_title', 'quick_modification_status', 'quick_modification_photos');
        $this->quick_modification_status = 'planned';
        session()->flash('status', 'Modificação registrada'.($count ? " com {$count} foto(s)" : '').'.');
    }

    public function saveQuickPart(): void
    {
        $this->ensureFeatureEnabled();
        $vehicle = $this->vehicle();
        $this->quick_part_name = trim($this->quick_part_name);
        $this->quick_part_brand = trim($this->quick_part_brand);
        $this->quick_part_model = trim($this->quick_part_model);
        $data = $this->validate([
            'quick_part_modification_id' => ['required', 'integer'],
            'quick_part_name' => ['required', 'string', 'max:150'],
            'quick_part_status' => ['required', Rule::in(['planned', 'in_progress', 'installed'])],
            'quick_part_brand' => ['nullable', 'string', 'max:100'],
            'quick_part_model' => ['nullable', 'string', 'max:120'],
        ], ['required' => 'Preencha este campo.', 'in' => 'Selecione uma opção válida.']);

        $project = $this->mainProject($vehicle);
        $modification = $project->modifications()->find($data['quick_part_modification_id']);
        if (! $modification) {
            $this->addError('quick_part_modification_id', 'Selecione uma modificação deste veículo.');

            return;
        }

        $modification->parts()->create([
            'name' => $data['quick_part_name'],
            'brand' => $data['quick_part_brand'] ?: null,
            'model' => $data['quick_part_model'] ?: null,
            'status' => $data['quick_part_status'],
            'description' => null,
            'sort_order' => ((int) $modification->parts()->max('sort_order')) + 1,
            'is_public' => false,
        ]);

        $this->reset('quick_part_modification_id', 'quick_part_name', 'quick_part_status', 'quick_part_brand', 'quick_part_model');
        $this->quick_part_status = 'planned';
        session()->flash('status', 'Peça adicionada à modificação.');
    }

    private function mainProject(Vehicle $vehicle): Project
    {
        $lockedVehicle = Vehicle::query()->lockForUpdate()->findOrFail($vehicle->id);
        $project = $lockedVehicle->projects()->latest('id')->first();

        return $project ?: $lockedVehicle->projects()->create([
            'title' => trim($lockedVehicle->brand.' '.$lockedVehicle->model),
            'category' => 'maintenance',
            'status' => 'planning',
            'description' => 'Registro principal de '.$lockedVehicle->brand.' '.$lockedVehicle->model.'.',
            'workshop_status' => 'pending',
            'is_public' => false,
            'show_cost' => false,
        ]);
    }

    private function photoContexts(): array
    {
        return ['vehicle', 'project', 'new_modification', 'existing_modification', 'existing_part', 'maintenance'];
    }

    private function photoRules(): array
    {
        return [
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:'.config('motorclub.photo_max_upload_kb', 5120),
            'dimensions:max_width=4096,max_height=4096',
        ];
    }

    private function photoMessages(): array
    {
        return [
            'photo.image' => 'Envie uma imagem válida.',
            'photo.mimes' => 'Use JPG, PNG ou WebP.',
            'photo.max' => 'Cada foto deve ter até :max KB.',
            'photo.dimensions' => 'A foto deve ter no máximo 4096 × 4096 pixels.',
        ];
    }

    private function resolveMediaTarget(Vehicle $vehicle): Model
    {
        $project = $this->mainProject($vehicle);

        return match ($this->photo_context) {
            'project' => $project,
            'new_modification' => $project->modifications()->create([
                'title' => trim($this->photo_context_title),
                'category' => 'outro',
                'status' => 'planned',
                'is_public' => false,
                'sort_order' => ((int) $project->modifications()->max('sort_order')) + 1,
            ]),
            'existing_modification' => tap($project->modifications()->find($this->photo_modification_id), function ($target): void {
                abort_unless($target, 404);
            }),
            'existing_part' => tap(ProjectPart::query()
                ->whereKey($this->photo_part_id)
                ->whereHas('modification', fn ($query) => $query->where('project_id', $project->id))
                ->first(), function ($target): void {
                    abort_unless($target, 404);
                }),
            'maintenance' => $vehicle->entries()->create([
                'category' => 'maintenance',
                'title' => trim($this->photo_context_title),
                'description' => trim($this->photo_context_description) ?: 'Fotos de manutenção do veículo.',
                'occurred_on' => now()->toDateString(),
                'is_public' => false,
            ]),
            default => throw ValidationException::withMessages(['photo_context' => 'Escolha onde estas fotos serão usadas.']),
        };
    }

    private function mediaRelation(Model $target)
    {
        return $target instanceof Project ? $target->mediaPhotos() : $target->photos();
    }

    private function storeMediaPhoto(Model $target, string $path): MediaPhoto
    {
        $photos = $this->mediaRelation($target);
        $isPrimary = $this->photo_kind === 'capa'
            || (! $photos->where('is_primary', true)->exists() && $photos->count() === 0);

        if ($isPrimary) {
            $photos->update(['is_primary' => false]);
        }

        return $photos->create([
            'path' => $path,
            'kind' => $this->photo_kind ?: 'galeria',
            'sort_order' => $photos->count(),
            'is_primary' => $isPrimary,
            'is_public' => false,
        ]);
    }

    private function storeMediaFiles(Model $target, array $uploads, VehicleImages $images, array &$storedPaths): void
    {
        $limit = (int) config('motorclub.photo_max_files', 12);
        $photos = $this->mediaRelation($target);
        if (count($uploads) > $limit || $photos->count() + count($uploads) > $limit) {
            throw ValidationException::withMessages(['photos' => "O limite é de {$limit} fotos neste álbum."]);
        }
        foreach ($uploads as $upload) {
            $path = $images->store($upload, 'media');
            $storedPaths[] = $path;
            $this->storeMediaPhoto($target, $path);
        }
    }

    private function deleteStoredPaths(array $paths): void
    {
        foreach ($paths as $path) {
            Storage::disk(config('filesystems.private_disk'))->delete($path);
        }
    }

    public function uploadPhoto(VehicleImages $images): void
    {
        $this->ensureFeatureEnabled();
        $vehicle = $this->vehicle();
        $hasMultiple = $this->photos !== [];
        $uploads = $hasMultiple ? $this->photos : ($this->photo ? [$this->photo] : []);
        if ($uploads === []) {
            $this->addError('photos', 'Escolha pelo menos uma foto.');

            return;
        }

        $this->validate([
            'photo_context' => ['required', Rule::in($this->photoContexts())],
            'photo_kind' => ['required', Rule::in(['capa', 'galeria', 'interno', 'externo', 'evento', 'detalhe', 'antes', 'durante', 'depois'])],
        ], ['required' => 'Preencha este campo.', 'in' => 'Selecione uma opção válida.']);

        $this->photo_context_title = trim($this->photo_context_title);
        if (in_array($this->photo_context, ['new_modification', 'maintenance'], true) && $this->photo_context_title === '') {
            $this->addError('photo_context_title', $this->photo_context === 'maintenance' ? 'Dê um nome para a manutenção.' : 'Dê um nome para a modificação.');

            return;
        }
        if ($this->photo_context === 'existing_modification' && ! $this->photo_modification_id) {
            $this->addError('photo_modification_id', 'Escolha uma modificação.');

            return;
        }
        if ($this->photo_context === 'existing_part' && ! $this->photo_part_id) {
            $this->addError('photo_part_id', 'Escolha uma peça.');

            return;
        }

        $limit = (int) config('motorclub.photo_max_files', 12);
        $failed = [];
        $target = null;
        $successCount = 0;

        foreach ($uploads as $index => $upload) {
            $path = null;
            try {
                Validator::make(['photo' => $upload], ['photo' => $this->photoRules()], $this->photoMessages())->validate();
                if ($this->photo_context === 'vehicle' && $vehicle->photos()->count() >= $limit) {
                    throw ValidationException::withMessages(['photo' => "O limite é de {$limit} fotos por veículo."]);
                }
                if ($this->photo_context !== 'vehicle' && $target && $this->mediaRelation($target)->count() >= $limit) {
                    throw ValidationException::withMessages(['photo' => "O limite é de {$limit} fotos neste álbum."]);
                }

                DB::transaction(function () use ($vehicle, $images, $upload, $successCount, &$target, &$path): void {
                    if ($this->photo_context === 'vehicle') {
                        $path = $images->store($upload);
                        $isPrimary = ($this->photo_kind === 'capa' && $successCount === 0)
                            || (! $vehicle->photos()->where('is_primary', true)->exists() && $vehicle->photos()->count() === 0);
                        if ($isPrimary) {
                            $vehicle->photos()->update(['is_primary' => false]);
                        }
                        $vehicle->photos()->create([
                            'path' => $path,
                            'kind' => $this->photo_kind,
                            'sort_order' => $vehicle->photos()->count(),
                            'is_primary' => $isPrimary,
                        ]);

                        return;
                    }

                    $target ??= $this->resolveMediaTarget($vehicle);
                    $path = $images->store($upload, 'media');
                    $this->storeMediaPhoto($target, $path);
                });
                $successCount++;
            } catch (ValidationException $exception) {
                if ($path) {
                    $this->deleteStoredPaths([$path]);
                }
                $message = collect($exception->errors())->flatten()->first() ?: 'Não foi possível processar esta foto.';
                $this->addError($hasMultiple ? "photos.{$index}" : 'photo', $message);
                $failed[] = $upload;
            } catch (\Throwable $exception) {
                if ($path) {
                    $this->deleteStoredPaths([$path]);
                }
                report($exception);
                $this->addError($hasMultiple ? "photos.{$index}" : 'photo', 'Esta foto falhou. Tente novamente.');
                $failed[] = $upload;
            }
        }

        if ($successCount === 0) {
            $this->addError('photos', 'Nenhuma foto foi salva. Corrija os itens indicados e tente novamente.');
        }

        if ($hasMultiple) {
            $this->photos = $failed;
        } elseif ($failed !== []) {
            $this->photo = $failed[0];
        } else {
            $this->reset('photo');
        }

        if ($successCount > 0) {
            $this->reset('photo_kind', 'photo_context', 'photo_context_title', 'photo_context_description', 'photo_modification_id', 'photo_part_id');
            $this->photo_kind = 'galeria';
            $this->photo_context = 'vehicle';
            session()->flash('status', $successCount === 1 ? 'Foto salva.' : "{$successCount} fotos salvas.");
        }
    }

    public function deletePhoto(int $id): void
    {
        $this->ensureFeatureEnabled();
        $photo = $this->vehicle()->photos()->find($id);
        abort_unless($photo, 404);
        if (! Storage::disk(config('filesystems.private_disk'))->delete($photo->path)) {
            $this->addError('photo', 'Não foi possível excluir a foto. Tente novamente.');

            return;
        }
        $photo->delete();
    }

    public function reorderPhotos(array $orderedIds): void
    {
        $this->ensureFeatureEnabled();
        $vehicle = $this->vehicle();
        $allowed = $vehicle->photos()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $orderedIds = array_values(array_map('intval', $orderedIds));
        abort_unless(count($orderedIds) === count($allowed) && ! array_diff($orderedIds, $allowed) && ! array_diff($allowed, $orderedIds), 422);
        DB::transaction(function () use ($vehicle, $orderedIds): void {
            foreach ($orderedIds as $position => $id) {
                $vehicle->photos()->whereKey($id)->update(['sort_order' => $position]);
            }
        });
    }

    public function movePhoto(int $id, string $direction): void
    {
        $this->ensureFeatureEnabled();
        $vehicle = $this->vehicle();
        $photos = $vehicle->photos()->orderBy('sort_order')->orderBy('id')->get()->values();
        $index = $photos->search(fn ($photo) => $photo->id === $id);
        if ($index === false) {
            abort(404);
        }
        $target = $direction === 'up' ? $index - 1 : $index + 1;
        if ($target < 0 || $target >= $photos->count()) {
            return;
        }
        $ids = $photos->pluck('id')->all();
        [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];
        $this->reorderPhotos($ids);
    }

    public function reuseLibraryPhotos(VehicleImages $images): void
    {
        $this->ensureFeatureEnabled();
        $vehicle = $this->vehicle();
        $ids = array_values(array_unique(array_map('intval', $this->reuse_photo_ids)));
        if ($ids === []) {
            $this->addError('reuse_photo_ids', 'Selecione pelo menos uma foto.');

            return;
        }
        $limit = (int) config('motorclub.photo_max_files', 12);
        if (count($ids) > $limit) {
            $this->addError('reuse_photo_ids', "Escolha no máximo {$limit} fotos por vez.");

            return;
        }
        if ($this->photo_context === 'vehicle') {
            $this->addError('reuse_photo_ids', 'Escolha um projeto, modificação, peça ou manutenção para reutilizar a foto.');

            return;
        }
        if (in_array($this->photo_context, ['new_modification', 'maintenance'], true) && trim($this->photo_context_title) === '') {
            $this->addError('photo_context_title', $this->photo_context === 'maintenance' ? 'Dê um nome para a manutenção.' : 'Dê um nome para a modificação.');

            return;
        }
        if ($this->photo_context === 'existing_modification' && ! $this->photo_modification_id) {
            $this->addError('photo_modification_id', 'Escolha uma modificação.');

            return;
        }
        if ($this->photo_context === 'existing_part' && ! $this->photo_part_id) {
            $this->addError('photo_part_id', 'Escolha uma peça.');

            return;
        }

        $photos = $vehicle->photos()->whereIn('id', $ids)->get();
        abort_unless($photos->count() === count($ids), 404);
        $target = null;
        $storedPaths = [];
        $copied = 0;
        try {
            DB::transaction(function () use ($vehicle, $photos, $images, $limit, &$target, &$storedPaths, &$copied): void {
                foreach ($photos as $photo) {
                    $target ??= $this->resolveMediaTarget($vehicle);
                    if ($this->mediaRelation($target)->count() >= $limit) {
                        throw ValidationException::withMessages(['reuse_photo_ids' => "O limite é de {$limit} fotos neste álbum."]);
                    }
                    $path = $images->copy($photo->path, 'media');
                    $storedPaths[] = $path;
                    $this->storeMediaPhoto($target, $path);
                    $copied++;
                }
            });
        } catch (\Throwable $exception) {
            $this->deleteStoredPaths($storedPaths);
            throw $exception;
        }
        $this->reset('reuse_photo_ids');
        session()->flash('status', $copied === 1 ? 'Foto reutilizada.' : "{$copied} fotos reutilizadas.");
    }

    public function setPrimaryPhoto(int $id): void
    {
        $this->ensureFeatureEnabled();
        $photo = $this->vehicle()->photos()->findOrFail($id);
        $this->vehicle()->photos()->update(['is_primary' => false]);
        $photo->update(['is_primary' => true]);
    }

    public function render()
    {
        $this->ensureFeatureEnabled();
        $vehicle = $this->vehicle();
        $project = $vehicle->projects()->latest('id')->first();
        $modifications = $project?->modifications()->with('parts')->get() ?? collect();

        return view('livewire.garage.details', [
            'vehicle' => $vehicle,
            'project' => $project,
            'vehiclePhotos' => $vehicle->photos()->get(),
            'modifications' => $modifications,
            'parts' => $modifications->flatMap->parts->values(),
            'entries' => $vehicle->entries()->with('photos')->orderByDesc('occurred_on')->orderByDesc('id')->paginate(10),
            'shotRequests' => ShotPurchaseRequest::with('photo.gallery')
                ->where('vehicle_id', $vehicle->id)
                ->where('buyer_id', auth()->id())
                ->where('status', 'approved')
                ->get(),
        ])->layout('components.layouts.app');
    }
}
