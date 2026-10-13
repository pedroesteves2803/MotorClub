@props([
    'title' => config('seo.site_name', 'Autoverse'),
    'description' => config('seo.default_description'),
    'canonical' => null,
    'robots' => 'index,follow',
    'ogType' => 'website',
    'image' => config('seo.default_image'),
    'jsonLd' => null,
])
@php
    $canonical = $canonical ?: (str_starts_with($robots, 'index') ? url()->current() : null);
    $image = $image ?: null;
    $jsonLdItems = $jsonLd ? \Illuminate\Support\Arr::wrap($jsonLd) : [];
@endphp
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="{{ $description }}" />
<meta name="robots" content="{{ $robots }}" />

<title>{{ $title }}</title>
@if($canonical)<link rel="canonical" href="{{ $canonical }}">@endif
<meta property="og:site_name" content="{{ config('seo.site_name', 'Autoverse') }}" />
<meta property="og:locale" content="pt_BR" />
<meta property="og:type" content="{{ $ogType }}" />
<meta property="og:title" content="{{ $title }}" />
<meta property="og:description" content="{{ $description }}" />
@if($canonical)<meta property="og:url" content="{{ $canonical }}" />@endif
@if($image)<meta property="og:image" content="{{ $image }}" />@endif
<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}" />
<meta name="twitter:title" content="{{ $title }}" />
<meta name="twitter:description" content="{{ $description }}" />
@if($image)<meta name="twitter:image" content="{{ $image }}" />@endif
@foreach($jsonLdItems as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endforeach
<link rel="icon" type="image/svg+xml" href="{{ asset('autoverse-mark.svg') }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600|space-grotesk:400,500,600,700" rel="stylesheet" />

@vite(['resources/js/app.js'])
@fluxAppearance
