<x-layouts.public :title="$title.' · Autoverse'" :description="$title.' do Autoverse.'">
    <article class="mx-auto max-w-4xl space-y-8">
        <header class="mc-page-intro space-y-4">
            <p class="mc-edition">INSTITUCIONAL · VERSÃO {{ $version }}</p>
            <h1 class="mc-title">{{ $title }}</h1>
            @if($isDraft)
                <p class="mc-notice rounded-xl p-4" role="note">Minuta baseada no funcionamento atual do Autoverse. Este documento precisa de revisão jurídica antes da publicação definitiva.</p>
            @endif
        </header>
        <div class="mc-surface prose prose-invert max-w-none rounded-2xl p-6 md:p-10">{!! $content !!}</div>
        <p class="text-sm mc-muted">Dúvidas sobre este documento? <a class="underline" href="{{ route('legal.help') }}">Fale com o Autoverse</a>.</p>
    </article>
</x-layouts.public>
