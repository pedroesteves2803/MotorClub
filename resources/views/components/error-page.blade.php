@props([
    'code' => '500',
    'title' => 'Algo saiu do trajeto',
    'description' => 'Ocorreu um problema no Autoverse. Tente novamente em alguns instantes.',
    'variant' => 'workshop',
    'illustrationLabel' => 'Carro do Autoverse em uma área de manutenção',
    'actions' => [],
])
<!doctype html>
<html lang="pt-BR" data-error-document>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $code }} · {{ $title }} · Autoverse</title>
    <style>
        :root {
            color-scheme: light;
            --error-page: #f5f7fb;
            --error-page-wash: #e9f0fa;
            --error-surface: rgb(255 255 255 / .78);
            --error-ink: #101827;
            --error-muted: #53657a;
            --error-line: #cfdaea;
            --error-action: #1d5db5;
            --error-action-strong: #164a96;
            --error-action-ink: #fff;
            --error-blue-soft: #bcd8ff;
            --error-amber: #c67614;
            --error-road: #a9bdd5;
        }
        @media (prefers-color-scheme: dark) {
            :root:not(.light) {
                color-scheme: dark;
                --error-page: #070a0f;
                --error-page-wash: #0e1d31;
                --error-surface: rgb(14 20 30 / .82);
                --error-ink: #edf3fb;
                --error-muted: #a6b4c8;
                --error-line: #263548;
                --error-action: #4f8fe8;
                --error-action-strong: #72a7ff;
                --error-action-ink: #06101e;
                --error-blue-soft: #335f9e;
                --error-amber: #f0b15a;
                --error-road: #314762;
            }
        }
        :root.dark {
            color-scheme: dark;
            --error-page: #070a0f;
            --error-page-wash: #0e1d31;
            --error-surface: rgb(14 20 30 / .82);
            --error-ink: #edf3fb;
            --error-muted: #a6b4c8;
            --error-line: #263548;
            --error-action: #4f8fe8;
            --error-action-strong: #72a7ff;
            --error-action-ink: #06101e;
            --error-blue-soft: #335f9e;
            --error-amber: #f0b15a;
            --error-road: #314762;
        }
        :root.light { color-scheme: light; }
        *, *::before, *::after { box-sizing: border-box; }
        html { background: var(--error-page); }
        body {
            align-items: stretch;
            background:
                radial-gradient(circle at 9% 6%, color-mix(in srgb, var(--error-action) 10%, transparent), transparent 30rem),
                radial-gradient(circle at 92% 80%, color-mix(in srgb, var(--error-blue-soft) 24%, transparent), transparent 32rem),
                var(--error-page);
            color: var(--error-ink);
            display: flex;
            flex-direction: column;
            font-family: "Instrument Sans", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            min-height: 100vh;
            min-width: 320px;
        }
        a, button { font: inherit; }
        a { color: inherit; }
        .av-error-skip {
            background: var(--error-ink);
            color: var(--error-page);
            left: 1rem;
            padding: .7rem 1rem;
            position: absolute;
            top: -5rem;
            z-index: 4;
        }
        .av-error-skip:focus { top: 1rem; }
        .av-error-header {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin: 0 auto;
            max-width: 90rem;
            padding: clamp(1.25rem, 3vw, 2.5rem) clamp(1.25rem, 5vw, 5rem);
            width: 100%;
        }
        .av-error-wordmark {
            align-items: center;
            display: inline-flex;
            font-family: "Space Grotesk", "Instrument Sans", ui-sans-serif, sans-serif;
            font-size: .85rem;
            font-weight: 700;
            gap: .72rem;
            letter-spacing: .16em;
            text-decoration: none;
            text-transform: uppercase;
        }
        .av-error-wordmark svg { color: var(--error-action); height: 1.7rem; width: 2.25rem; }
        .av-error-theme {
            align-items: center;
            background: color-mix(in srgb, var(--error-surface) 86%, transparent);
            border: 1px solid var(--error-line);
            border-radius: 999px;
            color: var(--error-muted);
            cursor: pointer;
            display: inline-flex;
            height: 2.65rem;
            justify-content: center;
            width: 2.65rem;
        }
        .av-error-theme:hover { border-color: var(--error-action); color: var(--error-action); }
        .av-error-theme:focus-visible, .av-error-actions a:focus-visible, .av-error-actions button:focus-visible, .av-error-footer a:focus-visible { outline: 3px solid color-mix(in srgb, var(--error-action) 60%, transparent); outline-offset: 4px; }
        .av-error-theme svg { height: 1.35rem; width: 1.35rem; }
        .av-error-main {
            display: grid;
            flex: 1;
            margin: 0 auto;
            max-width: 90rem;
            padding: clamp(.5rem, 3vw, 2rem) clamp(1.25rem, 5vw, 5rem) clamp(2.5rem, 7vw, 6rem);
            width: 100%;
        }
        .av-error-shell {
            align-items: center;
            display: grid;
            gap: clamp(2rem, 7vw, 8rem);
            grid-template-columns: minmax(0, 1.12fr) minmax(18rem, .88fr);
            margin: auto 0;
        }
        .av-error-art {
            background: color-mix(in srgb, var(--error-surface) 88%, transparent);
            border: 1px solid var(--error-line);
            min-height: clamp(20rem, 52vw, 34rem);
            overflow: hidden;
            padding: clamp(1rem, 4vw, 3rem);
            position: relative;
        }
        .av-error-art::before {
            border: 1px solid color-mix(in srgb, var(--error-action) 34%, transparent);
            border-radius: 50%;
            content: '';
            height: min(80%, 29rem);
            position: absolute;
            right: -16%;
            top: -22%;
            width: min(80%, 29rem);
        }
        .av-error-scene { height: 100%; min-height: 18rem; position: relative; }
        .av-error-road {
            background: linear-gradient(90deg, transparent, color-mix(in srgb, var(--error-road) 54%, transparent), transparent);
            bottom: 13%;
            height: 1px;
            left: 3%;
            position: absolute;
            right: 3%;
        }
        .av-error-road::after {
            background: repeating-linear-gradient(90deg, transparent 0 2rem, color-mix(in srgb, var(--error-road) 72%, transparent) 2rem 3.6rem, transparent 3.6rem 5.6rem);
            content: '';
            height: 1px;
            left: 7%;
            position: absolute;
            right: 7%;
            top: .65rem;
        }
        .av-error-car {
            animation: av-error-car-hover 6s ease-in-out infinite;
            bottom: 17%;
            color: var(--error-ink);
            left: 4%;
            position: absolute;
            transform-origin: center bottom;
            width: min(92%, 40rem);
            z-index: 1;
        }
        .av-error-car svg { display: block; overflow: visible; width: 100%; }
        .av-error-car__body { fill: color-mix(in srgb, var(--error-surface) 66%, var(--error-action) 34%); opacity: .9; stroke: color-mix(in srgb, var(--error-ink) 68%, var(--error-action) 32%); stroke-width: 2.2; stroke-linejoin: round; }
        .av-error-car__glass { fill: color-mix(in srgb, var(--error-page-wash) 76%, var(--error-action) 24%); opacity: .9; stroke: color-mix(in srgb, var(--error-ink) 44%, var(--error-action) 56%); stroke-width: 1.6; stroke-linejoin: round; }
        .av-error-car__accent, .av-error-car__wheel, .av-error-car__hub { fill: none; stroke: color-mix(in srgb, var(--error-ink) 58%, var(--error-action) 42%); stroke-linecap: round; stroke-width: 2; }
        .av-error-car__hub { stroke-width: 1.5; }
        .av-error-car__lamp { fill: color-mix(in srgb, var(--error-action) 22%, transparent); opacity: .78; stroke: var(--error-action-strong); stroke-linecap: round; stroke-linejoin: round; stroke-width: 3.2; }
        .av-error-marker {
            align-items: center;
            background: color-mix(in srgb, var(--error-action) 14%, var(--error-surface));
            border: 1px solid color-mix(in srgb, var(--error-action) 58%, var(--error-line));
            border-radius: .9rem;
            color: var(--error-action-strong);
            display: flex;
            font-family: "Space Grotesk", "Instrument Sans", ui-sans-serif, sans-serif;
            font-size: clamp(1.1rem, 3vw, 1.7rem);
            font-weight: 700;
            height: clamp(3.2rem, 8vw, 5rem);
            justify-content: center;
            position: absolute;
            right: 13%;
            top: 19%;
            transform: rotate(5deg);
            width: clamp(3.2rem, 8vw, 5rem);
            z-index: 2;
        }
        .av-error-marker::before { content: '!'; }
        .av-error-page--detour .av-error-marker::before { content: '↗'; }
        .av-error-page--restart .av-error-marker::before { content: '↻'; }
        .av-error-page--speed .av-error-marker::before { content: '−'; }
        .av-error-page--pause .av-error-marker::before { content: 'Ⅱ'; }
        .av-error-page--blocked .av-error-marker::before { content: '×'; }
        .av-error-page--speed .av-error-marker { border-color: color-mix(in srgb, var(--error-amber) 60%, var(--error-line)); color: var(--error-amber); }
        .av-error-page--pause .av-error-marker { border-radius: 50%; }
        .av-error-gate { background: var(--error-amber); height: 4px; left: 15%; opacity: 0; position: absolute; top: 39%; transform: rotate(-18deg); width: 28%; z-index: 2; }
        .av-error-gate::after { background: repeating-linear-gradient(135deg, var(--error-page) 0 8px, transparent 8px 16px); content: ''; inset: 0; opacity: .7; position: absolute; }
        .av-error-page--blocked .av-error-gate { opacity: 1; }
        .av-error-cone { bottom: 23%; height: 0; opacity: 0; position: absolute; width: 0; z-index: 2; }
        .av-error-cone::before { border-bottom: 2.2rem solid var(--error-amber); border-left: .8rem solid transparent; border-right: .8rem solid transparent; content: ''; display: block; }
        .av-error-cone::after { background: var(--error-amber); content: ''; height: .35rem; left: -.95rem; position: absolute; top: 2.1rem; width: 1.9rem; }
        .av-error-page--workshop .av-error-cone { left: 12%; opacity: 1; }
        .av-error-page--workshop .av-error-cone + .av-error-cone { left: 25%; transform: scale(.75); }
        .av-error-page--workshop .av-error-marker { border-color: color-mix(in srgb, var(--error-amber) 60%, var(--error-line)); color: var(--error-amber); }
        .av-error-caption { bottom: 1.3rem; color: var(--error-muted); font-size: .72rem; left: clamp(1rem, 4vw, 3rem); letter-spacing: .12em; margin: 0; position: absolute; text-transform: uppercase; }
        .av-error-copy { max-width: 34rem; }
        .av-error-code { color: var(--error-action); font-family: "Space Grotesk", "Instrument Sans", ui-sans-serif, sans-serif; font-size: clamp(4.5rem, 12vw, 9rem); font-weight: 600; letter-spacing: -.1em; line-height: .76; margin: 0 0 1.4rem -.08em; }
        .av-error-copy h1 { font-family: "Space Grotesk", "Instrument Sans", ui-sans-serif, sans-serif; font-size: clamp(2.1rem, 5vw, 4.8rem); font-weight: 500; letter-spacing: -.075em; line-height: .94; margin: 0; max-width: 11ch; text-wrap: balance; }
        .av-error-copy > p:not(.av-error-code) { color: var(--error-muted); font-size: clamp(1rem, 1.5vw, 1.2rem); line-height: 1.6; margin: 1.35rem 0 0; max-width: 38rem; }
        .av-error-actions { align-items: center; display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 2rem; }
        .av-error-actions a, .av-error-actions button { align-items: center; border-radius: 999px; cursor: pointer; display: inline-flex; font-family: "Space Grotesk", "Instrument Sans", ui-sans-serif, sans-serif; font-size: .75rem; font-weight: 700; justify-content: center; letter-spacing: .07em; min-height: 2.9rem; padding: .75rem 1.1rem; text-decoration: none; text-transform: uppercase; }
        .av-error-actions a[data-primary] { background: var(--error-action); border: 1px solid var(--error-action); color: var(--error-action-ink); }
        .av-error-actions a[data-primary]:hover { background: var(--error-action-strong); border-color: var(--error-action-strong); }
        .av-error-actions a:not([data-primary]), .av-error-actions button { background: transparent; border: 1px solid var(--error-line); color: var(--error-ink); }
        .av-error-actions a:not([data-primary]):hover, .av-error-actions button:hover { border-color: var(--error-action); color: var(--error-action-strong); }
        .av-error-footer { align-items: center; color: var(--error-muted); display: flex; font-size: .8rem; gap: 1rem; justify-content: space-between; margin: 0 auto; max-width: 90rem; padding: 1rem clamp(1.25rem, 5vw, 5rem) 1.5rem; width: 100%; }
        .av-error-footer a { color: var(--error-muted); text-underline-offset: .2em; }
        .av-error-footer a:hover { color: var(--error-action-strong); }
        @keyframes av-error-car-hover { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        @media (max-width: 760px) {
            .av-error-header { padding-top: 1rem; }
            .av-error-main { padding-top: 0; }
            .av-error-shell { gap: 2.2rem; grid-template-columns: 1fr; }
            .av-error-art { min-height: 18rem; order: -1; }
            .av-error-scene { min-height: 15rem; }
            .av-error-car { bottom: 18%; left: 1%; width: 99%; }
            .av-error-marker { right: 8%; top: 14%; }
            .av-error-copy h1 { max-width: 14ch; }
            .av-error-footer { align-items: flex-start; flex-direction: column; gap: .5rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .av-error-car { animation: none; }
        }
    </style>
    <script>
        (() => {
            const applyAppearance = () => {
                const preference = window.localStorage.getItem('flux.appearance') || 'system';
                const dark = preference === 'dark' || (preference === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', dark);
                document.documentElement.classList.toggle('light', !dark);
                const control = document.querySelector('[data-error-theme]');
                if (control) {
                    control.setAttribute('aria-pressed', dark ? 'true' : 'false');
                    control.setAttribute('aria-label', dark ? 'Ativar tema claro' : 'Ativar tema escuro');
                }
            };
            const start = () => {
                applyAppearance();
                document.querySelector('[data-error-theme]')?.addEventListener('click', () => {
                    const isDark = document.documentElement.classList.contains('dark');
                    window.localStorage.setItem('flux.appearance', isDark ? 'light' : 'dark');
                    applyAppearance();
                });
            };
            document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', start, {once: true}) : start();
        })();
    </script>
</head>
<body class="av-error-page av-error-page--{{ $variant }}">
    <a class="av-error-skip" href="#error-main">Pular para o conteúdo</a>
    <header class="av-error-header">
        <a class="av-error-wordmark" href="{{ url('/') }}" aria-label="Autoverse, página inicial">
            <svg viewBox="0 0 64 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                <path d="M11 8h10c6.8 0 12 7 12 16s-5.2 16-12 16H11c-4.2-4.3-6.5-9.7-6.5-16S6.8 12.3 11 8Z" fill="currentColor" opacity=".22" stroke="currentColor" stroke-width="2.4" />
                <path d="M38 13h21M36 24h24M38 35h21" stroke="currentColor" stroke-linecap="round" stroke-width="2.4" />
            </svg>
            AUTOVERSE
        </a>
        <button class="av-error-theme" type="button" data-error-theme aria-pressed="false" aria-label="Alternar tema">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M5 4h4.5c3.1 0 5.5 3.4 5.5 8s-2.4 8-5.5 8H5c-1.8-2.1-2.8-4.8-2.8-8S3.2 6.1 5 4Z" stroke="currentColor" stroke-width="1.7"/><path d="M17 7h5M16 12h6M17 17h5" stroke="currentColor" stroke-linecap="round" stroke-width="1.7"/></svg>
        </button>
    </header>
    <main class="av-error-main" id="error-main">
        <section class="av-error-shell" aria-labelledby="error-title">
            <div class="av-error-art" role="img" aria-label="{{ $illustrationLabel }}">
                <div class="av-error-scene" aria-hidden="true">
                    <span class="av-error-marker"></span>
                    <span class="av-error-gate"></span>
                    <span class="av-error-cone"></span>
                    <span class="av-error-cone"></span>
                    <span class="av-error-road"></span>
                    <div class="av-error-car">
                        <svg viewBox="0 0 640 360" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path class="av-error-car__body" d="M63 238h34l35-56c15-24 43-42 74-49l107-20c23-4 48 1 67 15l59 46 78 13c31 5 55 27 65 58l6 21H63v-28Z" />
                            <path class="av-error-car__glass" d="m174 177 38-28c9-7 20-12 32-14l77-14c19-3 38 1 53 12l44 34H174Z" />
                            <path class="av-error-car__accent" d="M130 231h58m265 0h57M238 208h165" />
                            <path class="av-error-car__lamp" d="M93 208c22-11 46-17 70-18l25 13-29 26-67 2-8-12 9-11Z" />
                            <path class="av-error-car__lamp" d="m451 202 42-2c23 1 45 8 63 20l7 9-67 1-45-17v-11Z" />
                            <circle class="av-error-car__wheel" cx="181" cy="239" r="31" />
                            <circle class="av-error-car__wheel" cx="463" cy="239" r="31" />
                            <circle class="av-error-car__hub" cx="181" cy="239" r="11" />
                            <circle class="av-error-car__hub" cx="463" cy="239" r="11" />
                        </svg>
                    </div>
                </div>
                <p class="av-error-caption">AUTOVERSE / ROTA {{ $code }}</p>
            </div>
            <div class="av-error-copy">
                <p class="av-error-code" aria-label="Erro {{ $code }}">{{ $code }}</p>
                <h1 id="error-title">{{ $title }}</h1>
                <p>{{ $description }}</p>
                <nav class="av-error-actions" aria-label="Ações para continuar">
                    @foreach($actions as $action)
                        @if(($action['type'] ?? 'link') === 'back')
                            <button type="button" data-error-back>{{ $action['label'] }}</button>
                        @elseif(($action['type'] ?? 'link') === 'reload')
                            <button type="button" data-error-reload>{{ $action['label'] }}</button>
                        @else
                            <a href="{{ $action['href'] }}" @if(($action['style'] ?? '') === 'primary') data-primary @endif>{{ $action['label'] }}</a>
                        @endif
                    @endforeach
                </nav>
            </div>
        </section>
    </main>
    <footer class="av-error-footer">
        <span>Autoverse · Para quem vive a cultura automotiva.</span>
        <a href="{{ url('/ajuda-e-contato') }}">Ajuda e contato</a>
    </footer>
    <script>
        document.querySelector('[data-error-back]')?.addEventListener('click', () => {
            if (window.history.length > 1) window.history.back();
            else window.location.assign('{{ url('/') }}');
        });
        document.querySelector('[data-error-reload]')?.addEventListener('click', () => window.location.reload());
    </script>
</body>
</html>
