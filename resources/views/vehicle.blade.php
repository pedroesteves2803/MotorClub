<x-layouts.public :title="$vehicle->brand.' '.$vehicle->model.' — MotorClub'" :noindex="(bool) $vehicle->user->is_demo">
    <a href="{{ route('community') }}" class="mc-text-link">← Conhecer a comunidade</a>
    <header class="mc-intro"><p class="mc-edition mb-5">{{ $vehicle->brand }} / {{ $vehicle->model_year }}</p><h1>{{ $vehicle->model }}<br><span>{{ $vehicle->trim }}</span></h1><p class="mt-6 text-xl">{{ $vehicle->engine }} · {{ $vehicle->model_year }}</p></header>
    @if($vehicle->user->profile_public)<p>Na garagem de <a class="mc-text-link" href="{{ route('profiles.show',$vehicle->user) }}">{{ $vehicle->user->name }}</a></p>@endif
    @if($vehicle->user->is_demo)<p class="mc-notice border-l-2 border-[var(--mc-action)] p-4">Veículo fictício de demonstração.</p>@endif
    <section class="grid gap-6 md:grid-cols-2" aria-label="Fotos do veículo">
        @forelse($vehicle->photos as $photo)
            <figure class="{{ $loop->first ? 'md:col-span-2' : '' }}"><img src="{{ route('vehicle-photos.show', $photo) }}" alt="Foto de {{ $vehicle->model }}" class="max-h-[650px] w-full object-cover" /></figure>
        @empty
            <div class="mc-home-poster min-h-72! md:col-span-2"><span class="mc-edition">{{ $vehicle->brand }}</span><p>{{ $vehicle->model }}<em> / {{ $vehicle->trim }}</em></p><span class="mc-edition">Este veículo ainda não tem fotos</span></div>
        @endforelse
    </section>
    @if($vehicle->user->is_demo && ($details = config('verified_cars.'.strtolower($vehicle->model).($vehicle->trim === 'GTI' ? '-gti' : ''))))
        <x-verified-car :details="$details" />
    @else
        <p class="max-w-2xl text-sm">Informações declaradas pelo proprietário. Não representam garantia de compatibilidade técnica.</p>
    @endif
<section class="space-y-6"><h2 class="text-3xl font-bold">Projetos deste carro</h2><x-project-cards :projects="\App\Models\Project::published()->where('vehicle_id',$vehicle->id)->with(['previewPhoto','vehicle.previewPhoto'])->get()" /></section></x-layouts.public>
