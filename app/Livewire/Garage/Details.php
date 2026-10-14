<?php

namespace App\Livewire\Garage;

use App\Models\FeatureSetting;
use App\Models\Project;
use App\Models\ShotPurchaseRequest;
use App\Models\Vehicle;
use App\Services\VehicleImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
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
            'quick_modification_photos' => ['array', 'max:12'],
            'quick_modification_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4096,max_height=4096'],
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

    private function storeMediaFiles(Model $target, array $uploads, VehicleImages $images, array &$storedPaths): void
    {
        if (count($uploads) > 12 || $target->photos()->count() + count($uploads) > 12) {
            throw ValidationException::withMessages(['photos' => 'O limite é de 12 fotos neste álbum.']);
        }
        foreach ($uploads as $index => $upload) {
            $path = $images->store($upload, 'media');
            $storedPaths[] = $path;
            $target->photos()->create([
                'path' => $path,
                'kind' => $this->photo_kind ?: 'galeria',
                'sort_order' => $target->photos()->count() + $index,
                'is_primary' => ! $target->photos()->where('is_primary', true)->exists() && $index === 0,
                'is_public' => false,
            ]);
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
        $uploads = $this->photos ?: ($this->photo ? [$this->photo] : []);
        if (! $uploads) {
            $this->addError('photos', 'Escolha pelo menos uma foto.');

            return;
        }
        if ($this->photo_context === 'vehicle' && $vehicle->photos()->count() + count($uploads) > 12) {
            $this->addError($this->photos ? 'photos' : 'photo', 'O limite é de 12 fotos por veículo.');

            return;
        }
        if ($this->photos) {
            $this->validate(['photos' => ['array', 'max:12'], 'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4096,max_height=4096'], 'photo_kind' => ['required', Rule::in(['capa', 'galeria', 'interno', 'externo', 'evento', 'detalhe', 'antes', 'durante', 'depois'])], 'photo_context' => ['required', Rule::in(['vehicle', 'new_modification', 'existing_modification', 'maintenance'])]], ['photos.*.image' => 'Envie imagens válidas.', 'photos.*.mimes' => 'Use JPEG, PNG ou WebP.', 'photos.*.max' => 'Cada foto deve ter até 5 MB.', 'photos.*.dimensions' => 'Cada foto deve ter no máximo 4096 × 4096 pixels.']);
        } else {
            $this->validate(['photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4096,max_height=4096'], 'photo_kind' => ['required', Rule::in(['capa', 'galeria', 'interno', 'externo', 'evento', 'detalhe', 'antes', 'durante', 'depois'])], 'photo_context' => ['required', Rule::in(['vehicle', 'new_modification', 'existing_modification', 'maintenance'])]], ['photo.required' => 'Escolha uma foto.', 'photo.image' => 'Envie uma imagem válida.', 'photo.mimes' => 'Use JPEG, PNG ou WebP.', 'photo.max' => 'A foto deve ter até 5 MB.', 'photo.dimensions' => 'A foto deve ter no máximo 4096 × 4096 pixels.']);
        }
        if ($this->photo_context === 'existing_modification' && ! $this->photo_modification_id) {
            $this->addError('photo_modification_id', 'Selecione a modificação que receberá estas fotos.');

            return;
        }
        $storedPaths = [];
        try {
            DB::transaction(function () use ($vehicle, $uploads, $images, &$storedPaths): void {
                if ($this->photo_context === 'vehicle') {
                    foreach ($uploads as $index => $upload) {
                        $path = $images->store($upload);
                        $storedPaths[] = $path;
                        $vehicle->photos()->create(['path' => $path, 'kind' => $this->photo_kind, 'sort_order' => $vehicle->photos()->count() + $index, 'is_primary' => ! $vehicle->photos()->where('is_primary', true)->exists() && $index === 0]);
                    }

                    return;
                }

                $project = $this->mainProject($vehicle);
                if ($this->photo_context === 'new_modification') {
                    $this->photo_context_title = trim($this->photo_context_title);
                    if ($this->photo_context_title === '') {
                        throw ValidationException::withMessages(['photo_context_title' => 'Dê um nome para a modificação.']);
                    }
                    $target = $project->modifications()->create(['title' => $this->photo_context_title, 'category' => 'outro', 'status' => 'planned', 'is_public' => false, 'sort_order' => ((int) $project->modifications()->max('sort_order')) + 1]);
                } elseif ($this->photo_context === 'existing_modification') {
                    $target = $project->modifications()->find($this->photo_modification_id);
                    abort_unless($target, 404);
                } else {
                    $this->photo_context_title = trim($this->photo_context_title);
                    if ($this->photo_context_title === '') {
                        throw ValidationException::withMessages(['photo_context_title' => 'Dê um nome para o registro de manutenção.']);
                    }
                    $target = $vehicle->entries()->create(['category' => 'maintenance', 'title' => $this->photo_context_title, 'description' => trim($this->photo_context_description) ?: 'Fotos de manutenção do veículo.', 'occurred_on' => now()->toDateString(), 'is_public' => false]);
                }
                $this->storeMediaFiles($target, $uploads, $images, $storedPaths);
            });
        } catch (\Throwable $e) {
            $this->deleteStoredPaths($storedPaths);
            throw $e;
        }
        $count = count($uploads);
        $this->reset('photo', 'photos', 'photo_kind', 'photo_context', 'photo_context_title', 'photo_context_description', 'photo_modification_id');
        $this->photo_kind = 'galeria';
        $this->photo_context = 'vehicle';
        session()->flash('status', $count === 1 ? 'Foto adicionada.' : $count.' fotos adicionadas.');
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

        return view('livewire.garage.details', ['vehicle' => $vehicle, 'project' => $vehicle->projects()->latest('id')->first(), 'vehiclePhotos' => $vehicle->photos()->get(), 'modifications' => $vehicle->projects()->latest('id')->first()?->modifications()->get() ?? collect(), 'entries' => $vehicle->entries()->with('photos')->orderByDesc('occurred_on')->orderByDesc('id')->paginate(10), 'shotRequests' => ShotPurchaseRequest::with('photo.gallery')->where('vehicle_id', $vehicle->id)->where('buyer_id', auth()->id())->where('status', 'approved')->get()])->layout('components.layouts.app');
    }
}
