<x-layouts.public :title="'Relatório: '.$project->title.' · MotorClub'" :noindex="!$report->publiclyVisible()">
    <section class="space-y-5">
        <p class="mc-edition">MotorClub AI · relatório demonstrativo v{{ $report->version }}</p>
        <h1 class="mc-title">{{ $project->title }}</h1>
        <p>Relatório revisado pelo proprietário. Dados registrados e orientações estão identificados em cada seção.</p>
        @foreach($report->sections as $section)
            <article class="mc-surface mc-form">
                <p class="mc-edition">{{ $section['type'] === 'fact' ? 'Dados do projeto' : 'Orientação geral' }}</p>
                <h2 class="text-2xl font-bold">{{ $section['title'] }}</h2>
                <p class="whitespace-pre-line">{{ $section['content'] }}</p>
            </article>
        @endforeach
        <p class="mc-muted">Este relatório é demonstrativo e não substitui manual técnico, diagnóstico ou profissional qualificado.</p>
    </section>
</x-layouts.public>
