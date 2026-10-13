<?php

namespace App\Services\AI;

use App\Models\Project;

class FakeAIReportGenerator implements AIReportGeneratorInterface
{
    public function generate(Project $project): array
    {
        $project->loadMissing('vehicle', 'photos', 'workshop');
        $vehicle = $project->vehicle;
        $missing = [];
        $addMissing = function (bool $condition, string $label) use (&$missing): void {
            if ($condition) {
                $missing[] = $label;
            }
        };
        $addMissing(blank($project->parts), 'Peças, marcas, códigos e especificações');
        $addMissing(blank($project->services), 'Etapas ou serviços executados');
        $addMissing($project->cost === null, 'Custos informados pelo proprietário');
        $addMissing($project->photos->isEmpty(), 'Fotos de antes, durante ou depois');
        $addMissing($project->workshop_id === null, 'Responsável pela execução, quando aplicável');

        $identity = trim("{$vehicle->brand} {$vehicle->model} {$vehicle->trim} · {$vehicle->model_year} · {$vehicle->engine}");
        $cost = $project->cost !== null && $project->show_cost
            ? 'Custo autorizado pelo proprietário: R$ '.number_format($project->cost, 2, ',', '.').'.'
            : 'Nenhum custo autorizado para exibição neste relatório.';
        $workshop = $project->workshop?->name ? "Oficina indicada: {$project->workshop->name}." : 'Não há oficina informada para este projeto.';

        return [
            'sections' => [
                'overview' => ['title' => 'Visão geral', 'type' => 'fact', 'content' => "Veículo: {$identity}.\nObjetivo e resumo informado: {$project->description}"],
                'diagnosis' => ['title' => 'Diagnóstico inicial', 'type' => 'fact', 'content' => 'Condição anterior e motivo da intervenção: '.$this->knownOrMissing($project->description, 'não foram detalhados além do relato do projeto.')],
                'parts' => ['title' => 'Peças utilizadas', 'type' => 'fact', 'content' => $this->knownOrMissing($project->parts, 'Nenhuma peça, marca, código, especificação ou compatibilidade foi informada.')],
                'tools' => ['title' => 'Ferramentas e equipamentos', 'type' => 'guidance', 'content' => 'Não há ferramentas registradas. Confirme ferramentas, torques e procedimentos na documentação técnica aplicável antes de executar qualquer intervenção.'],
                'planning' => ['title' => 'Planejamento', 'type' => 'guidance', 'content' => "Etapa atual: {$project->status}. Não há estimativa automática de prazo ou orçamento. Verifique pré-requisitos, peças corretas e disponibilidade de profissional qualificado."],
                'execution' => ['title' => 'Execução', 'type' => 'fact', 'content' => $this->knownOrMissing($project->services, "Não há etapas executadas registradas. {$workshop}")],
                'care' => ['title' => 'Cuidados técnicos', 'type' => 'guidance', 'content' => $this->safetyGuidance($project->category)],
                'costs' => ['title' => 'Custos', 'type' => 'fact', 'content' => $cost],
                'results' => ['title' => 'Resultados', 'type' => 'fact', 'content' => 'Status informado: '.config('motorclub.project_status.'.$project->status).'. Fotos registradas: '.$project->photos->count().'. Resultados e limitações devem ser confirmados pelo proprietário.'],
                'recommendations' => ['title' => 'Recomendações', 'type' => 'guidance', 'content' => 'Revise os itens ausentes, guarde notas e comprovantes e confirme compatibilidade por veículo, versão e aplicação. Este relatório não substitui avaliação técnica.'],
                'references' => ['title' => 'Referências técnicas', 'type' => 'guidance', 'content' => 'Nenhuma fonte externa foi pesquisada automaticamente. Inclua manual do fabricante, documentação de peças ou laudo técnico somente após verificação.'],
            ],
            'missing_information' => $missing,
        ];
    }

    private function knownOrMissing(?string $value, string $fallback): string
    {
        return filled($value) ? $value : $fallback;
    }

    private function safetyGuidance(string $category): string
    {
        if (in_array($category, ['maintenance', 'performance'], true)) {
            return 'Antes de qualquer serviço, confirme procedimento, torque, compatibilidade e condição do veículo. Intervenções em freios, suspensão, direção ou sistemas elétricos exigem avaliação de profissional qualificado e fontes técnicas confiáveis.';
        }

        return 'Planeje o serviço com documentação confiável, use equipamentos adequados e interrompa a execução diante de qualquer dúvida sobre segurança ou compatibilidade.';
    }
}
