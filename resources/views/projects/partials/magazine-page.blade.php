@php($pageNumber = $page['number'] ? str_pad((string) $page['number'], 2, '0', STR_PAD_LEFT) : null)
<article class="av-magazine-page av-magazine-page--{{ $page['type'] }} av-magazine-page--{{ $page['layout'] ?? $page['type'] }}" data-av-page-content>
    <div class="av-magazine-page__inner">
        @if($page['type'] === 'cover')
            <div class="av-magazine-cover__image" @if(!empty($page['photos'][0]['url'])) style="--av-cover-image: url('{{ $page['photos'][0]['url'] }}')" @endif>
                @if(empty($page['photos']))<span class="av-magazine-cover__empty">Adicione fotos do carro para abrir esta edição.</span>@endif
                <div class="av-magazine-cover__veil"></div>
            </div>
            <div class="av-magazine-cover__copy">
                <p class="av-magazine-kicker">{{ $page['kicker'] }}</p>
                <p class="av-magazine-cover__edition">Edição {{ now()->format('Y') }}</p>
                <h2>{{ $page['title'] }}</h2>
                <p class="av-magazine-cover__vehicle">{{ $page['subtitle'] }}</p>
                <p class="av-magazine-cover__description">{{ $page['description'] }}</p>
                <div class="av-magazine-cover__meta">
                    @foreach($page['meta'] as $meta)<span>{{ $meta }}</span>@endforeach
                </div>
            </div>
        @elseif($page['type'] === 'contents')
            <header class="av-magazine-page__header">
                <p class="av-magazine-kicker">{{ $page['kicker'] }}</p>
                <h2>{{ $page['title'] }}</h2>
                <p>{{ $page['subtitle'] }}</p>
            </header>
            <ol class="av-magazine-toc" aria-label="Capítulos da edição">
                @forelse($page['chapters'] as $chapter)
                    <li><button type="button" data-av-toc="{{ $chapter['page'] }}"><span>{{ str_pad((string) $chapter['number'], 2, '0', STR_PAD_LEFT) }}</span><strong>{{ $chapter['title'] }}</strong><small>{{ $chapter['layout'] === 'timeline' ? 'Linha do tempo' : 'Capítulo editorial' }}</small></button></li>
                @empty
                    <li class="av-magazine-toc__empty">Esta edição ainda está recebendo seus primeiros capítulos.</li>
                @endforelse
            </ol>
            <p class="av-magazine-page__note">Os capítulos são montados a partir do conteúdo cadastrado na garagem.</p>
        @else
            <header class="av-magazine-page__header">
                <div><p class="av-magazine-kicker">Capítulo {{ $pageNumber }} · {{ $page['kicker'] }}</p><h2>{{ $page['title'] }}</h2></div>
                @if(!empty($page['meta']['status']))<span class="av-magazine-status">{{ $page['meta']['status'] }}</span>@endif
            </header>
            @if($page['type'] === 'chapter' && $page['layout'] === 'timeline')
                <div class="av-magazine-timeline">
                    @foreach($page['meta']['entries'] ?? [] as $entry)
                        <article class="av-magazine-timeline__item">
                            <div class="av-magazine-timeline__date"><span>{{ $entry['date'] }}</span>@if($entry['visibility'])<small>{{ $entry['visibility'] }}</small>@endif</div>
                            <div><p class="av-magazine-kicker">{{ $entry['category'] }}</p><h3>{{ $entry['title'] }}</h3><p>{{ $entry['description'] }}</p>@if($entry['parts'])<p><strong>Peças:</strong> {{ $entry['parts'] }}</p>@endif @if($entry['services'])<p><strong>Serviços:</strong> {{ $entry['services'] }}</p>@endif</div>
                        </article>
                    @endforeach
                </div>
            @elseif($page['layout'] === 'technical' || $page['layout'] === 'photo-text')
                <div class="av-magazine-split">
                    <div class="av-magazine-split__copy">@if($page['description'])<p class="av-magazine-lede">{{ $page['description'] }}</p>@endif @if(!empty($page['meta']['planned']))<blockquote>{{ $page['meta']['planned'] }}</blockquote>@endif @if(!empty($page['meta']['parts']))<div class="av-magazine-parts"><p class="av-magazine-kicker">Peças relacionadas</p>@foreach($page['meta']['parts'] as $part)<div><strong>{{ $part['name'] }}</strong><span>{{ $part['status'] }}@if($part['brand']) · {{ $part['brand'] }}@endif @if($part['model']) · {{ $part['model'] }}@endif</span></div>@endforeach</div>@endif</div>
                    @if(!empty($page['photos']))<div class="av-magazine-split__photos">@foreach($page['photos'] as $photo)<figure><img loading="lazy" src="{{ $photo['url'] }}" alt="{{ $photo['alt'] }}"><figcaption>{{ match($photo['source']) { 'carro' => 'Arquivo do carro', 'modificação' => 'Arquivo da modificação', 'peça' => 'Arquivo da peça', 'shots' => 'Fotografia licenciada · '.$photo['kind'], default => 'Arquivo do projeto' } }}</figcaption></figure>@endforeach</div>@endif
                </div>
            @elseif($page['layout'] === 'spec-sheet')
                <div class="av-magazine-specs">@foreach($page['meta']['costs'] ?? [] as $cost)<div><span>{{ $cost['label'] }}</span><strong>R$ {{ number_format($cost['value'], 2, ',', '.') }}</strong></div>@endforeach</div>
                @if($page['meta']['total'] !== null)<p class="av-magazine-total">Total registrado <strong>R$ {{ number_format($page['meta']['total'], 2, ',', '.') }}</strong></p>@endif
            @elseif($page['layout'] === 'quote')
                <blockquote class="av-magazine-quote">{{ $page['description'] }}</blockquote><p class="av-magazine-quote__body">{{ $page['meta']['planned'] ?? '' }}</p>
            @elseif($page['layout'] === 'gallery')
                <div class="av-magazine-gallery">@foreach($page['photos'] as $photo)<figure><img loading="lazy" src="{{ $photo['url'] }}" alt="{{ $photo['alt'] }}"><figcaption>{{ match($photo['source']) { 'carro' => 'Arquivo do carro', 'modificação' => 'Arquivo da modificação', 'peça' => 'Arquivo da peça', 'shots' => 'Fotografia licenciada · '.$photo['kind'], default => 'Arquivo do projeto' } }}</figcaption></figure>@endforeach</div>
            @endif
        @endif
        @if($pageNumber)<span class="av-magazine-page__number" aria-hidden="true">{{ $pageNumber }}</span>@endif
    </div>
</article>
