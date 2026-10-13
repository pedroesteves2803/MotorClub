<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Models\ProjectPhoto;
use App\Models\ProjectReport;
use App\Models\Vehicle;
use App\Models\Workshop;
use App\Services\AI\AIReportGeneratorInterface;
use App\Services\VehicleImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $mine = $request->boolean('mine') && $request->user();
        $query = Project::with(['vehicle.user', 'photos', 'vehicle.photos']);
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

    public function show(Project $project)
    {
        abort_unless(Gate::allows('view', $project), 404);
        $project->load('photos', 'vehicle.user', 'workshop');
        $reports = Gate::allows('update', $project)
            ? $project->reports()->latest('version')->get()
            : $project->reports()->where('status', 'approved')->where('is_public', true)->latest('version')->get();

        return response()->view('projects.show', compact('project', 'reports'))->header('Cache-Control', 'private, no-store');
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
        abort_if($project->photos()->count() >= 12, 422, 'Limite de 12 fotos.');
        $request->validate(['photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=4096,max_height=4096']);
        $path = $images->store($request->file('photo'));
        try {
            $project->photos()->create(['path' => $path]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return back()->with('status', 'Foto adicionada.');
    }

    public function deletePhoto(ProjectPhoto $photo)
    {
        Gate::authorize('update', $photo->project);
        abort_unless(Storage::disk('local')->delete($photo->path), 500);
        $photo->delete();

        return back()->with('status', 'Foto excluída.');
    }

    public function image(ProjectPhoto $photo)
    {
        abort_unless(Gate::allows('view', $photo->project), 404);

        return Storage::disk('local')->response($photo->path, null, ['Content-Type' => 'image/webp', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
