<x-layouts.public title="Autoverse — Seu carro. Sua história. Seu universo.">
    <section class="mc-home-hero">
        <div class="mc-home-copy">
            <p class="mc-edition">Autoverse · Garagens que contam histórias</p>
            <h1>Seu carro merece mais que <strong>uma publicação.</strong></h1>
            <p class="mt-7 max-w-xl text-lg leading-relaxed mc-muted">Crie sua garagem digital, registre a evolução dos seus projetos e descubra fotografias e pessoas que compartilham sua paixão por carros.</p>
            <div class="mt-8 flex flex-wrap gap-4"><a class="mc-link-button" href="{{ route('dashboard') }}">Criar minha garagem</a><a class="mc-text-link" href="{{ route('shots.index') }}">Explorar fotografias</a></div>
        </div>
        <aside class="mc-home-poster" aria-label="Manifesto Autoverse"><span class="mc-edition">Seu carro · Sua história · Seu universo</span><x-brand-mark /><p>Alguns veem<br>um carro.<br>A gente vê<br>um universo.</p><span class="mc-edition">Garagem · Projetos · Shots</span></aside>
    </section>
    <section class="mc-home-features" aria-label="O que você pode fazer">
        <article><span class="mc-edition">01 / Garagem</span><h2>Seu carro, em todos os detalhes.</h2><p>Cadastre veículos, organize fotos e defina o que pertence à sua garagem ou ao mundo.</p></article>
        <article><span class="mc-edition">02 / Projetos</span><h2>A evolução merece registro.</h2><p>Conecte peças, etapas e memórias ao veículo que torna cada projeto pessoal.</p></article>
        <article><span class="mc-edition">03 / Shots</span><h2>O olhar de quem entende.</h2><p>Descubra fotógrafos e galerias automotivas para guardar os melhores momentos.</p><a class="mc-text-link mt-4" href="{{ route('shots.index') }}">Conhecer Shots</a></article>
    </section>
    <section class="border-t border-[var(--mc-line)] pt-8"><p class="mc-edition mb-3">Conheça a comunidade</p><h2 class="text-2xl font-bold">A garagem é só o começo.</h2><p class="mt-3 max-w-3xl mc-muted">Explore veículos, projetos, fotógrafos e galerias da comunidade Autoverse.</p><div class="mt-6 flex flex-wrap gap-6"><a class="mc-text-link" href="{{ route('explore') }}">Explorar a comunidade</a></div></section>
</x-layouts.public>
