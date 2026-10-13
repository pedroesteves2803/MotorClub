<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\MediaPhoto;
use App\Models\Project;
use App\Models\ProjectModification;
use App\Models\ProjectPart;
use App\Models\ProjectPhoto;
use App\Models\ProjectReport;
use App\Models\Vehicle;
use App\Models\Workshop;
use App\Presenters\ProjectMagazinePresenter;
use App\Services\AI\AIReportGeneratorInterface;
use App\Services\VehicleImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $mine = $request->boolean('mine') && $request->user();
        $query = Project::with(['previewPhoto', 'vehicle.previewPhoto']);
        if ($mine) {
            $query->whereHas('vehicle', fn ($q) => $q->where('user_id', $request->user()->id));
        } else {
            $query->published();
        }
        if ($request->filled('q')) {
            $term = mb_substr($request->string('q'), 0, 100);
            $query->where(fn ($q) => $q->where('title', 'like', "%$term%")->orWhereHas('vehicle', fn ($v) => $v->where('brand', 'like', "%$term%")->orWhere('model', 'like', "%$term%")));
        }

        return view('projects.index', ['projects' => $query->latest()->paginate(12)->withQueryString(), 'mine' => $mine]);
    }

    public function create(Request $request)
    {
        return $this->form($request, new Project);
    }

    public function edit(Request $request, Project $project)
    {
        Gate::authorize('update', $project);

        return $this->form($request, $project);
    }

    private function form(Request $request, Project $project)
    {
        $project->load(['photos', 'modifications.parts.photos', 'modifications.photos']);

        return view('projects.form', ['project' => $project, 'vehicles' => Vehicle::where('user_id', $request->user()->id)->get(), 'workshops' => Workshop::where('is_public', true)->get()]);
    }

    public function store(ProjectRequest $request)
    {
        $project = Project::create($request->validated() + ['workshop_status' => 'pending']);

        return redirect()->route('projects.edit', $project)->with('status', 'Projeto criado. Agora você pode adicionar fotos.');
    }

    public function update(ProjectRequest $request, Project $project)
    {
        Gate::authorize('update', $project);
        $data = $request->validated();
        $data['workshop_status'] = 'pending';
        $project->update($data);

        return back()->with('status', 'Projeto atualizado.');
    }

    public function show(Project $project, ProjectMagazinePresenter $magazinePresenter)
    {
        abort_unless(Gate::allows('view', $project), 404);
        $ownerCanEdit = Gate::allows('update', $project);
        $project->load('photos', 'mediaPhotos', 'modifications.parts.photos', 'modifications.photos', 'vehicle.user', 'vehicle.photos', 'vehicle.entries', 'workshop');
        if (! $ownerCanEdit) {
            $project->vehicle->setRelation('entries', $project->vehicle->entries->where('is_public', true)->values());
        }
        $reports = $ownerCanEdit
            ? $project->reports()->latest('version')->get()
            : $project->reports()->where('status', 'approved')->where('is_public', true)->latest('version')->get();
        $magazine = $magazinePresenter->present($project, $ownerCanEdit);

        return response()->view('projects.show', compact('project', 'reports', 'magazine'))->header('Cache-Control', 'private, no-store');
    }

    public function showModification(Project $project, ProjectModification $modification)
    {
        abort_unless($modification->project_id === $project->id, 404);
        abort_unless(Gate::allows('view', $project), 404);
        $ownerCanEdit = Gate::allows('update', $project);
        abort_unless($ownerCanEdit || $modification->is_public, 404);
        $modification->load(['photos', 'parts.photos']);
        if (! $ownerCanEdit) {
            $modification->setRelation('photos', $modification->photos->where('is_public', true)->values());
            $modification->setRelation('parts', $modification->parts->where('is_public', true)->values());
        }

        return response()->view('projects.modifications.show', compact('project', 'modification'))->header('Cache-Control', 'private, no-store');
    }

    public function showPart(Project $project, ProjectModification $modification, ProjectPart $part)
    {
        abort_unless($modification->project_id === $project->id && $part->project_modification_id === $modification->id, 404);
        abort_unless(Gate::allows('view', $project), 404);
        $ownerCanEdit = Gate::allows('update', $project);
        abort_unless($ownerCanEdit || ($modification->is_public && $part->is_public), 404);
        $part->load('photos');
        if (! $ownerCanEdit) {
            $part->setRelation('photos', $part->photos->where('is_public', true)->values());
        }

        return response()->view('projects.parts.show', compact('project', 'modification', 'part'))->header('Cache-Control', 'private, no-store');
    }

    public function generateReport(Project $project, AIReportGeneratorInterface $generator)
    {
        Gate::authorize('update', $project);
        $result = $generator->generate($project);
        $version = ((int) $project->reports()->max('version')) + 1;
        $report = $project->reports()->create($result + ['version' => $version, 'generator' => config('motorclub.ai_provider')]);

        return redirect()->route('projects.reports.edit', [$project, $report])->with('status', "Relatório demonstrativo v{$version} gerado. Revise antes de aprovar ou publicar.");
    }

    public function editReport(Project $project, ProjectReport $report)
    {
        Gate::authorize('update', $project);
        abort_unless($report->project_id === $project->id, 404);

        return view('projects.reports.edit', compact('project', 'report'));
    }

    public function updateReport(Request $request, Project $project, ProjectReport $report)
    {
        Gate::authorize('update', $project);
        abort_unless($report->project_id === $project->id, 404);
        $data = $request->validate([
            'sections' => ['required', 'array'],
            'sections.*.content' => ['required', 'string', 'max:5000'],
            'status' => ['required', 'in:draft,approved'],
            'is_public' => ['required', 'boolean'],
        ]);
        $sections = $report->sections;
        foreach ($sections as $key => $section) {
            $sections[$key]['content'] = $data['sections'][$key]['content'] ?? $section['content'];
        }
        $report->update([
            'sections' => $sections,
            'status' => $data['status'],
            'is_public' => $data['is_public'],
        ]);

        return redirect()->route('projects.show', $project)->with('status', 'Relatório atualizado.');
    }

    public function showReport(Project $project, ProjectReport $report)
    {
        abort_unless($report->project_id === $project->id, 404);
        abort_unless(Gate::allows('update', $project) || $report->publiclyVisible(), 404);

        return response()->view('projects.reports.show', compact('project', 'report'))->header('Cache-Control', 'private, no-store');
    }

    public function destroy(Project $project)
    {
        Gate::authorize('update', $project);
        $project->delete();

        return redirect()->route('projects.index', ['mine' => 1])->with('status', 'Projeto excluído.');
    }

    public function photo(Request $request, Project $project, VehicleImages $images)
    {
        Gate::authorize('update', $project);
        $files = $request->file('photos', []);
        if (! is_array($files)) {
            $files = [$files];
        }
        if ($files === [] && $request->hasFile('photo')) {
            $files = [$request->file('photo')];
        }
        abort_if($project->photos()->count() + count($files) > 12, 422, 'Limite de 12 fotos.');
        if ($request->hasFile('photo') && ! $request->hasFile('photos')) {
            $request->validate(['photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=4096,max_height=4096']);
        } else {
            Validator::make(['photos' => $files], ['photos' => ['required', 'array', 'max:12'], 'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4096,max_height=4096']], ['photos.required' => 'Escolha pelo menos uma foto.', 'photos.*.image' => 'Envie imagens válidas.', 'photos.*.mimes' => 'Use JPEG, PNG ou WebP.', 'photos.*.max' => 'Cada foto deve ter até 5 MB.', 'photos.*.dimensions' => 'Cada foto deve ter no máximo 4096 × 4096 pixels.'])->validate();
        }
        $storedPaths = [];
        try {
            $kind = $request->validate(['kind' => ['nullable', 'string', 'max:30']])['kind'] ?? 'editorial';
            $caption = $request->validate(['caption' => ['nullable', 'string', 'max:255']])['caption'] ?? null;
            foreach ($files as $index => $file) {
                $path = $images->store($file);
                $storedPaths[] = $path;
                $project->photos()->create([
                    'path' => $path,
                    'kind' => $kind,
                    'caption' => $caption,
                    'sort_order' => $project->photos()->count() + $index,
                    'is_primary' => ! $project->photos()->where('is_primary', true)->exists() && $index === 0,
                ]);
            }
        } catch (\Throwable $e) {
            foreach ($storedPaths as $path) {
                Storage::disk(config('filesystems.private_disk'))->delete($path);
            }
            throw $e;
        }

        return back()->with('status', count($files) === 1 ? 'Foto adicionada.' : count($files).' fotos adicionadas.');
    }

    public function deletePhoto(ProjectPhoto $photo)
    {
        Gate::authorize('update', $photo->project);
        abort_unless(Storage::disk(config('filesystems.private_disk'))->delete($photo->path), 500);
        $photo->delete();

        return back()->with('status', 'Foto excluída.');
    }

    public function updatePhoto(Request $request, ProjectPhoto $photo)
    {
        Gate::authorize('update', $photo->project);
        $data = $request->validate([
            'kind' => ['required', 'string', 'max:30'],
            'caption' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);
        if ($request->boolean('is_primary')) {
            $photo->project->photos()->update(['is_primary' => false]);
        }
        $photo->update($data + ['is_primary' => $request->boolean('is_primary')]);

        return back()->with('status', 'Metadados da foto atualizados.');
    }

    public function storeModification(Request $request, Project $project)
    {
        Gate::authorize('update', $project);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:planned,in_progress,completed'],
            'description' => ['nullable', 'string', 'max:5000'],
            'started_on' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'completed_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:started_on', 'before_or_equal:today'],
            'is_public' => ['sometimes', 'boolean'],
        ]);
        $data['is_public'] = $request->boolean('is_public');
        $data['sort_order'] = ((int) $project->modifications()->max('sort_order')) + 1;
        $project->modifications()->create($data);

        return back()->with('status', 'Modificação adicionada ao projeto.');
    }

    public function updateModification(Request $request, Project $project, ProjectModification $modification)
    {
        $this->authorizeModification($project, $modification);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:planned,in_progress,completed'],
            'description' => ['nullable', 'string', 'max:5000'],
            'started_on' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'completed_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:started_on', 'before_or_equal:today'],
            'is_public' => ['sometimes', 'boolean'],
        ]);
        $data['is_public'] = $request->boolean('is_public');
        $modification->update($data);

        return back()->with('status', 'Modificação atualizada.');
    }

    public function deleteModification(Project $project, ProjectModification $modification)
    {
        $this->authorizeModification($project, $modification);
        $modification->delete();

        return back()->with('status', 'Modificação excluída.');
    }

    public function storePart(Request $request, Project $project, ProjectModification $modification)
    {
        $this->authorizeModification($project, $modification);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'in:planned,in_progress,installed'],
            'description' => ['nullable', 'string', 'max:3000'],
            'is_public' => ['sometimes', 'boolean'],
        ]);
        $data['is_public'] = $request->boolean('is_public');
        $data['sort_order'] = ((int) $modification->parts()->max('sort_order')) + 1;
        $modification->parts()->create($data);

        return back()->with('status', 'Peça adicionada à modificação.');
    }

    public function updatePart(Request $request, Project $project, ProjectModification $modification, ProjectPart $part)
    {
        $this->authorizePart($project, $modification, $part);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'in:planned,in_progress,installed'],
            'description' => ['nullable', 'string', 'max:3000'],
            'is_public' => ['sometimes', 'boolean'],
        ]);
        $data['is_public'] = $request->boolean('is_public');
        $part->update($data);

        return back()->with('status', 'Peça atualizada.');
    }

    public function deletePart(Project $project, ProjectModification $modification, ProjectPart $part)
    {
        $this->authorizePart($project, $modification, $part);
        $part->delete();

        return back()->with('status', 'Peça excluída.');
    }

    public function modificationPhoto(Request $request, Project $project, ProjectModification $modification, VehicleImages $images)
    {
        $this->authorizeModification($project, $modification);

        return $this->storeMedia($request, $modification, $images, 'Fotos desta modificação');
    }

    public function partPhoto(Request $request, Project $project, ProjectModification $modification, ProjectPart $part, VehicleImages $images)
    {
        $this->authorizePart($project, $modification, $part);

        return $this->storeMedia($request, $part, $images, 'Fotos desta peça');
    }

    public function updateMedia(Request $request, MediaPhoto $photo)
    {
        $project = $this->mediaProject($photo);
        Gate::authorize('update', $project);
        $data = $request->validate([
            'kind' => ['required', 'string', 'max:30'],
            'caption' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_primary' => ['sometimes', 'boolean'],
            'is_public' => ['sometimes', 'boolean'],
        ]);
        if ($request->boolean('is_primary')) {
            $photo->imageable->photos()->update(['is_primary' => false]);
        }
        $photo->update($data + ['is_primary' => $request->boolean('is_primary'), 'is_public' => $request->boolean('is_public')]);

        return back()->with('status', 'Metadados da imagem atualizados.');
    }

    public function deleteMedia(MediaPhoto $photo)
    {
        Gate::authorize('update', $this->mediaProject($photo));
        $photo->delete();

        return back()->with('status', 'Imagem excluída.');
    }

    public function mediaImage(MediaPhoto $photo)
    {
        $project = $this->mediaProject($photo);
        $owner = Gate::allows('update', $project);
        abort_unless($owner || ($photo->is_public && $project->publiclyVisible()), 404);
        abort_unless(Storage::disk(config('filesystems.private_disk'))->exists($photo->path), 404);

        return Storage::disk(config('filesystems.private_disk'))->response($photo->path, null, ['Content-Type' => 'image/webp', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    private function authorizeModification(Project $project, ProjectModification $modification): void
    {
        Gate::authorize('update', $project);
        abort_unless($modification->project_id === $project->id, 404);
    }

    private function authorizePart(Project $project, ProjectModification $modification, ProjectPart $part): void
    {
        $this->authorizeModification($project, $modification);
        abort_unless($part->project_modification_id === $modification->id, 404);
    }

    private function storeMedia(Request $request, Model $imageable, VehicleImages $images, string $label)
    {
        $files = $request->file('photos', []);
        if (! is_array($files)) {
            $files = [$files];
        }
        if ($files === [] && $request->hasFile('photo')) {
            $files = [$request->file('photo')];
        }
        abort_if($imageable->photos()->count() + count($files) > 12, 422, 'Limite de 12 fotos neste álbum.');
        Validator::make(['photos' => $files], [
            'photos' => ['required', 'array', 'max:12'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4096,max_height=4096'],
        ], ['photos.required' => 'Escolha pelo menos uma foto.', 'photos.*.image' => 'Envie imagens válidas.', 'photos.*.mimes' => 'Use JPEG, PNG ou WebP.', 'photos.*.max' => 'Cada foto deve ter até 5 MB.', 'photos.*.dimensions' => 'Cada foto deve ter no máximo 4096 × 4096 pixels.'])->validate();
        $metadata = $request->validate([
            'kind' => ['nullable', 'string', 'max:30'],
            'caption' => ['nullable', 'string', 'max:255'],
            'is_public' => ['sometimes', 'boolean'],
        ]);
        $stored = [];
        try {
            foreach ($files as $index => $file) {
                $path = $images->store($file, 'media');
                $stored[] = $path;
                $imageable->photos()->create([
                    'path' => $path,
                    'kind' => $metadata['kind'] ?? 'galeria',
                    'caption' => $metadata['caption'] ?? null,
                    'sort_order' => $imageable->photos()->count() + $index,
                    'is_primary' => ! $imageable->photos()->where('is_primary', true)->exists() && $index === 0,
                    'is_public' => (bool) ($metadata['is_public'] ?? false),
                ]);
            }
        } catch (\Throwable $e) {
            foreach ($stored as $path) {
                Storage::disk(config('filesystems.private_disk'))->delete($path);
            }
            throw $e;
        }

        return back()->with('status', count($files).' '.mb_strtolower($label).' adicionadas.');
    }

    private function mediaProject(MediaPhoto $photo): Project
    {
        $imageable = $photo->imageable;
        $project = match (true) {
            $imageable instanceof Project => $imageable,
            $imageable instanceof ProjectModification => $imageable->project,
            $imageable instanceof ProjectPart => $imageable->modification->project,
            default => null,
        };
        abort_unless($project instanceof Project, 404);

        return $project;
    }

    public function image(ProjectPhoto $photo)
    {
        abort_unless(Gate::allows('view', $photo->project), 404);
        abort_unless(Storage::disk(config('filesystems.private_disk'))->exists($photo->path), 404);

        return Storage::disk(config('filesystems.private_disk'))->response($photo->path, null, ['Content-Type' => 'image/webp', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
