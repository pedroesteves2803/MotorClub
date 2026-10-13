<x-layouts.public title="Autoverse — Seu carro. Sua história. Seu universo.">
    <section class="mc-home-hero" aria-labelledby="home-title">
        <div class="mc-home-copy">
            <p class="mc-edition">Autoverse · Garagens que contam histórias</p>
            <h1 id="home-title">O carro é seu.<br><strong>O universo também.</strong></h1>
            <p class="mt-7 text-lg leading-relaxed">Seu carro merece mais que uma publicação. Registre detalhes, mudanças, encontros e os olhares que fazem cada projeto seguir em frente.</p>
            <div class="mt-8 flex flex-wrap gap-3"><a class="mc-link-button" href="{{ route('dashboard') }}">Criar minha garagem</a><a class="mc-text-link" href="{{ route('shots.index') }}">Explorar Shots</a></div>
        </div>
        <aside class="mc-home-poster mc-home-showroom" aria-label="Assinatura luminosa automotiva" data-hero-animation>
            <div class="mc-showroom mc-light-showroom" data-light-showroom role="img" aria-label="Ilustração minimalista de um carro esportivo com faróis azuis">
                <div class="mc-light-showroom__scene" aria-hidden="true">
                    <span class="mc-light-showroom__beam mc-light-showroom__beam--left"></span>
                    <span class="mc-light-showroom__beam mc-light-showroom__beam--right"></span>
                    <svg class="mc-light-showroom__art" viewBox="0 0 640 360" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path class="mc-light-showroom__body" d="M63 238h34l35-56c15-24 43-42 74-49l107-20c23-4 48 1 67 15l59 46 78 13c31 5 55 27 65 58l6 21H63v-28Z" />
                        <path class="mc-light-showroom__glass" d="m174 177 38-28c9-7 20-12 32-14l77-14c19-3 38 1 53 12l44 34H174Z" />
                        <path class="mc-light-showroom__accent" d="M130 231h58m265 0h57M238 208h165" />
                        <path class="mc-light-showroom__lamp mc-light-showroom__lamp--left" pathLength="1" d="M93 208c22-11 46-17 70-18l25 13-29 26-67 2-8-12 9-11Z" />
                        <path class="mc-light-showroom__lamp mc-light-showroom__lamp--right" pathLength="1" d="m451 202 42-2c23 1 45 8 63 20l7 9-67 1-45-17v-11Z" />
                        <circle class="mc-light-showroom__wheel" cx="181" cy="239" r="31" />
                        <circle class="mc-light-showroom__wheel" cx="463" cy="239" r="31" />
                        <circle class="mc-light-showroom__hub" cx="181" cy="239" r="11" />
                        <circle class="mc-light-showroom__hub" cx="463" cy="239" r="11" />
                    </svg>
                </div>
                <div class="mc-light-showroom__copy">
                    <span class="mc-edition">Autoverse / assinatura</span>
                    <p>Design em movimento.<br><em>Sem excesso.</em></p>
                    <span class="mc-edition" data-light-showroom-status>Animação SVG · consumo mínimo</span>
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
