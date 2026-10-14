<!DOCTYPE html>
<html lang="pt-BR">
<head>@include('partials.head', ['title' => 'Entrar na Autoverse', 'description' => 'Acesse sua garagem digital e acompanhe seus projetos automotivos.', 'robots' => 'noindex,nofollow', 'canonical' => null])</head>
<body class="mc-site antialiased">
    <main class="mc-auth">
        <section class="mc-auth-story">
            <a href="{{ route('home') }}" class="mc-wordmark"><x-brand-mark class="mc-mark" /><span>AUTOVERSE</span></a>
            <div><p class="mc-edition">Para quem vive a cultura automotiva</p><h1>Todo carro tem uma história.<br><em>Mostre a sua.</em></h1><p class="max-w-sm text-base leading-relaxed">Da primeira volta ao próximo projeto, registre cada detalhe que torna seu carro único.</p></div>
            <p class="mc-edition mt-10">Garagem · Projetos · Fotografias</p>
        </section>
        <section class="mc-auth-form"><div class="w-full max-w-sm">{{ $slot }}</div></section>
    </main>
    @fluxScripts
</body>
</html>
