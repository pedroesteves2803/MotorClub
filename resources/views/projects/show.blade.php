<x-layouts.public :title="$project->title.' · Autoverse Magazine'" :noindex="!$project->publiclyVisible()">
    <div class="av-magazine" data-av-magazine data-av-page-count="{{ $magazine['pageCount'] }}" data-av-default-mode="magazine">
        <header class="av-magazine__intro">
            <div>
                <p class="av-magazine-kicker">{{ $magazine['category'] }} · {{ $magazine['status'] }}</p>
                <h1>{{ $magazine['title'] }}</h1>
                <p>{{ $magazine['vehicleName'] }} · Edição editorial do Autoverse</p>
            </div>
            <div class="av-magazine__actions">
                <div class="av-magazine__mode" role="group" aria-label="Modo de visualização">
                    <button type="button" class="is-active" data-av-mode="magazine" aria-pressed="true">Revista</button>
                    <button type="button" data-av-mode="reading" aria-pressed="false">Leitura</button>
                </div>
                @can('update', $project)
                    <a class="mc-text-link" href="{{ route('projects.edit', $project) }}">Editar projeto</a>
                    @if(Route::has('projects.reports.generate'))<form method="POST" action="{{ route('projects.reports.generate', $project) }}">@csrf<button class="mc-link-button" type="submit">Gerar relatório</button></form>@endif
                @endcan
            </div>
        </header>

        @can('update', $project)
            @if(!$project->publiclyVisible())<p class="mc-notice rounded-xl p-4" role="status">Este projeto está privado. A edição continua disponível apenas para você.</p>@endif
        @endcan
        @if($project->vehicle->user->is_demo)<p class="mc-notice rounded-xl p-4">Projeto fictício de demonstração. Informações e custos simulados.</p>@endif

        <section class="av-magazine__experience" data-av-magazine-view aria-labelledby="magazine-title">
            <div class="av-magazine__toolbar">
                <div><p class="av-magazine-kicker">Autoverse Magazine · {{ $magazine['pageCount'] }} páginas</p><p class="av-magazine__hint">Use as setas, clique nas bordas ou pressione ← e →.</p></div>
                <div class="av-magazine__progress" aria-live="polite"><span data-av-progress-label>Página 01 de {{ str_pad((string) $magazine['pageCount'], 2, '0', STR_PAD_LEFT) }}</span><span class="av-magazine__progress-line"><i data-av-progress-bar></i></span></div>
            </div>
            <div class="av-magazine__book" data-av-book tabindex="0" aria-label="Revista digital do projeto">
                <button type="button" class="av-magazine__edge av-magazine__edge--prev" data-av-prev aria-label="Página anterior">‹</button>
                <div class="av-magazine__sheet" data-av-sheet>
                    @foreach($magazine['pages'] as $index => $page)
                        <div class="av-magazine__slot" data-av-page="{{ $index }}" aria-hidden="{{ $index === 0 ? 'false' : 'true' }}" @if($index > 1) hidden @endif>
                            @include('projects.partials.magazine-page', ['page' => $page])
                        </div>
                    @endforeach
                </div>
                <button type="button" class="av-magazine__edge av-magazine__edge--next" data-av-next aria-label="Próxima página">›</button>
            </div>
            <p class="av-magazine__sr-status sr-only" data-av-page-status aria-live="polite">Capa</p>
        </section>

        <section class="av-magazine__reading" data-av-reading-view aria-labelledby="reading-title" hidden>
            <div class="av-magazine-reading__header"><p class="av-magazine-kicker">Modo leitura · Conteúdo completo</p><h2 id="reading-title">{{ $magazine['title'] }}</h2><p class="av-magazine-lede">A mesma documentação da revista em uma leitura vertical, sem efeitos de movimento.</p></div>
            @foreach($magazine['pages'] as $page)
                <div class="av-magazine-reading__page">@include('projects.partials.magazine-page', ['page' => $page])</div>
            @endforeach
            @if($reports->isNotEmpty())
                <section class="av-magazine-reading__reports" aria-labelledby="reports-title"><p class="av-magazine-kicker">Documentação complementar</p><h2 id="reports-title">Relatórios do projeto</h2>@foreach($reports as $report)<article class="mc-surface rounded-2xl p-5"><p>Relatório v{{ $report->version }} · {{ $report->status === 'approved' ? 'Aprovado' : 'Em revisão' }}</p><a class="mc-text-link mt-4" href="{{ route('projects.reports.show',[$project,$report]) }}">Ler relatório →</a></article>@endforeach</section>
            @endif
        </section>
    </div>
</x-layouts.public>
