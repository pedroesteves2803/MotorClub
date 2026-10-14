<x-layouts.app>
    @php
        $vehicleName = $vehicle->public_name ?: trim($vehicle->brand.' '.$vehicle->model);
        $vehicleFacts = collect([$vehicle->brand.' '.$vehicle->model, $vehicle->model_year, $vehicle->trim])->filter()->implode(' · ');
    @endphp

    <div class="av-qr-page">
        <nav class="av-qr-breadcrumb" aria-label="Navegação estrutural">
            <a href="{{ route('dashboard') }}">Minha garagem</a>
            <span aria-hidden="true">/</span>
            <span>{{ $vehicleName }}</span>
        </nav>

        <header class="av-qr-intro">
            <div>
                <p class="mc-edition">Compartilhamento do projeto</p>
                <h1>Leve o projeto<br><em>com você.</em></h1>
                <p class="av-qr-intro__lede">Um link para compartilhar e um QR Code para colocar na garagem, no encontro ou onde o carro estiver.</p>
            </div>
            <div class="av-qr-intro__vehicle" aria-label="Veículo selecionado">
                <span class="av-qr-intro__dot" aria-hidden="true"></span>
                <div>
                    <strong>{{ $vehicleName }}</strong>
                    <span>{{ $vehicleFacts }}</span>
                </div>
            </div>
        </header>

        @if (session('status'))
            <p class="mc-notice rounded-xl p-4" role="status">{{ session('status') }}</p>
        @endif

        <section class="av-qr-hero" aria-labelledby="qr-section-title">
            <div class="av-qr-hero__copy">
                <p class="av-qr-kicker">Endereço público</p>
                <h2 id="qr-section-title">Uma página para<br>o carro inteiro.</h2>
                <p class="av-qr-muted">Quem abrir este endereço encontra o projeto completo, as fotos e a revista, conforme a visibilidade configurada.</p>

                <div class="av-qr-url" aria-label="Link público do projeto">
                    <span>{{ $publicUrl }}</span>
                    <button type="button" class="av-qr-icon-button" data-copy-link="{{ $publicUrl }}" aria-label="Copiar link público" title="Copiar link">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 8.5A2.5 2.5 0 0 1 11.5 6h6A2.5 2.5 0 0 1 20 8.5v6a2.5 2.5 0 0 1-2.5 2.5h-6A2.5 2.5 0 0 1 9 14.5v-6Z"/><path d="M15 6V5.5A2.5 2.5 0 0 0 12.5 3h-6A2.5 2.5 0 0 0 4 5.5v6A2.5 2.5 0 0 0 6.5 14H9"/></svg>
                    </button>
                </div>

                <div class="av-qr-actions">
                    <button type="button" class="mc-link-button" data-native-share="{{ $publicUrl }}" data-native-share-title="{{ $vehicleName }}">Compartilhar</button>
                    <a class="mc-text-link" href="{{ $publicUrl }}" target="_blank" rel="noopener">Abrir página</a>
                    <a class="mc-text-link" href="https://wa.me/?text={{ urlencode($publicUrl) }}" target="_blank" rel="noopener">WhatsApp</a>
                </div>

                <div class="av-qr-destination">
                    <span class="av-qr-destination__icon" aria-hidden="true">↗</span>
                    <div>
                        <strong>O QR Code leva para este endereço</strong>
                        <span>Se você mudar o slug, o Autoverse mantém os links antigos funcionando.</span>
                    </div>
                </div>
            </div>

            <div class="av-qr-preview">
                <div class="av-qr-preview__topline"><span>SCAN TO EXPLORE</span><span>01 / 01</span></div>
                <div class="av-qr-preview__code" style="background: {{ $settings->qr_background }}">
                    <img src="{{ route('garage.share.qr', $vehicle) }}" alt="QR Code do projeto de {{ $vehicleName }}" decoding="async">
                </div>
                <div class="av-qr-preview__caption">
                    <div>
                        <span>PROJETO AUTOVERSE</span>
                        <strong>{{ $vehicleName }}</strong>
                    </div>
                    <span class="av-qr-preview__mark" aria-hidden="true">AV</span>
                </div>
            </div>
        </section>

        <section class="av-qr-downloads" aria-labelledby="downloads-title">
            <div>
                <p class="av-qr-kicker">Pronto para usar</p>
                <h2 id="downloads-title">Baixe no formato certo.</h2>
                <p class="av-qr-muted">PNG para tela, SVG para impressão e etiqueta pronta para aplicar no carro.</p>
            </div>
            <div class="av-qr-downloads__actions">
                <a class="av-qr-download" href="{{ route('garage.share.qr.download', [$vehicle, 'format' => 'png']) }}"><strong>PNG</strong><span>Tela e mensagens</span><b aria-hidden="true">↓</b></a>
                <a class="av-qr-download" href="{{ route('garage.share.qr.download', [$vehicle, 'format' => 'svg']) }}"><strong>SVG</strong><span>Impressão nítida</span><b aria-hidden="true">↓</b></a>
                <a class="av-qr-download av-qr-download--accent" href="{{ route('garage.share.qr.download', [$vehicle, 'format' => 'sticker']) }}"><strong>Etiqueta</strong><span>Com nome e marca</span><b aria-hidden="true">↓</b></a>
            </div>
        </section>

        <section class="av-qr-settings" aria-labelledby="settings-title">
            <div class="av-qr-settings__intro">
                <p class="av-qr-kicker">Personalização</p>
                <h2 id="settings-title">Dê a sua identidade.</h2>
                <p class="av-qr-muted">Esses dados aparecem na etiqueta SVG. O código continua levando para o mesmo projeto.</p>
            </div>
            <form method="POST" action="{{ route('garage.share.qr.settings', $vehicle) }}" class="av-qr-form">
                @csrf
                @method('PUT')
                <label><span>Nome na etiqueta</span><input name="qr_label" value="{{ old('qr_label', $settings->qr_label ?: $vehicleName) }}" maxlength="120"></label>
                <label><span>Marca</span><input name="qr_brand" value="{{ old('qr_brand', $settings->qr_brand ?: 'Autoverse') }}" maxlength="120"></label>
                <label class="av-qr-color-field"><span>Cor do código</span><input name="qr_foreground" type="color" value="{{ old('qr_foreground', $settings->qr_foreground) }}"><small>{{ old('qr_foreground', $settings->qr_foreground) }}</small></label>
                <label class="av-qr-color-field"><span>Fundo</span><input name="qr_background" type="color" value="{{ old('qr_background', $settings->qr_background) }}"><small>{{ old('qr_background', $settings->qr_background) }}</small></label>
                <button type="submit" class="mc-link-button av-qr-form__submit">Salvar identidade</button>
            </form>
        </section>

        <details class="av-qr-slug">
            <summary><span><strong>Editar endereço público</strong><small>{{ $publicUrl }}</small></span><b aria-hidden="true">＋</b></summary>
            <form method="POST" action="{{ route('garage.share.slug', $vehicle) }}">
                @csrf
                <label><span>Slug do projeto</span><input name="slug" value="{{ old('slug', $vehicle->slug) }}" pattern="[a-z][a-z0-9-]*" maxlength="170" required></label>
                <button type="submit" class="mc-text-link">Salvar endereço</button>
            </form>
        </details>
    </div>
</x-layouts.app>
