<footer class="mx-auto mt-12 max-w-7xl border-t border-[var(--mc-line)] px-6 py-10 lg:px-10">
    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
        <div><p class="text-xs uppercase tracking-[.16em]">Autoverse</p><p class="mt-3 text-sm mc-muted">Para quem vive a cultura automotiva.</p><div class="mt-4 flex flex-wrap gap-3 text-sm"><a class="mc-text-link" href="{{ route('explore') }}">Explorar veículos</a><a class="mc-text-link" href="{{ route('community') }}">Comunidade</a></div></div>
        <div><p class="text-xs uppercase tracking-[.16em]">Suporte</p><div class="mt-3 grid gap-2 text-sm"><a class="mc-text-link" href="{{ route('legal.help') }}">Central de ajuda</a><a class="mc-text-link" href="{{ route('feedback.create') }}">Enviar feedback</a><a class="mc-text-link" href="mailto:{{ config('legal.contact_email') }}">Contato</a></div></div>
        <div><p class="text-xs uppercase tracking-[.16em]">Legal</p><div class="mt-3 grid gap-2 text-sm"><a class="mc-text-link" href="{{ route('legal.terms') }}">Termos de Uso</a><a class="mc-text-link" href="{{ route('legal.privacy') }}">Privacidade</a><a class="mc-text-link" href="{{ route('legal.cookies') }}">Cookies</a><a class="mc-text-link" href="{{ route('legal.community') }}">Diretrizes da Comunidade</a><a class="mc-text-link" href="{{ route('cookies.settings') }}">Configurações de cookies</a></div></div>
        <div class="text-sm mc-muted"><p>Autoverse · {{ now()->year }}</p><p class="mt-3">{{ config('legal.responsible_party') }}</p></div>
    </div>
</footer>
