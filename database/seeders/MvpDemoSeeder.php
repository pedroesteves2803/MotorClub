<?php

namespace Database\Seeders;

use App\Models\CarEvent;
use App\Models\Project;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Workshop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MvpDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demonstração apenas em ambiente local ou testes.');
        }
        $this->call(DemoCommunitySeeder::class);
        DB::transaction(function () {
            $people = User::where('is_demo', true)->where('email', 'like', 'demo-%@motorclub.example')->orderBy('id')->get();
            $cities = ['São Paulo', 'Campinas', 'Curitiba', 'São Paulo', 'Belo Horizonte', 'Rio de Janeiro', 'Curitiba', 'Campinas'];
            $workshops = collect();
            foreach ($people as $i => $user) {
                $user->forceFill(['profile_public' => true, 'city' => $cities[$i % 8], 'bio' => 'Perfil fictício de demonstração. Apaixonado por carros, encontros e projetos bem cuidados. Compartilho aqui ideias de manutenção e histórias da garagem.'])->save();
                if ($i < 3) {
                    $workshops->push(Workshop::updateOrCreate(['user_id' => $user->id], ['name' => ['Box 014 · Demonstração', 'Garagem Horizonte · Demonstração', 'Oficina Raiz · Demonstração'][$i], 'city' => $cities[$i], 'state' => $i === 2 ? 'PR' : 'SP', 'specialties' => ['Revisão preventiva, motores TSI e diagnóstico', 'Suspensão, freios e preparação de rua', 'Restauração, acabamento e mecânica clássica'][$i], 'description' => 'Oficina fictícia, criada para demonstrar o catálogo, o portfólio autorizado e os pedidos de orçamento. Não presta serviços reais.', 'is_public' => true]));
                    $user->forceFill(['account_type' => 'workshop'])->save();
                }
            }
            foreach ($people as $i => $user) {
                $vehicle = Vehicle::where('user_id', $user->id)->first();
                if (! $vehicle) {
                    continue;
                }
                Project::updateOrCreate(['vehicle_id' => $vehicle->id, 'title' => $vehicle->model.' · Projeto de demonstração'], ['category' => ['maintenance', 'performance', 'restoration'][$i % 3], 'status' => ['completed', 'ongoing', 'planning'][$i % 3], 'description' => 'Projeto fictício: dar uma nova fase ao '.$vehicle->model.' com revisão completa, cuidado com o acabamento e melhorias para uso no dia a dia. Todas as informações, peças e valores são exemplos para testar o MotorClub.', 'parts' => 'Filtros, fluidos e componentes de desgaste (exemplos). Rodas e acabamento conforme avaliação técnica.', 'services' => 'Diagnóstico inicial, revisão preventiva, alinhamento e avaliação final. Serviços simulados.', 'cost' => 2400 + $i * 850, 'show_cost' => true, 'is_public' => true, 'workshop_id' => $workshops[($i + 1) % 3]->id, 'workshop_status' => 'approved']);
            }
            foreach (['Café & Garagem', 'Clássicos ao pôr do sol', 'Domingo dos projetos'] as $i => $name) {
                CarEvent::updateOrCreate(['user_id' => $people[$i]->id, 'title' => $name.' · Demonstração'], ['category' => 'meet', 'starts_at' => now()->addDays(7 + 7 * $i)->setTime(13, 0), 'city' => $cities[$i], 'state' => $i === 2 ? 'PR' : 'SP', 'venue' => 'Local fictício — evento de teste', 'organizer' => 'Comunidade MotorClub (simulação)', 'description' => 'Evento fictício para testar a agenda. Programação de exemplo: recepção, exposição dos projetos e conversa sobre manutenção. Não existe encontro real nesta data.', 'is_public' => true]);
            }
        });
    }
}
