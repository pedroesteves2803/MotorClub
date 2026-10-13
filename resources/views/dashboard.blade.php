<x-layouts.app :title="__('Minha conta')">
    <section class="max-w-3xl rounded-xl border border-zinc-200 p-8 dark:border-zinc-700">
        <h1 class="text-3xl font-bold">Bem-vindo ao MotorClub</h1>
        <p class="mt-4 text-zinc-600 dark:text-zinc-300">Sua conta está pronta. A garagem digital será o próximo módulo: veículos, histórico e privacidade.</p>
        <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">Esta é a fundação técnica. Projetos, oficinas, orçamentos e eventos ainda não estão disponíveis.</p>
        <a href="{{ route('settings.profile') }}" class="mt-6 inline-block underline underline-offset-4">Gerenciar minha conta</a>
    </section>
</x-layouts.app>
