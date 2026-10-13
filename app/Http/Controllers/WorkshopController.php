<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Vehicle;
use App\Models\Workshop;
use App\Services\PublicReferences;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkshopController extends Controller
{
    public function index(Request $request)
    {
        $query = Workshop::with('user')->where('is_public', true);
        if ($request->filled('q')) {
            $term = mb_substr($request->string('q'), 0, 100);
            $query->where(fn ($q) => $q->where('name', 'like', "%$term%")->orWhere('city', 'like', "%$term%")->orWhere('specialties', 'like', "%$term%"));
        }

        return view('workshops.index', ['references' => PublicReferences::matching('workshops', $request), 'workshops' => $query->orderBy('name')->paginate(12)->withQueryString()]);
    }

    public function show(Request $request, Workshop $workshop)
    {
        abort_unless($workshop->is_public || $request->user()?->id === $workshop->user_id, 404);

        return view('workshops.show', ['workshop' => $workshop, 'projects' => $workshop->projects()->published()->where('workshop_status', 'approved')->with('vehicle')->get(), 'vehicles' => $request->user() ? Vehicle::where('user_id', $request->user()->id)->get() : collect()]);
    }

    public function edit(Request $request)
    {
        $workshop = Workshop::firstOrNew(['user_id' => $request->user()->id]);

        return view('workshops.form', ['workshop' => $workshop, 'requests' => $workshop->exists ? $workshop->projects()->with('vehicle')->get() : collect()]);
    }

    public function save(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:120', 'city' => 'required|string|max:100', 'state' => ['required', Rule::in(config('motorclub.states'))], 'specialties' => 'required|string|max:250', 'description' => 'required|string|max:5000', 'whatsapp' => ['nullable', 'regex:/^55[1-9][0-9]{9,10}$/'], 'is_public' => 'required|boolean']);
        Workshop::updateOrCreate(['user_id' => $request->user()->id], $data);
        $request->user()->forceFill(['account_type' => 'workshop'])->save();

        return back()->with('status', 'Oficina salva.');
    }

    public function association(Request $request, Project $project)
    {
        abort_unless($project->workshop && $project->workshop->user_id === $request->user()->id, 403);
        $data = $request->validate(['workshop_status' => ['required', Rule::in(['approved', 'rejected'])]]);
        $project->update($data);

        return back()->with('status', 'Autorização atualizada.');
    }

    public function destroy(Request $request)
    {
        Workshop::where('user_id', $request->user()->id)->delete();
        $request->user()->forceFill(['account_type' => 'enthusiast'])->save();

        return redirect()->route('workshops.index')->with('status', 'Oficina excluída.');
    }
}
