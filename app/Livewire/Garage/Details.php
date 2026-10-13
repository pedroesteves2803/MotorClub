<?php

namespace App\Livewire\Garage;

use App\Models\FeatureSetting;
use App\Models\ShotPurchaseRequest;
use App\Models\Vehicle;
use App\Services\VehicleImages;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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

    public function uploadPhoto(VehicleImages $images): void
    {
        $this->ensureFeatureEnabled();
        $vehicle = $this->vehicle();
        $uploads = $this->photos ?: ($this->photo ? [$this->photo] : []);
        if ($vehicle->photos()->count() + count($uploads) > 12) {
            $this->addError($this->photos ? 'photos' : 'photo', 'O limite é de 12 fotos por veículo.');

            return;
        }
        if (! $uploads) {
            $this->addError('photos', 'Escolha pelo menos uma foto.');

            return;
        }
        if ($this->photos) {
            $this->validate(['photos' => ['array', 'max:12'], 'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4096,max_height=4096']], ['photos.*.image' => 'Envie imagens válidas.', 'photos.*.mimes' => 'Use JPEG, PNG ou WebP.', 'photos.*.max' => 'Cada foto deve ter até 5 MB.', 'photos.*.dimensions' => 'Cada foto deve ter no máximo 4096 × 4096 pixels.']);
        } else {
            $this->validate(['photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4096,max_height=4096']], ['photo.required' => 'Escolha uma foto.', 'photo.image' => 'Envie uma imagem válida.', 'photo.mimes' => 'Use JPEG, PNG ou WebP.', 'photo.max' => 'A foto deve ter até 5 MB.', 'photo.dimensions' => 'A foto deve ter no máximo 4096 × 4096 pixels.']);
        }
        $storedPaths = [];
        try {
            foreach ($uploads as $upload) {
                $path = $images->store($upload);
                $storedPaths[] = $path;
                $vehicle->photos()->create(['path' => $path]);
            }
        } catch (\Throwable $e) {
            foreach ($storedPaths as $path) {
                Storage::disk(config('filesystems.private_disk'))->delete($path);
            }
            throw $e;
        }
        $this->reset('photo', 'photos');
        session()->flash('status', count($uploads) === 1 ? 'Foto adicionada.' : count($uploads).' fotos adicionadas.');
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

    public function render()
    {
        $this->ensureFeatureEnabled();
        $vehicle = $this->vehicle();

        return view('livewire.garage.details', ['vehicle' => $vehicle, 'photos' => $vehicle->photos()->get(), 'entries' => $vehicle->entries()->orderByDesc('occurred_on')->orderByDesc('id')->paginate(10), 'shotRequests' => ShotPurchaseRequest::with('photo.gallery')->where('vehicle_id', $vehicle->id)->where('buyer_id', auth()->id())->where('status', 'approved')->get()])->layout('components.layouts.app');
    }
}
