<x-layouts.public title="Configurações de cookies · Autoverse">
    <section class="mx-auto max-w-3xl space-y-8">
        <header class="mc-page-intro space-y-4"><p class="mc-edition">PRIVACIDADE</p><h1 class="mc-title">Configurações de cookies</h1><p class="text-xl mc-muted">Escolha o que pode ser usado além do funcionamento essencial.</p></header>
        <form method="POST" action="{{ route('cookies.preferences') }}" class="mc-surface mc-form rounded-2xl p-6 md:p-8">
            @csrf
            <label class="flex items-start gap-3"><input type="checkbox" checked disabled class="mt-1"><span><strong>Necessários</strong><br><small class="mc-muted">Sessão, autenticação, CSRF e preferências técnicas indispensáveis. Não podem ser desativados.</small></span></label>
            <label class="mt-6 flex items-start gap-3"><input type="checkbox" name="analytics" value="1" @checked($consent?->analytics) @disabled(!config('legal.optional_cookies_available')) class="mt-1"><span><strong>Métricas opcionais</strong><br><small class="mc-muted">{{ config('legal.optional_cookies_available') ? 'Ajudam a entender o uso da plataforma sem identificar você diretamente.' : 'Nenhum serviço de métricas opcional está ativo atualmente.' }}</small></span></label>
            <label class="mt-6 flex items-start gap-3"><input type="checkbox" name="personalization" value="1" @checked($consent?->personalization) @disabled(!config('legal.optional_cookies_available')) class="mt-1"><span><strong>Personalização opcional</strong><br><small class="mc-muted">{{ config('legal.optional_cookies_available') ? 'Permite lembrar preferências que não são essenciais.' : 'Nenhuma personalização opcional usa cookies atualmente.' }}</small></span></label>
            <div class="mt-8 flex flex-wrap items-center gap-4"><button class="mc-link-button">Salvar preferências</button><a class="mc-text-link" href="{{ route('legal.privacy.short') }}#cookies">Ler sobre cookies na Política de Privacidade</a></div>
        </form>
    </section>
</x-layouts.public>
