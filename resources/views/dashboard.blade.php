<x-layouts.app :title="__('Minha conta')">
    <section class="mc-surface max-w-3xl rounded-2xl p-8 md:p-10">
        <h1 class="font-display text-4xl font-medium tracking-[-.06em] md:text-6xl">Sua conta</h1>
        <p class="mt-5 max-w-xl text-lg mc-muted">Organize sua garagem, acompanhe projetos e gerencie sua privacidade em um só lugar.</p>
        <a href="{{ route('settings.profile') }}" class="mc-link-button mt-7">Abrir configurações</a>
    </section>
</x-layouts.app>
