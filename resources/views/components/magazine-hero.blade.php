@props([
    'vehicle',
    'magazine',
    'url',
    'vehicleUrl' => null,
    'qrUrl' => null,
    'owner' => false,
])

@php
    $cover = collect($magazine['pages'] ?? [])->firstWhere('type', 'cover');
    $coverPhoto = $cover['photos'][0] ?? null;
@endphp

<section class="av-magazine__hero" aria-labelledby="magazine-hero-title">
    <div class="av-magazine__hero-cover">
        @if(!empty($coverPhoto['url']))
            <img src="{{ $coverPhoto['url'] }}" alt="Capa de {{ $magazine['title'] }}" loading="eager">
        @else
            <div class="av-magazine__hero-cover-empty"><span aria-hidden="true">AV</span><small>Sem foto de capa</small></div>
        @endif
    </div>
    <div class="av-magazine__hero-copy">
        <p class="av-magazine-kicker">Revista digital · {{ $magazine['pageCount'] }} páginas</p>
        <h2 id="magazine-hero-title">{{ $magazine['title'] }}</h2>
        <p class="av-magazine__hero-vehicle">{{ $magazine['vehicleName'] }}</p>
        <p class="av-magazine__hero-description">{{ $magazine['subtitle'] ?: 'Uma leitura visual do projeto, das escolhas e da evolução deste veículo.' }}</p>
        <div class="av-magazine__hero-actions">
            <a class="mc-link-button" href="#revista">Explorar revista <span aria-hidden="true">↓</span></a>
            <button type="button" class="mc-text-link" data-copy-link="{{ $url }}">Copiar link</button>
            @if($vehicleUrl)<a class="mc-text-link" href="{{ $vehicleUrl }}">Página do veículo</a>@endif
            @if($qrUrl)<a class="mc-text-link" href="{{ $qrUrl }}">QR Code</a>@endif
            @if($owner)<a class="mc-text-link" href="{{ route('garage.magazine.edit', $vehicle) }}">Personalizar</a>@endif
        </div>
    </div>
</section>
