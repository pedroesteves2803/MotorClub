@php
    $vehicleName = $vehicle->public_name ?: trim($vehicle->brand.' '.$vehicle->model);
    $canonical = $public ? route('vehicles.slug.evolution', ['slug' => $vehicle->slug]) : route('garage.evolution', $vehicle);
@endphp
<x-layouts.public :title="$vehicleName.' · Evolução | Autoverse'" :description="'Evolução documentada de '.$vehicleName" :canonical="$canonical" :noindex="!$public">
    <header class="mc-page-intro space-y-5">
        <p class="mc-edition">{{ $public ? 'Evolução pública' : 'Arquivo privado' }} · {{ $vehicle->brand }} {{ $vehicle->model_year }}</p>
        <h1 class="mc-title">{{ $vehicleName }}<br><span>Evolução real, etapa por etapa.</span></h1>
        <p class="max-w-2xl text-lg mc-muted">Modificações e registros exibidos a partir dos dados cadastrados no projeto. Etapas planejadas permanecem identificadas como planejadas.</p>
        @if($public)<div class="flex flex-wrap gap-4"><a class="mc-text-link" href="{{ route('vehicles.slug.show', ['slug' => $vehicle->slug]) }}">← Página do veículo</a><a class="mc-text-link" href="{{ route('vehicles.slug.magazine', ['slug' => $vehicle->slug]) }}">Ler revista →</a></div>@else<a class="mc-text-link" href="{{ route('garage.details', $vehicle) }}">← Voltar para a garagem</a>@endif
    </header>
    @if(!$project)
        <div class="mc-surface rounded-2xl p-8"><h2 class="text-2xl font-semibold">A evolução começa com um projeto</h2><p class="mt-3 mc-muted">Cadastre o primeiro projeto e as etapas aparecerão aqui sem criar conteúdo duplicado.</p></div>
    @elseif(empty($stages))
        <div class="mc-surface rounded-2xl p-8"><h2 class="text-2xl font-semibold">Nenhuma etapa pública ainda</h2><p class="mt-3 mc-muted">Quando houver uma modificação ou registro público, ele aparecerá nesta linha do tempo.</p></div>
    @else
        <ol class="relative space-y-8 border-l border-[var(--mc-line)] pl-7 md:space-y-12 md:pl-10" aria-label="Linha do tempo da evolução">
            @foreach($stages as $stage)
                <li class="relative">
                    <span class="absolute -left-[2.2rem] top-1 flex h-5 w-5 items-center justify-center rounded-full border-4 border-[var(--mc-paper)] bg-[var(--mc-action)] md:-left-[2.8rem]" aria-hidden="true"></span>
                    <article class="mc-surface overflow-hidden rounded-2xl p-6 md:p-8">
                        <div class="flex flex-wrap items-start justify-between gap-4"><div><p class="mc-edition">{{ $stage['date'] ?: 'Data não informada' }} · {{ ucfirst($stage['kind']) }}</p><h2 class="mt-2 text-2xl font-semibold">{{ $stage['title'] }}</h2></div><span class="rounded-full border border-[var(--mc-line)] px-3 py-1 text-sm">{{ $stage['status'] }}</span></div>
                        @if($stage['description'])<p class="mt-5 whitespace-pre-line text-lg">{{ $stage['description'] }}</p>@endif
                        @if(is_array($stage['parts']) && count($stage['parts']))<div class="mt-6 grid gap-3 sm:grid-cols-2"><h3 class="sm:col-span-2 text-sm font-semibold uppercase tracking-[.18em] mc-muted">Peças relacionadas</h3>@foreach($stage['parts'] as $part)<div class="rounded-xl border border-[var(--mc-line)] p-4"><strong>{{ $part['name'] }}</strong><p class="mt-1 text-sm mc-muted">{{ $part['status'] }}{{ $part['brand'] ? ' · '.$part['brand'] : '' }}{{ $part['model'] ? ' · '.$part['model'] : '' }}</p></div>@endforeach</div>@elseif($stage['parts'])<p class="mt-4 text-sm mc-muted">{{ $stage['parts'] }}</p>@endif
                        @if(count($stage['photos']))<div class="mt-6 grid gap-4 sm:grid-cols-2">@foreach($stage['photos'] as $photo)<figure><img class="aspect-[4/3] w-full rounded-xl object-cover" loading="lazy" src="{{ $photo['url'] }}" alt="{{ $photo['alt'] }}"><figcaption class="mt-2 text-xs mc-muted">{{ $photo['kind'] ?: 'Registro visual' }}</figcaption></figure>@endforeach</div>@endif
                    </article>
                </li>
            @endforeach
        </ol>
    @endif
</x-layouts.public>
