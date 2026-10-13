<x-layouts.public title="Autoverse — Seu carro. Sua história. Seu universo.">
    <section class="mc-home-hero" aria-labelledby="home-title">
        <div class="mc-home-copy">
            <p class="mc-edition">Autoverse · Garagens que contam histórias</p>
            <h1 id="home-title">O carro é seu.<br><strong>O universo também.</strong></h1>
            <p class="mt-7 text-lg leading-relaxed">Seu carro merece mais que uma publicação. Registre detalhes, mudanças, encontros e os olhares que fazem cada projeto seguir em frente.</p>
            <div class="mt-8 flex flex-wrap gap-3"><a class="mc-link-button" href="{{ route('dashboard') }}">Criar minha garagem</a><a class="mc-text-link border-[#2d486e] text-[#edf3fb]" href="{{ route('shots.index') }}">Explorar Shots</a></div>
        </div>
        <aside class="mc-home-poster mc-home-showroom" aria-label="Showroom 3D Autoverse" data-hero-3d>
            <div class="mc-showroom" data-autoverse-showroom data-model-url="{{ asset('models/spectral_gt_rs_game_ready.glb') }}" role="group" aria-label="Spectral GT RS 3D interativo">
                <div class="mc-showroom__canvas" data-showroom-canvas></div>
                <div class="mc-showroom__fallback" data-showroom-fallback>
                    <span class="mc-edition">Autoverse · Showroom</span>
                    <x-brand-mark />
                    <p>Alguns veem<br>um carro.<br>A gente vê<br><em>um universo.</em></p>
                    <span class="mc-edition">Garagem · Projetos · Shots</span>
                </div>
                <div class="mc-showroom__hud" aria-live="polite">
                    <span class="mc-edition">Autoverse / 01</span>
                    <button class="mc-showroom__reset" type="button" data-showroom-reset>Recentrar vista</button>
                    <span class="mc-showroom__status" data-showroom-status>Arraste para explorar</span>
                </div>
            </div>
        </aside>
    </section>

    <section class="mc-home-features" aria-label="O que você pode fazer">
        <article><span class="mc-edition">Garagem</span><h2>Um registro que acompanha o carro.</h2><p>Cadastre veículos, fotos e detalhes técnicos. Escolha o que fica só para você e o que merece uma página pública.</p></article>
        <article><span class="mc-edition">Projetos</span><h2>Cada mudança tem um capítulo.</h2><p>Organize evolução, peças, etapas e memórias para continuar o trabalho de onde parou.</p></article>
        <article><span class="mc-edition">Shots</span><h2>O olhar de quem entende.</h2><p>Descubra fotógrafos e galerias autorais com prévias protegidas e originais entregues com segurança.</p><a class="mc-text-link mt-5" href="{{ route('shots.index') }}">Conhecer Shots</a></article>
    </section>

    <section class="border-t border-[var(--mc-line)] pt-8" aria-labelledby="community-title"><p class="mc-edition mb-3">Conheça a comunidade</p><h2 id="community-title" class="font-display text-4xl font-medium tracking-[-.06em] md:text-5xl">A garagem é só o começo.</h2><p class="mt-4 max-w-2xl text-lg mc-muted">Explore veículos, projetos, fotógrafos e galerias da comunidade Autoverse.</p><div class="mt-7 flex flex-wrap gap-3"><a class="mc-text-link" href="{{ route('explore') }}">Explorar a comunidade</a></div></section>
</x-layouts.public>
