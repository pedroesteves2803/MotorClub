<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? 'Autoverse' }}</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('autoverse-mark.svg') }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600|space-grotesk:400,500,600,700" rel="stylesheet" />

@vite(['resources/js/app.js'])
@fluxAppearance
