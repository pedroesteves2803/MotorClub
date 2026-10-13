<!DOCTYPE html>
<html lang="pt-BR">
<head>@include('partials.head', ['title' => 'Entre para o MotorClub'])</head>
<body class="mc-site antialiased">
    <main class="mc-auth">
        <section class="mc-auth-story">
            <a href="{{ route('home') }}" class="mc-wordmark">MOTORCLUB</a>
            <div><p class="mc-edition">Para quem vive a cultura automotiva</p><h1>O carro é seu.<br><em>A história<br>também.</em></h1><p class="max-w-sm text-base leading-relaxed">Da primeira volta ao próximo projeto. Guarde cada detalhe do que faz seu carro ser único.</p></div>
            <p class="mc-edition mt-10">Garagem · Memórias · Estrada</p>
        </section>
        <section class="mc-auth-form"><div class="w-full max-w-sm"><p class="mc-edition mb-10">Seu próximo capítulo começa aqui</p>{{ $slot }}</div></section>
    </main>
    @fluxScripts
</body>
</html>
