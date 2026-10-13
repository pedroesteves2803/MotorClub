<?php

namespace App\Livewire\Garage;

use App\Models\FeatureSetting;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Locked]
    public ?int $editingId = null;

    public bool $formOpen = false;

    public string $brand = '';

    public string $model = '';

    public string $trim = '';

    public string $model_year = '';

    public string $engine = '';

    public string $visibility = 'private';

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
        foreach (['brand', 'model', 'trim', 'model_year', 'engine', 'visibility'] as $field) {
            $this->$field = (string) $vehicle->$field;
        }
        $this->formOpen = true;
    }

    public function save(): void
    {
        $this->ensureFeatureEnabled();
        $vehicle = $this->editingId ? Vehicle::findOrFail($this->editingId) : new Vehicle;
        if ($vehicle->exists) {
            Gate::authorize('update', $vehicle);
        }
        foreach (['brand', 'model', 'trim', 'engine'] as $field) {
            $this->$field = trim($this->$field);
        }
        $data = $this->validate([
            'brand' => ['required', 'string', 'max:80'],
            'model' => ['required', 'string', 'max:80'],
            'trim' => ['required', 'string', 'max:100'],
            'model_year' => ['required', 'integer', 'min:1886', 'max:'.(now()->year + 1)],
            'engine' => ['required', 'string', 'max:100'],
            'visibility' => ['required', Rule::in(['private', 'public'])],
        ], [
            'required' => 'Preencha o campo :attribute.',
            'max' => 'O campo :attribute deve respeitar o limite de :max.',
            'min' => 'O ano deve ser igual ou posterior a :min.',
            'integer' => 'Informe um ano válido.',
            'in' => 'Selecione uma opção válida.',
        ], ['brand' => 'marca', 'model' => 'modelo', 'trim' => 'versão', 'model_year' => 'ano', 'engine' => 'motorização', 'visibility' => 'visibilidade']);
        $vehicle->fill($data);
        if (! $vehicle->exists) {
            $vehicle->user_id = Auth::id();
        }
        $vehicle->save();
        $this->resetForm();
        $this->resetPage();
        session()->flash('status', 'Veículo salvo com sucesso.');
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
        $this->reset('editingId', 'formOpen', 'brand', 'model', 'trim', 'model_year', 'engine', 'visibility');
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
