<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectPart;
use Illuminate\Http\Request;

class DiscoveryController extends Controller
{
    public function projects(Request $request)
    {
        $query = Project::published()->with(['vehicle', 'previewPhoto']);
        $this->applyVehicleFilters($query, $request);
        if ($request->filled('q')) {
            $term = mb_substr($request->string('q'), 0, 100);
            $query->where(fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"));
        }

        return view('discovery.projects', ['projects' => $query->latest()->paginate(12)->withQueryString(), 'filters' => $request->only(['q', 'brand', 'model', 'vehicle_type'])]);
    }

    public function parts(Request $request)
    {
        $query = ProjectPart::query()->where('is_public', true)->whereHas('modification.project', fn ($q) => $q->published())->with(['modification.project.vehicle', 'photos']);
        if ($request->filled('q')) {
            $term = mb_substr($request->string('q'), 0, 100);
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('brand', 'like', "%{$term}%")->orWhere('model', 'like', "%{$term}%"));
        }
        if ($request->filled('brand')) {
            $query->where('brand', $request->string('brand'));
        }
        if ($request->filled('vehicle_type')) {
            $query->whereHas('modification.project.vehicle', fn ($q) => $q->where('vehicle_type', $request->string('vehicle_type')));
        }

        return view('discovery.parts', ['parts' => $query->latest()->paginate(18)->withQueryString(), 'filters' => $request->only(['q', 'brand', 'vehicle_type'])]);
    }

    public function part(ProjectPart $part)
    {
        abort_unless($part->is_public && $part->modification->project->publiclyVisible(), 404);
        $part->load(['modification.project.vehicle', 'photos']);

        return view('discovery.part', compact('part'));
    }

    private function applyVehicleFilters($query, Request $request): void
    {
        foreach (['brand', 'model', 'vehicle_type'] as $field) {
            if ($request->filled($field)) {
                $query->whereHas('vehicle', fn ($q) => $q->where($field, $request->string($field)));
            }
        }
    }
}
