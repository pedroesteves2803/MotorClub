@props(['title' => 'Autoverse', 'noindex' => false])
<!DOCTYPE html>
<html lang="pt-BR">
<head>@include('partials.head', ['title' => $title])@if($noindex)<meta name="robots" content="noindex,nofollow">@endif</head>
<body class="mc-site antialiased @if(request()->routeIs('billing.*', 'shots.galleries.show')) mc-site--action-heavy @endif" data-autoverse-app data-mc-page="{{ request()->route()?->getName() ?? 'page' }}">
<a href="#conteudo" class="sr-only focus:not-sr-only">Pular para o conteúdo</a>
<div class="mc-scroll-progress" aria-hidden="true"></div>
<x-theme-toggle />
<x-site.header />
<main id="conteudo" class="mc-page-shell mx-auto max-w-7xl space-y-12 px-6 py-10 lg:px-10" data-mc-content>@if(session('status'))<div role="status" class="mc-notice rounded-xl p-5">{{ session('status') }}</div>@endif @if($errors->any())<div role="alert" class="mc-notice rounded-xl p-5"><p class="font-bold">Confira os campos:</p><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif {{ $slot }}</main>
<x-site.footer />
@fluxScripts
</body></html>
