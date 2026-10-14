<x-layouts.public title="Ajude a melhorar o Autoverse">
    <section class="mx-auto max-w-3xl space-y-8">
        <header class="mc-page-intro space-y-4"><p class="mc-edition">FEEDBACK</p><h1 class="mc-title">Ajude a melhorar o Autoverse</h1><p class="text-xl mc-muted">Uma ideia, um bug ou um elogio já ajuda. O envio leva menos de dois minutos.</p></header>
        <form method="POST" action="{{ route('feedback.store') }}" enctype="multipart/form-data" class="mc-surface mc-form rounded-2xl p-6 md:p-8">
            @csrf
            <div aria-hidden="true" class="hidden"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
            <x-select-field name="category" label="Sobre o que você quer falar?" :options="app(App\\Http\\Controllers\\FeedbackController::class)->categories()" required />
            <x-field name="title" label="Título" placeholder="Ex.: Quero organizar fotos por etapa" maxlength="160" required />
            <x-area name="description" label="Conte mais" placeholder="Explique o que aconteceu ou o que você gostaria de ver." maxlength="5000" required />
            <x-field name="image" label="Imagem ou captura de tela (opcional)" type="file" accept="image/jpeg,image/png,image/webp" />
            @auth
                <p class="text-sm mc-muted">Responderemos pelo e-mail da sua conta: <strong>{{ auth()->user()->email }}</strong>.</p>
            @else
                <x-field name="email" label="E-mail para retorno (opcional)" type="email" value="{{ old('email', $email) }}" maxlength="255" />
            @endauth
            <div class="flex flex-wrap items-center gap-4 pt-2"><button class="mc-link-button">Enviar feedback</button><a class="mc-text-link" href="{{ route('legal.help') }}">Precisa de suporte?</a></div>
        </form>
    </section>
</x-layouts.public>
