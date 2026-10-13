<?php

namespace App\Livewire\Garage;

use App\Models\FeatureSetting;
use App\Models\Vehicle;
use App\Services\VehicleImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads, WithPagination;

    #[Locked]
    public ?int $editingId = null;

    public bool $formOpen = false;

    public string $brand = '';

    public string $model = '';

    public string $public_name = '';

    public string $trim = '';

    public string $generation = '';

    public string $color = '';

    public string $model_year = '';

    public string $engine = '';

    public string $mileage = '';

    public bool $mileage_approximate = false;

    public string $acquired_on = '';

    public string $location = '';

    public string $instagram_url = '';

    public string $visibility = 'private';

    public bool $project_enabled = true;

    #[Locked]
    public ?int $projectId = null;

    public string $project_title = '';

    public string $project_category = 'maintenance';

    public string $project_status = 'planning';

    public string $project_description = '';

    public string $project_parts = '';

    public string $project_services = '';

    public string $project_cost = '';

    public bool $project_show_cost = false;

    public bool $project_is_public = false;

    public array $photos = [];

    public function mount(Request $request): void
    {
        if ($request->boolean('novo')) {
            $this->create();
        }
    }

    private function ensureFeatureEnabled(): void
    {
        abort_unless(FeatureSetting::enabled('garage'), 404);
    }

    public function create(): void
    {
        $this->ensureFeatureEnabled();
        $this->resetForm();
        $this->formOpen = true;
    }

    public function edit(int $id): void
    {
        $this->ensureFeatureEnabled();
        $vehicle = Vehicle::findOrFail($id);
        Gate::authorize('update', $vehicle);
        $this->resetValidation();
        $this->editingId = $id;
        foreach (['brand', 'model', 'public_name', 'trim', 'generation', 'color', 'model_year', 'engine', 'mileage', 'mileage_approximate', 'acquired_on', 'location', 'instagram_url', 'visibility'] as $field) {
            $this->$field = (string) $vehicle->$field;
        }
        $this->mileage_approximate = (bool) $vehicle->mileage_approximate;
        $project = $vehicle->projects()->latest('id')->first();
        if ($project) {
            $this->projectId = $project->id;
            $this->project_enabled = true;
            foreach (['title' => 'project_title', 'category' => 'project_category', 'status' => 'project_status', 'description' => 'project_description', 'parts' => 'project_parts', 'services' => 'project_services', 'cost' => 'project_cost'] as $source => $target) {
                $this->$target = (string) $project->$source;
            }
            $this->project_show_cost = (bool) $project->show_cost;
            $this->project_is_public = (bool) $project->is_public;
        }
        $this->formOpen = true;
    }

    public function save(VehicleImages $images): void
    {
        $this->ensureFeatureEnabled();
        $vehicle = $this->editingId ? Vehicle::findOrFail($this->editingId) : new Vehicle;
        if ($vehicle->exists) {
            Gate::authorize('update', $vehicle);
        }
        foreach (['brand', 'model', 'public_name', 'trim', 'generation', 'color', 'engine', 'location', 'instagram_url'] as $field) {
            $this->$field = trim($this->$field);
        }
        $data = $this->validate([
            'brand' => ['required', 'string', 'max:80'],
            'model' => ['required', 'string', 'max:80'],
            'public_name' => ['nullable', 'string', 'max:120'],
            'trim' => ['required', 'string', 'max:100'],
            'generation' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
            'model_year' => ['required', 'integer', 'min:1886', 'max:'.(now()->year + 1)],
            'engine' => ['required', 'string', 'max:100'],
            'mileage' => ['nullable', 'integer', 'min:0', 'max:2000000'],
            'mileage_approximate' => ['required', 'boolean'],
            'acquired_on' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'location' => ['nullable', 'string', 'max:100'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'visibility' => ['required', Rule::in(['private', 'public'])],
            'photos' => ['array', 'max:12'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4096,max_height=4096'],
            'project_enabled' => ['required', 'boolean'],
            'project_title' => ['nullable', 'string', 'max:150'],
            'project_category' => ['nullable', Rule::in(array_keys(config('motorclub.categories')))],
            'project_status' => ['nullable', Rule::in(array_keys(config('motorclub.project_status')))],
            'project_description' => ['nullable', 'string', 'max:5000'],
            'project_parts' => ['nullable', 'string', 'max:2000'],
            'project_services' => ['nullable', 'string', 'max:2000'],
            'project_cost' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'project_show_cost' => ['required', 'boolean'],
            'project_is_public' => ['required', 'boolean'],
        ], [
            'required' => 'Preencha o campo :attribute.',
            'max' => 'O campo :attribute deve respeitar o limite de :max.',
            'min' => 'O ano deve ser igual ou posterior a :min.',
            'integer' => 'Informe um ano válido.',
            'in' => 'Selecione uma opção válida.',
        ], ['brand' => 'marca', 'model' => 'modelo', 'public_name' => 'nome público', 'trim' => 'versão', 'generation' => 'geração', 'color' => 'cor', 'model_year' => 'ano', 'engine' => 'motorização', 'mileage' => 'quilometragem', 'mileage_approximate' => 'quilometragem aproximada', 'acquired_on' => 'data de aquisição', 'location' => 'localização', 'instagram_url' => 'Instagram', 'visibility' => 'visibilidade', 'photos.*' => 'fotos', 'project_title' => 'título do projeto', 'project_description' => 'descrição do projeto', 'project_cost' => 'custo do projeto']);
        if ($this->project_enabled && (trim($this->project_title) !== '' || trim($this->project_description) !== '')) {
            $this->validate([
                'project_title' => ['required', 'string', 'max:150'],
                'project_category' => ['required', Rule::in(array_keys(config('motorclub.categories')))],
                'project_status' => ['required', Rule::in(array_keys(config('motorclub.project_status')))],
                'project_description' => ['required', 'string', 'max:5000'],
            ], ['required' => 'Preencha o campo :attribute.'], ['project_title' => 'título do projeto', 'project_category' => 'categoria do projeto', 'project_status' => 'status do projeto', 'project_description' => 'descrição do projeto']);
        } else {
            $this->project_enabled = false;
        }
        if ($vehicle->exists && $vehicle->photos()->count() + count($this->photos) > 12) {
            $this->addError('photos', 'O limite é de 12 fotos por veículo.');

            return;
        }
        $vehicleData = collect($data)->only(['brand', 'model', 'public_name', 'trim', 'generation', 'color', 'model_year', 'engine', 'mileage', 'mileage_approximate', 'acquired_on', 'location', 'instagram_url', 'visibility'])->all();
        $vehicle->fill($vehicleData);
        if (! $vehicle->exists) {
            $vehicle->user_id = Auth::id();
        }
        $vehicle->save();
        $storedPaths = [];
        try {
            foreach ($this->photos as $photo) {
                $path = $images->store($photo);
                $storedPaths[] = $path;
                $vehicle->photos()->create(['path' => $path]);
            }
            if ($this->project_enabled) {
                $project = $this->projectId ? $vehicle->projects()->find($this->projectId) : null;
                $projectPayload = [
                    'title' => trim($this->project_title),
                    'category' => $this->project_category,
                    'status' => $this->project_status,
                    'description' => trim($this->project_description),
                    'parts' => trim($this->project_parts) ?: null,
                    'services' => trim($this->project_services) ?: null,
                    'cost' => $this->project_cost === '' ? null : $this->project_cost,
                    'show_cost' => $this->project_show_cost,
                    'is_public' => $this->project_is_public,
                    'workshop_status' => 'pending',
                ];
                if ($project) {
                    $project->update($projectPayload);
                } else {
                    $vehicle->projects()->create($projectPayload);
                }
            }
        } catch (\Throwable $e) {
            foreach ($storedPaths as $path) {
                Storage::disk(config('filesystems.private_disk'))->delete($path);
            }
            throw $e;
        }
        $photoCount = count($this->photos);
        $projectSaved = $this->project_enabled;
        $this->resetForm();
        $this->resetPage();
        session()->flash('status', 'Veículo salvo'.($projectSaved ? ' com o projeto principal' : '').($photoCount ? " e {$photoCount} foto(s)" : '').'.');
    }

    public function delete(int $id): void
    {
        $this->ensureFeatureEnabled();
        $vehicle = Vehicle::findOrFail($id);
        Gate::authorize('delete', $vehicle);
        $vehicle->delete();
        $this->resetForm();
        $this->resetPage();
        session()->flash('status', 'Veículo excluído.');
    }

    public function resetForm(): void
    {
        $this->reset('editingId', 'formOpen', 'brand', 'model', 'public_name', 'trim', 'generation', 'color', 'model_year', 'engine', 'mileage', 'mileage_approximate', 'acquired_on', 'location', 'instagram_url', 'visibility', 'projectId', 'project_enabled', 'project_title', 'project_category', 'project_status', 'project_description', 'project_parts', 'project_services', 'project_cost', 'project_show_cost', 'project_is_public', 'photos');
        $this->visibility = 'private';
        $this->project_enabled = true;
        $this->project_category = 'maintenance';
        $this->project_status = 'planning';
        $this->resetValidation();
    }

    public function render()
    {
        $this->ensureFeatureEnabled();

        return view('livewire.garage.index', [
            'vehicles' => Vehicle::with('photos')->where('user_id', Auth::id())->latest('id')->paginate(12),
        ])->layout('components.layouts.app');
    }
}
