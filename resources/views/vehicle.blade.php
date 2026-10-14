@php
    $vehiclePublicUrl = route('vehicles.slug.show', ['slug' => $vehicle->slug]);
    $vehicleName = $vehicle->public_name ?: $vehicle->brand.' '.$vehicle->model;
    $publicProject = $vehicle->projects()->where('is_public', true)->latest('id')->first();
    $publicMagazine = $vehicle->magazineSetting;
    $hasPublishedMagazine = $publicProject && $publicMagazine?->is_published;
    $magazineCover = $publicMagazine?->coverPhoto ?: $vehicle->photos->first();
    $projectPublicUrl = $publicProject && $publicMagazine?->is_published !== false ? route('projects.show', $publicProject) : $vehiclePublicUrl;
    $vehicleFacts = collect([$vehicle->brand.' '.$vehicle->model, $vehicle->trim, $vehicle->model_year])->filter()->implode(' · ');
    $vehicleDescription = collect(['Conheça '.$vehicleName, $vehicleFacts, 'e acompanhe suas fotos, modificações e evolução no Autoverse.'])->filter()->implode(' — ');
    $vehicleBreadcrumb = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [['@type' => 'ListItem', 'position' => 1, 'name' => 'Explorar', 'item' => route('explore')], ['@type' => 'ListItem', 'position' => 2, 'name' => $vehicleName, 'item' => $vehiclePublicUrl]]];
    $vehicleStructuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'Vehicle',
        'name' => $vehicleName,
        'url' => $vehiclePublicUrl,
        'brand' => ['@type' => 'Brand', 'name' => $vehicle->brand],
        'model' => $vehicle->model,
        'modelDate' => (string) $vehicle->model_year,
    ];
    if ($vehicle->color) {
        $vehicleStructuredData['color'] = $vehicle->color;
    }
    if ($vehicle->photos->first()) {
        $vehicleStructuredData['image'] = route('vehicle-photos.show', $vehicle->photos->first());
    }
