<?php

namespace App\Http\Controllers;

use App\Models\CarEvent;
use App\Services\PublicReferences;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $mine = $request->boolean('mine') && $request->user();
        $query = CarEvent::with('user');
        if ($mine) {
            $query->where('user_id', $request->user()->id);
        } else {
            $query->where('is_public', true)->where('starts_at', '>=', now());
        }
        if ($request->filled('state')) {
            $query->where('state', $request->string('state')->upper());
        }
        if ($request->filled('q')) {
            $query->where('city', 'like', '%'.mb_substr($request->string('q'), 0, 100).'%');
        }

        return view('events.index', ['references' => PublicReferences::matching('events', $request), 'events' => $query->orderBy('starts_at')->paginate(12)->withQueryString(), 'mine' => $mine]);
    }

    public function create()
    {
        return view('events.form', ['event' => new CarEvent]);
    }

    public function edit(Request $request, CarEvent $event)
    {
        abort_unless($event->user_id === $request->user()->id, 403);

        return view('events.form', compact('event'));
    }

    public function store(Request $request)
    {
        $event = CarEvent::create($this->data($request) + ['user_id' => $request->user()->id]);

        return redirect()->route('events.show', $event)->with('status', 'Evento criado.');
    }

    public function update(Request $request, CarEvent $event)
    {
        abort_unless($event->user_id === $request->user()->id, 403);
        $event->update($this->data($request));

        return redirect()->route('events.show', $event)->with('status', 'Evento atualizado.');
    }

    private function data(Request $request): array
    {
        $data = $request->validate(['title' => 'required|string|max:150', 'category' => ['required', Rule::in(array_keys(config('motorclub.event_categories')))], 'starts_at' => ['required', 'date_format:Y-m-d\TH:i', function ($attribute, $value, $fail) {
            try {
                if (Carbon::createFromFormat('Y-m-d\TH:i', $value, 'America/Sao_Paulo')->lessThanOrEqualTo(now())) {
                    $fail('A data do evento deve estar no futuro.');
                }
            } catch (\Throwable $e) {
                $fail('Informe uma data válida.');
            }
        }], 'city' => 'required|string|max:100', 'state' => ['required', Rule::in(config('motorclub.states'))], 'venue' => 'required|string|max:200', 'organizer' => 'required|string|max:120', 'description' => 'required|string|max:5000', 'is_public' => 'required|boolean']);
        $data['starts_at'] = Carbon::createFromFormat('Y-m-d\TH:i', $data['starts_at'], 'America/Sao_Paulo')->utc();

        return $data;
    }

    public function show(Request $request, CarEvent $event)
    {
        abort_unless($event->is_public || $request->user()?->id === $event->user_id, 404);

        return view('events.show', ['event' => $event, 'shotGalleries' => $event->shotGalleries()->with(['user.photographerProfile', 'photos'])->where('is_public', true)->whereHas('user.photographerProfile', fn ($profile) => $profile->where('is_public', true))->get()]);
    }

    public function destroy(Request $request, CarEvent $event)
    {
        abort_unless($event->user_id === $request->user()->id, 403);
        $event->delete();

        return redirect()->route('events.index', ['mine' => 1])->with('status', 'Evento excluído.');
    }
}
