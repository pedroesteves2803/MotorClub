@php($vehicleName = $vehicle->public_name ?: $vehicle->brand.' '.$vehicle->model)
<x-layouts.public :title="$vehicleName.' — MotorClub'" :noindex="(bool) $vehicle->user->is_demo">
    <a href="{{ route('community') }}" class="mc-text-link">← Conhecer a comunidade</a>
    <header class="mc-intro"><p class="mc-edition mb-5">{{ $vehicle->brand }} / {{ $vehicle->model_year }}</p><h1>{{ $vehicleName }}<br><span>{{ $vehicle->trim }}</span></h1><p class="mt-6 text-xl">{{ $vehicle->brand }} {{ $vehicle->model }} · {{ $vehicle->engine }} · {{ $vehicle->model_year }}</p>@if($vehicle->color || $vehicle->mileage || $vehicle->location)<p class="mc-muted">{{ $vehicle->color }}{{ $vehicle->mileage ? ' · '.number_format($vehicle->mileage, 0, ',', '.').' km'.($vehicle->mileage_approximate ? ' (aprox.)' : '') : '' }}{{ $vehicle->location ? ' · '.$vehicle->location : '' }}</p>@endif @if($vehicle->instagram_url)<a class="mc-text-link" href="{{ $vehicle->instagram_url }}" target="_blank" rel="noopener noreferrer">Instagram do carro ↗</a>@endif</header>
    @if($vehicle->user->profile_public)<p>Na garagem de <a class="mc-text-link" href="{{ route('profiles.show',$vehicle->user) }}">{{ $vehicle->user->name }}</a></p>@endif
    @if($vehicle->user->is_demo)<p class="mc-notice border-l-2 border-[var(--mc-action)] p-4">Veículo fictício de demonstração.</p>@endif
    <section class="mc-media-grid grid gap-6 md:grid-cols-2" aria-label="Fotos do veículo">
        @forelse($vehicle->photos as $photo)
            <figure class="{{ $loop->first ? 'md:col-span-2' : '' }}"><img src="{{ route('vehicle-photos.show', $photo) }}" alt="Foto de {{ $vehicleName }}" class="max-h-[650px] w-full object-cover" /></figure>
        @empty
            <div class="mc-home-poster min-h-72! md:col-span-2"><span class="mc-edition">{{ $vehicle->brand }}</span><p>{{ $vehicleName }}<em> / {{ $vehicle->trim }}</em></p><span class="mc-edition">Este veículo ainda não tem fotos</span></div>
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
