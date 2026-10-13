<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoCommunitySeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demonstração permitida apenas em ambiente local ou de testes.');
        }
        DB::transaction(function () {
            $people = [
                ['Marina Costa', 'Volkswagen', 'Golf', 'TSI', 2015, '1.4 TSI'],
                ['Rafael Lima', 'Volkswagen', 'Golf', 'GTI', 2017, '2.0 TSI'],
                ['Lucas Almeida', 'Honda', 'Civic', 'Si', 2008, '2.0'],
                ['Beatriz Rocha', 'Volkswagen', 'Polo', 'GTS', 2022, '1.4 TSI'],
                ['André Santos', 'Fiat', 'Uno', 'Mille', 2001, '1.0'],
                ['Camila Ribeiro', 'Renault', 'Sandero', 'RS', 2019, '2.0'],
                ['Bruno Martins', 'Chevrolet', 'Opala', 'Comodoro', 1988, '4.1'],
                ['Julia Ferreira', 'Mazda', 'MX-5', 'Roadster', 2016, '2.0'],
            ];
            foreach ($people as $i => [$name,$brand,$model,$trim,$year,$engine]) {
                $email = 'demo-'.($i + 1).'@motorclub.example';
                $user = User::firstOrNew(['email' => $email]);
                if ($user->exists && ! $user->is_demo) {
                    throw new \RuntimeException('Conta existente não é demonstrativa.');
                }
                $user->name = $name;
                $user->is_demo = true;
                $user->profile_public = true;
                if (! $user->exists) {
                    $user->password = Str::random(64);
                }
                $user->save();
                $vehicle = Vehicle::firstOrNew(['user_id' => $user->id, 'model' => $model]);
                $vehicle->fill(['brand' => $brand, 'model' => $model, 'trim' => $trim, 'model_year' => $year, 'engine' => $engine, 'visibility' => 'public'])->save();
                $vehicle->entries()->firstOrCreate(['title' => 'Exemplo: revisão preventiva'], ['category' => 'maintenance', 'description' => 'Registro fictício para demonstrar a organização do histórico. Não representa um serviço real.', 'occurred_on' => '2026-09-15', 'parts' => 'Filtro de óleo (exemplo)', 'services' => 'Revisão de demonstração', 'cost' => '350.00']);
            }
        });
    }
}
