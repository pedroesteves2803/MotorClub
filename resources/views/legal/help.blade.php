<x-layouts.public title="Central de ajuda e contato · Autoverse">
    <section class="mx-auto max-w-4xl space-y-8">
        <header class="mc-page-intro space-y-4"><p class="mc-edition">SUPORTE</p><h1 class="mc-title">Central de ajuda e contato</h1><p class="text-xl mc-muted">Encontre orientação rápida ou envie uma mensagem para a equipe.</p></header>
        <div class="grid gap-5 md:grid-cols-2">
            <article class="mc-surface rounded-2xl p-6"><h2 class="text-2xl font-bold">Ajude a melhorar</h2><p class="mt-3 mc-muted">Sugestões, bugs e elogios chegam diretamente para a equipe.</p><a class="mc-link-button mt-5" href="{{ route('feedback.create') }}">Enviar feedback</a></article>
            <article class="mc-surface rounded-2xl p-6"><h2 class="text-2xl font-bold">Contato</h2><p class="mt-3 mc-muted">Para suporte, privacidade ou denúncias, use o e-mail:</p><a class="mt-3 inline-block underline" href="mailto:{{ config('legal.contact_email') }}">{{ config('legal.contact_email') }}</a></article>
        </div>
        <div class="mc-surface rounded-2xl p-6"><h2 class="text-2xl font-bold">Conteúdo e moderação</h2><p class="mt-3 mc-muted">Consulte as <a class="underline" href="{{ route('legal.community') }}">Diretrizes da Comunidade</a> para entender as regras, denúncias e contestação de decisões.</p></div>
    </section>
</x-layouts.public>