@endphp
<x-layouts.public :title="$vehicleName.' — '.$vehicleFacts.' | Autoverse'" :description="$vehicleDescription" :noindex="(bool) $vehicle->user->is_demo" :canonical="$vehiclePublicUrl" :jsonLd="[$vehicleStructuredData, $vehicleBreadcrumb]" :image="$vehicle->photos->first() ? route('vehicle-photos.show', $vehicle->photos->first()) : null">
    <a href="{{ route('community') }}" class="mc-text-link">← Conhecer a comunidade</a>
    <header class="mc-intro"><p class="mc-edition mb-5">{{ $vehicle->brand }} / {{ $vehicle->model_year }}</p><h1>{{ $vehicleName }}@if($vehicle->trim)<br><span>{{ $vehicle->trim }}</span>@endif</h1><p class="mt-6 text-xl">{{ collect([$vehicle->brand.' '.$vehicle->model, $vehicle->engine, $vehicle->model_year])->filter()->implode(' · ') }}</p>@if($vehicle->color || $vehicle->mileage)<p class="mc-muted">{{ $vehicle->color }}{{ $vehicle->mileage ? ' · '.number_format($vehicle->mileage, 0, ',', '.').' km'.($vehicle->mileage_approximate ? ' (aprox.)' : '') : '' }}</p>@endif @if($vehicle->instagram_url)<a class="mc-text-link" href="{{ $vehicle->instagram_url }}" target="_blank" rel="noopener noreferrer">Instagram do carro ↗</a>@endif</header>
    @if($vehicle->user->profile_public)<p>Na garagem de <a class="mc-text-link" href="{{ route('profiles.show',$vehicle->user) }}">{{ $vehicle->user->name }}</a></p>@endif
    @if($vehicle->user->is_demo)<p class="mc-notice border-l-2 border-[var(--mc-action)] p-4">Veículo fictício de demonstração.</p>@endif
    @if($hasPublishedMagazine)
        <section class="av-vehicle-magazine" aria-labelledby="vehicle-magazine-title">
            <div class="av-vehicle-magazine__cover">
                @if($magazineCover)<img src="{{ route('vehicle-photos.show', $magazineCover) }}" alt="Capa da revista de {{ $vehicleName }}" loading="lazy">@else<span aria-hidden="true"><svg viewBox="0 0 24 24" class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 4.75A2.25 2.25 0 0 1 7.25 2.5h9.5A2.25 2.25 0 0 1 19 4.75v14.5a2.25 2.25 0 0 1-2.25 2.25h-9.5A2.25 2.25 0 0 1 5 19.25z"/><path d="M5 5.5h11.75A2.25 2.25 0 0 1 19 7.75"/></svg></span>@endif
            </div>
            <div class="av-vehicle-magazine__content"><p class="av-magazine-kicker">Revista digital</p><h2 id="vehicle-magazine-title">{{ $publicMagazine->title ?: 'A história de '.$vehicleName }}</h2><p>{{ $publicMagazine->subtitle ?: 'Fotos, evolução e escolhas deste projeto em uma leitura.' }}</p><div class="av-vehicle-magazine__actions"><a class="mc-link-button" href="{{ route('vehicles.slug.magazine', ['slug' => $vehicle->slug]) }}">Explorar revista <span aria-hidden="true">↗</span></a><button type="button" class="mc-text-link" data-copy-link="{{ $projectPublicUrl }}">Copiar link do projeto</button></div></div>
        </section>
    @endif
    <nav class="flex flex-wrap gap-4" aria-label="Explorar veículo">
        @if($publicProject)<a class="mc-text-link" href="{{ route('vehicles.slug.evolution', ['slug' => $vehicle->slug]) }}">Ver evolução →</a>@endif
        <a class="mc-text-link" href="{{ route('vehicles.slug.qr', ['slug' => $vehicle->slug]) }}">Abrir QR Code do projeto ↗</a>
        <button type="button" class="mc-text-link" data-copy-link="{{ $projectPublicUrl }}">Copiar link do projeto</button>
    </nav>
    <section class="mc-media-grid grid gap-6 md:grid-cols-2" aria-label="Fotos do veículo">
        @forelse($vehicle->photos as $photo)
            <figure class="{{ $loop->first ? 'md:col-span-2' : '' }}"><img src="{{ route('vehicle-photos.show', $photo) }}" alt="Foto de {{ $vehicleName }}" class="max-h-[650px] w-full object-cover" /></figure>
        @empty
            <div class="mc-home-poster min-h-72! md:col-span-2"><span class="mc-edition">{{ $vehicle->brand }}</span><p>{{ $vehicleName }}@if($vehicle->trim)<em> / {{ $vehicle->trim }}</em>@endif</p><span class="mc-edition">Este veículo ainda não tem fotos</span></div>
        @endforelse
    </section>
    @if($vehicle->user->is_demo && ($details = config('verified_cars.'.strtolower($vehicle->model).($vehicle->trim === 'GTI' ? '-gti' : ''))))
        <x-verified-car :details="$details" />
    @else
        <p class="max-w-2xl text-sm">Informações declaradas pelo proprietário. Não representam garantia de compatibilidade técnica.</p>
    @endif
    @if($vehicle->entries->isNotEmpty())
        <section class="mt-12 space-y-6" aria-labelledby="public-history-title">
            <h2 id="public-history-title" class="text-3xl font-bold">Histórico público</h2>
            <div class="space-y-5">
                @foreach($vehicle->entries as $entry)
                    <article class="mc-surface space-y-3 rounded-2xl p-6">
                        <p class="mc-edition">{{ $entry->occurred_on?->format('d/m/Y') ?? 'Data não informada' }} · {{ $entry->category === 'maintenance' ? 'Manutenção' : 'Modificação' }}</p>
                        <h3 class="text-xl font-semibold">{{ $entry->title }}</h3>
                        <p class="whitespace-pre-line">{{ $entry->description }}</p>
                        @if($entry->parts)<p class="whitespace-pre-line">Peças: {{ $entry->parts }}</p>@endif
                        @if($entry->services)<p class="whitespace-pre-line">Serviços: {{ $entry->services }}</p>@endif
                        @if($entry->cost !== null)<p>Valor: R$ {{ number_format((float) $entry->cost, 2, ',', '.') }}</p>@endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif
    <section class="space-y-6"><h2 class="text-3xl font-bold">Projetos deste carro</h2><x-project-cards :projects="\App\Models\Project::published()->where('vehicle_id',$vehicle->id)->with(['previewPhoto','vehicle.previewPhoto'])->get()" /></section>
</x-layouts.public>
