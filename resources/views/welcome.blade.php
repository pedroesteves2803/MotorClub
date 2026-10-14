@php
    $homeStructuredData = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Autoverse',
            'url' => route('home'),
            'logo' => asset('autoverse-mark.svg'),
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Autoverse',
            'url' => route('home'),
            'inLanguage' => 'pt-BR',
        ],
    ];
@endphp
<x-layouts.public title="Autoverse — Garagens, projetos e fotografia automotiva" description="Crie sua garagem digital, registre cada modificação e compartilhe a evolução do seu carro com quem vive a mesma paixão." :canonical="route('home')" :jsonLd="$homeStructuredData">
    <section class="mc-home-hero mc-home-hero--focused" aria-labelledby="home-title">
        <div class="mc-home-copy">
            <h1 id="home-title">Todo carro tem uma história.<br><strong>Mostre a sua.</strong></h1>
            <p class="mt-7 text-lg leading-relaxed">Crie sua garagem digital, registre cada modificação e compartilhe a evolução do seu carro com quem vive a mesma paixão.</p>
            <div class="mt-8 flex flex-wrap gap-3"><a class="mc-link-button" href="{{ route('dashboard') }}">Criar minha garagem</a><a class="mc-text-link" href="{{ route('projects.index') }}">Explorar projetos</a></div>
        </div>

    </section>

    <section class="mc-home-features" aria-label="O que você pode fazer">
        <article><span class="mc-edition">Garagem</span><h2>Um registro que acompanha o carro.</h2><p>Cadastre seus carros, fotos e detalhes técnicos. Escolha o que fica só para você e o que merece uma página pública.</p></article>
        <article><span class="mc-edition">Projetos</span><h2>Cada mudança tem um capítulo.</h2><p>Organize modificações, peças e etapas para acompanhar a evolução do seu carro.</p><a class="mc-text-link mt-5" href="{{ route('projects.index') }}">Ver projetos</a></article>
        <article><span class="mc-edition">Shots</span><h2>Encontre fotos do seu carro.</h2><p>Explore ensaios e encontros registrados por fotógrafos da comunidade.</p><a class="mc-text-link mt-5" href="{{ route('shots.index') }}">Conhecer Shots</a></article>
    </section>

    <section class="border-t border-[var(--mc-line)] pt-8" aria-labelledby="community-title"><p class="mc-edition mb-3">Conheça a comunidade</p><h2 id="community-title" class="font-display text-4xl font-medium tracking-[-.06em] md:text-5xl">A garagem é só o começo.</h2><p class="mt-4 max-w-2xl text-lg mc-muted">Explore veículos, projetos, fotógrafos e galerias da comunidade.</p><div class="mt-7 flex flex-wrap gap-3"><a class="mc-text-link" href="{{ route('explore') }}">Explorar a comunidade</a></div></section>
</x-layouts.public>
