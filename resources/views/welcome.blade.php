<x-layouts.public title="MotorClub — Seu carro. Sua história. Sua comunidade.">
    <section class="mc-home-hero">
        <div class="mc-intro border-0!">
            <p class="mc-edition mb-6">MotorClub</p>
            <h1>Seu carro.<br>Sua história.<br><span>Sua comunidade.</span></h1>
            <p class="mt-8 max-w-lg text-lg leading-relaxed">Organize sua garagem, compartilhe seus projetos e encontre quem também vive essa paixão.</p>
            <div class="mt-8 flex flex-wrap gap-4"><a class="mc-link-button" href="{{ route('dashboard') }}">Criar minha garagem →</a><a class="mc-text-link" href="{{ route('explore') }}">Explorar a comunidade</a></div>
        </div>
        <aside class="mc-home-poster" aria-label="Manifesto MotorClub"><span class="mc-edition">O que nos move / 01</span><p>Alguns veem<br>um carro.<br><em>A gente vê<br>uma história.</em></p><span class="mc-edition">Garagem · Memórias · Estrada</span></aside>
    </section>
    <section class="mc-home-features" aria-label="O que você pode fazer">
        <article><span class="mc-edition">01 / Sua identidade</span><h2>Uma garagem com a sua cara.</h2><p>Cadastre seus veículos, adicione fotos e escolha o que fica privado ou público.</p></article>
        <article><span class="mc-edition">02 / Seus projetos</span><h2>O cuidado vira história.</h2><p>Registre cada etapa, peça e memória de um projeto do seu jeito.</p></article>
        <article><span class="mc-edition">03 / Shots</span><h2>Fotos que ficam.</h2><p>Encontre fotógrafos e galerias automotivas para guardar os melhores momentos.</p><a class="mc-text-link" href="{{ route('shots.index') }}">Ver Shots →</a></article>
    </section>
    <section class="border-t border-zinc-400 pt-8"><p class="mc-edition mb-3">Conheça a comunidade</p><h2 class="text-2xl font-bold">A garagem é o começo.</h2><p class="mt-3 max-w-3xl">Explore veículos, membros, fotógrafos e galerias que fazem parte do MotorClub.</p><div class="mt-6 flex flex-wrap gap-6"><a class="mc-text-link" href="{{ route('explore') }}">Explorar →</a></div></section>
</x-layouts.public>
