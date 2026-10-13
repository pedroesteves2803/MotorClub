<x-layouts.public :title="'Relatório do projeto · Autoverse'">
    <section class="space-y-5">
        <p class="mc-edition">Autoverse AI · relatório demonstrativo v{{ $report->version }}</p>
        <h1 class="mc-title">Revise o relatório</h1>
        <p>Este texto foi montado por templates locais a partir do projeto. Revise, corrija e aprove antes de publicar.</p>
        @if($report->missing_information)
            <div class="mc-notice rounded-xl p-4"><strong>Informações ausentes:</strong> {{ implode(' · ', $report->missing_information) }}</div>
        @endif
    </section>
    <form class="mc-surface mc-form mt-6" method="POST" action="{{ route('projects.reports.update', [$project, $report]) }}">
        @csrf @method('PUT')
        @foreach($report->sections as $key => $section)
            <x-area :name="'sections.'.$key.'.content'" :label="$section['title'].($section['type'] === 'guidance' ? ' · orientação' : ' · dados do projeto')" :value="$section['content']" maxlength="5000" required />
        @endforeach
        <x-select-field name="status" label="Situação" :value="$report->status" :options="['draft' => 'Em revisão', 'approved' => 'Aprovado pelo proprietário']" />
        <x-check name="is_public" label="Disponibilizar este relatório na página pública do projeto quando estiver aprovado" :checked="$report->is_public" />
        <p class="mc-muted">Relatórios privados, projetos privados e custos sem autorização não ficam visíveis para terceiros.</p>
        <button class="mc-link-button">Salvar relatório</button>
    </form>
</x-layouts.public>
