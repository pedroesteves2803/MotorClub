<x-layouts.app :title="__('Minha conta')">
    <section class="mc-surface max-w-3xl rounded-2xl p-8 md:p-10">
        <p class="mc-edition">Autoverse / Conta</p>
        <h1 class="mt-4 font-display text-4xl font-medium tracking-[-.06em] md:text-6xl">Sua conta está pronta.</h1>
        <p class="mt-5 max-w-xl text-lg mc-muted">Continue para organizar sua garagem, acompanhar projetos e manter suas escolhas de privacidade em um só lugar.</p>
        <a href="{{ route('settings.profile') }}" class="mc-link-button mt-7">Gerenciar minha conta</a>
    </section>
</x-layouts.app>
