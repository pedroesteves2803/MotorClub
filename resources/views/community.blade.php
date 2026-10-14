<x-layouts.public title="Explorar carros, projetos e fotógrafos | Autoverse" description="Conheça carros, projetos, fotógrafos e pessoas da comunidade Autoverse." :noindex="request()->routeIs('community')" :canonical="route('explore')">
    <header class="mc-intro">
        <p class="mc-edition mb-5">Pessoas diferentes. A mesma paixão.</p>
        <h1>Uma garagem.<br><span>Muitas histórias.</span></h1>
        <p class="mt-6 max-w-xl text-lg">Clássicos, esportivos e carros do dia a dia. Conheça as histórias e os projetos de quem compartilha a cultura automotiva na Autoverse.</p>
    </header>

    <form method="GET" class="mc-surface grid gap-3 rounded-2xl p-4 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-center" aria-label="Filtrar comunidade">
        <input class="mc-input" name="q" value="{{ request('q') }}" placeholder="Pessoa, marca ou modelo" aria-label="Buscar na comunidade">
        <label class="flex min-h-12 items-center gap-2 rounded-lg border border-[var(--mc-line)] px-3 text-sm"><input type="checkbox" name="magazine" value="1" @checked($magazineFilter ?? false)> <span>Com revista</span></label>
        <button class="mc-link-button min-h-12" type="submit">Buscar</button>
    </form>

    @if($magazineVehicles->isNotEmpty())
        <section class="av-community-magazines space-y-5" aria-labelledby="community-magazines-title">
            <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="mc-edition">Publicações da comunidade</p><h2 id="community-magazines-title" class="text-3xl font-bold">Histórias sobre quatro rodas</h2></div><a class="mc-text-link" href="{{ route('explore', ['magazine' => 1]) }}">Ver só revistas →</a></div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($magazineVehicles as $vehicle)
                    <a href="{{ $vehicle['magazine']['url'] }}" class="av-community-magazine-card group">
                        <div class="av-community-magazine-card__cover">@if($vehicle['magazine']['cover'])<img src="{{ $vehicle['magazine']['cover'] }}" alt="Capa da revista de {{ $vehicle['magazine']['vehicle'] }}" loading="lazy">@else<span aria-hidden="true">Revista</span>@endif</div>
                        <div class="av-community-magazine-card__body"><p class="av-magazine-kicker">Revista digital</p><h3>{{ $vehicle['magazine']['title'] }}</h3><p>{{ $vehicle['magazine']['vehicle'] }}</p><span>Explorar revista <span aria-hidden="true">↗</span></span></div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if($people->isEmpty())<p>Nenhum perfil encontrado.</p>@endif
    <section class="grid items-start gap-6 sm:grid-cols-2 lg:grid-cols-3" aria-label="Perfis da comunidade">
        @foreach($people as $person)
            <article class="overflow-hidden border border-zinc-300 mc-surface">
                <div class="flex items-center gap-3 p-5">@if(!empty($person['avatar']))<img src="{{ $person['avatar'] }}" alt="Foto pública de {{ $person['name'] }} no Instagram" class="h-12 w-12 shrink-0 rounded-full object-cover" />@else<span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#223d32] font-bold text-white">{{ mb_substr($person['name'],0,1) }}</span>@endif<div><h2 class="font-bold">@if(!empty($person['url']))<a href="{{ $person['url'] }}">{{ $person['name'] }}</a>@else{{ $person['name'] }}@endif</h2><p class="text-xs mc-muted">{{ $person['label'] }}</p></div></div>
                @foreach($person['vehicles'] as $vehicle)
                    <a href="{{ $vehicle['url'] }}">
                        @if($vehicle['image'])<img src="{{ $vehicle['image'] }}" alt="{{ !empty($vehicle['post']) ? 'Foto real de' : 'Imagem ilustrativa de' }} {{ $vehicle['name'] }}" loading="lazy" class="aspect-[3/2] w-full {{ !empty($vehicle['post']) ? 'object-contain bg-zinc-900' : 'object-cover' }}" />@else<div class="flex min-h-48 items-center bg-[#223d32] p-6 text-4xl font-bold italic text-[#f2f0e9]">{{ $vehicle['name'] }}</div>@endif
                    </a>
                    <div class="space-y-4 p-5"><h3 class="font-semibold">{{ $vehicle['name'] }}</h3><p class="text-sm mc-muted">{{ $vehicle['note'] }}</p>@if(!empty($vehicle['post']))<a href="{{ $vehicle['post'] }}" target="_blank" rel="noopener noreferrer" class="block text-xs underline">Foto: {{ $vehicle['credit'] }}</a>@endif@if(!empty($vehicle['magazine']))<a class="av-magazine-chip" href="{{ $vehicle['magazine']['url'] }}">@if($vehicle['magazine']['cover'])<img src="{{ $vehicle['magazine']['cover'] }}" alt="" loading="lazy">@endif<span><strong>Revista digital</strong><small>{{ $vehicle['magazine']['title'] }}</small></span><span aria-hidden="true">↗</span></a>@endif<a href="{{ $vehicle['url'] }}" class="mc-text-link w-fit">Conhecer o carro</a></div>
                @endforeach
            </article>
        @endforeach
    </section>
</x-layouts.public>
