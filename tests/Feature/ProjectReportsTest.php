<?php

use App\Models\Project;
use App\Models\ProjectReport;
use App\Models\User;
use App\Models\Vehicle;

function reportProject(array $data = [])
{
    $vehicle = Vehicle::factory()->create(['visibility' => 'public']);

    return Project::create($data + [
        'vehicle_id' => $vehicle->id,
        'title' => 'Revisão do Golf',
        'category' => 'maintenance',
        'status' => 'ongoing',
        'description' => 'Revisão completa para uso diário',
        'parts' => 'Filtro de óleo',
        'services' => 'Revisão',
        'cost' => '1234.56',
        'show_cost' => false,
        'is_public' => true,
        'workshop_status' => 'pending',
    ]);
}

it('gera relatório local versionado sem inventar dados ausentes', function () {
    $project = reportProject(['parts' => null, 'services' => null, 'cost' => '1234.56', 'show_cost' => false]);

    $this->actingAs($project->vehicle->user)
        ->post(route('projects.reports.generate', $project))
        ->assertRedirect();

    $report = ProjectReport::sole();
    expect($report->version)->toBe(1)
        ->and($report->status)->toBe('draft')
        ->and($report->is_public)->toBeFalse()
        ->and($report->generator)->toBe('fake')
        ->and($report->missing_information)->toContain('Peças, marcas, códigos e especificações')
        ->and($report->sections['parts']['content'])->toContain('Nenhuma peça')
        ->and($report->sections['costs']['content'])->toContain('Nenhum custo autorizado');

    $this->post(route('projects.reports.generate', $project));
    expect(ProjectReport::orderBy('version')->pluck('version')->all())->toBe([1, 2]);
});

it('permite revisar e publicar somente um relatório aprovado de projeto público', function () {
    $project = reportProject();
    $owner = $project->vehicle->user;
    $this->actingAs($owner)->post(route('projects.reports.generate', $project));
    $report = ProjectReport::sole();
    $sections = collect($report->sections)->map(fn ($section) => ['content' => $section['content']])->all();

    $this->put(route('projects.reports.update', [$project, $report]), [
        'sections' => $sections,
        'status' => 'approved',
        'is_public' => 1,
    ])->assertRedirect(route('projects.show', $project));

    auth()->logout();
    $this->get(route('projects.reports.show', [$project, $report]))->assertOk()->assertSee('Relatório revisado pelo proprietário');
    $this->get(route('projects.show', $project))->assertSee('Relatórios do projeto');

    $project->update(['is_public' => false]);
    $this->get(route('projects.reports.show', [$project, $report]))->assertNotFound();
});

it('protege relatórios contra acesso e edição de terceiros', function () {
    $project = reportProject();
    $this->actingAs($project->vehicle->user)->post(route('projects.reports.generate', $project));
    $report = ProjectReport::sole();
    $stranger = User::factory()->create();

    $this->actingAs($stranger)->get(route('projects.reports.edit', [$project, $report]))->assertForbidden();
    $this->put(route('projects.reports.update', [$project, $report]), [])->assertForbidden();
    auth()->logout();
    $this->get(route('projects.reports.show', [$project, $report]))->assertNotFound();
});
