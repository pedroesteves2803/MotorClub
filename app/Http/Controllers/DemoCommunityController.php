<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DemoCommunityController extends Controller
{
    public function index(Request $request)
    {
        $people = collect(config('production.demo_enabled') ? config('demo_garages') : [])->map(function ($garage, $slug) {
            return [
                'name' => $garage['name'],
                'label' => 'Perfil de demonstração · Não oficial',
                'avatar' => asset('images/demo/'.$garage['avatar']),
                'vehicles' => collect($garage['cars'])->map(fn ($car) => [
                    'name' => $car['brand'].' '.$car['model'],
                    'image' => $car['image'] ? asset('images/demo/'.$car['image']) : null,
                    'details' => config('demo_vehicle_details.'.$car['demo_key']),
                    'credit' => $car['credit'],
                    'post' => $car['post'],
                    'url' => route('community.garage', $slug),
                    'note' => $car['image'] ? 'Foto real · Referências com fontes' : 'Foto do carro pendente de confirmação',
                ]),
            ];
        })->values();

        $demoPeople = User::where('profile_public', true)->when(! config('production.demo_enabled'), fn ($query) => $query->where('is_demo', false))->orderBy('id')->get()->map(function ($person) {
            return [
                'name' => $person->name,
                'label' => $person->is_demo ? 'Perfil fictício · Entusiasta' : ($person->account_type === 'workshop' ? 'Profissional de oficina' : 'Entusiasta'),
                'url' => route('profiles.show', $person),
                'avatar' => null,
                'vehicles' => Vehicle::with('photos')->where('user_id', $person->id)->where('visibility', 'public')->get()->map(fn ($vehicle) => [
                    'name' => $vehicle->brand.' '.$vehicle->model.' '.$vehicle->trim.' · '.$vehicle->model_year,
                    'details' => $person->is_demo ? config('demo_vehicle_details.'.strtolower($vehicle->model).($vehicle->trim === 'GTI' ? '-gti' : '')) : null,
                    'image' => $vehicle->photos->isNotEmpty() ? route('vehicle-photos.show', $vehicle->photos->first()) : null,
                    'url' => route('vehicles.show', $vehicle),
                    'note' => $person->is_demo ? 'Veículo de demonstração · '.($vehicle->photos->isNotEmpty() ? 'Imagem gerada por IA' : 'Sem foto real') : 'Veículo público da comunidade',
                ]),
            ];
        });

        $people = $people->concat($demoPeople);
        if ($request->filled('q')) {
            $term = mb_strtolower(mb_substr($request->string('q'), 0, 100));
            $people = $people->filter(fn ($person) => str_contains(mb_strtolower($person['name'].' '.$person['vehicles']->pluck('name')->implode(' ')), $term));
        }

        return view('community', ['people' => $people]);
    }

    public function garage(string $slug)
    {
        abort_unless(config('production.demo_enabled'), 404);
        $garages = config('demo_garages');
        abort_unless(isset($garages[$slug]), 404);

        return view('demo-garage', ['garage' => $garages[$slug]]);
    }
}
