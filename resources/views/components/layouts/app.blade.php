<!DOCTYPE html>
<html lang="pt-BR">
    <head>@include('partials.head', ['title' => $title ?? 'Minha garagem — Autoverse', 'robots' => 'noindex,nofollow', 'canonical' => null])</head>
<body class="mc-site min-h-screen bg-stone-100 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100 @if(request()->routeIs('billing.*', 'shots.galleries.show')) mc-site--action-heavy @endif" data-autoverse-app data-mc-page="{{ request()->route()?->getName() ?? 'page' }}">
    <a href="#conteudo" class="sr-only focus:not-sr-only">Pular para o conteúdo</a>
    <div class="mc-scroll-progress" aria-hidden="true"></div>
    <x-theme-toggle />
    <x-site.header />
    <main id="conteudo" class="mc-page-shell mx-auto max-w-7xl px-6 py-10 md:py-16 lg:px-10" data-mc-content>{{ $slot }}</main>
    <x-site.footer />
    @fluxScripts
</body>
</html>
