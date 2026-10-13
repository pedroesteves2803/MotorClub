<!DOCTYPE html>
<html lang="pt-BR">
<head>@include('partials.head', ['title' => $title ?? 'Minha garagem — Autoverse'])</head>
<body class="mc-site min-h-screen bg-stone-100 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
    <a href="#conteudo" class="sr-only focus:not-sr-only">Pular para o conteúdo</a>
    <x-site.header />
    <main id="conteudo" class="mx-auto max-w-7xl px-6 py-10 md:py-16 lg:px-10">{{ $slot }}</main>
    <x-site.footer />
    @fluxScripts
</body>
</html>
