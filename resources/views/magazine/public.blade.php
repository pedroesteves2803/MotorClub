@php
    $vehicleName = $vehicle->public_name ?: trim($vehicle->brand.' '.$vehicle->model);
    $url = route('vehicles.public.magazine', ['key' => $vehicle->public_key ?: $vehicle->slug]);
    $vehicleUrl = $vehicle->public_key
        ? route('vehicles.public.show', ['key' => $vehicle->public_key])
        : route('vehicles.slug.show', ['slug' => $vehicle->slug]);
    $qrUrl = $vehicle->slug ? route('vehicles.slug.qr', ['slug' => $vehicle->slug]) : null;
@endphp
<x-layouts.public :title="$vehicleName.' · Revista | Autoverse'" :description="'Revista digital de '.$vehicleName" :canonical="$url" :ogType="'article'">
    <div class="av-magazine av-magazine--palette-{{ $settings->palette ?: 'carbon' }} av-magazine--style-{{ $settings->editorial_style ?: 'editorial' }} @if($settings->theme === 'light') av-magazine--light @endif" style="--av-accent: {{ $settings->primary_color }}" data-av-magazine data-av-page-count="{{ $magazine['pageCount'] }}" data-av-default-mode="reading">
        <header class="av-magazine__intro">
            <div>
                <p class="av-magazine-kicker">Revista pública · {{ $magazine['category'] }}</p>
                <h1>{{ $magazine['title'] }}</h1>
                <p>{{ $magazine['vehicleName'] }} · Uma história em movimento</p>
            </div>
            <div class="av-magazine__actions">
                <div class="av-magazine__mode" role="group" aria-label="Modo de visualização">
                    <button type="button" data-av-mode="reading" aria-pressed="true">Leitura</button>
                    <button type="button" data-av-mode="magazine" aria-pressed="false">Revista</button>
                </div>
                <a class="mc-text-link" href="{{ $vehicleUrl }}">Página do veículo</a>
            </div>
        </header>

        <x-magazine-hero :vehicle="$vehicle" :magazine="$magazine" :url="$url" :vehicle-url="$vehicleUrl" :qr-url="$qrUrl" />

        <section id="revista" class="av-magazine__experience" data-av-magazine-view aria-label="Revista digital">
            <div class="av-magazine__toolbar">
                <div><p class="av-magazine-kicker">{{ $magazine['pageCount'] }} páginas · Conteúdo público</p><p class="av-magazine__hint">Leia verticalmente ou abra a revista para virar as páginas.</p></div>
                <div class="av-magazine__progress" aria-live="polite"><span data-av-progress-label>Página 01 de {{ str_pad((string) $magazine['pageCount'], 2, '0', STR_PAD_LEFT) }}</span><span class="av-magazine__progress-line"><i data-av-progress-bar></i></span></div>
            </div>
            <div class="av-magazine__book" data-av-book tabindex="0" aria-label="Revista digital de {{ $vehicleName }}">
                <button type="button" class="av-magazine__edge av-magazine__edge--prev" data-av-prev aria-label="Página anterior">‹</button>
                <div class="av-magazine__sheet" data-av-sheet>@foreach($magazine['pages'] as $index => $page)<div class="av-magazine__slot" data-av-page="{{ $index }}" aria-hidden="{{ $index === 0 ? 'false' : 'true' }}" @if($index > 1) hidden @endif>@include('projects.partials.magazine-page', ['page' => $page])</div>@endforeach</div>
                <button type="button" class="av-magazine__edge av-magazine__edge--next" data-av-next aria-label="Próxima página">›</button>
            </div>
            <p class="av-magazine__sr-status sr-only" data-av-page-status aria-live="polite">Capa</p>
        </section>

        <section class="av-magazine__reading" data-av-reading-view aria-labelledby="reading-title">
            <div class="av-magazine-reading__header"><p class="av-magazine-kicker">Modo leitura · Conteúdo completo</p><h2 id="reading-title">{{ $magazine['title'] }}</h2><p class="av-magazine-lede">A mesma história em uma leitura contínua, confortável no celular e no desktop.</p></div>
            @foreach($magazine['pages'] as $page)<div class="av-magazine-reading__page" data-av-reading-page>@include('projects.partials.magazine-page', ['page' => $page])</div>@endforeach
        </section>
    </div>
</x-layouts.public>
