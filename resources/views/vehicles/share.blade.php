<x-layouts.app>
    <div class="mx-auto w-full max-w-6xl space-y-8">
        <header class="mc-intro">
            <p class="mc-edition">Identidade pública</p>
            <h1 class="text-5xl font-bold tracking-tight">Compartilhar {{ $vehicle->public_name ?: $vehicle->model }}</h1>
            <p class="mt-4 max-w-2xl text-lg mc-muted">Um endereço permanente para a página do veículo, com QR Code pronto para tela ou impressão.</p>
        </header>

        @if (session('status'))
            <p class="mc-notice rounded-xl p-4" role="status">{{ session('status') }}</p>
        @endif

        <div class="grid gap-8 lg:grid-cols-[1.1fr_.9fr]">
            <section class="mc-surface space-y-6 rounded-2xl p-6">
                <div>
                    <h2 class="text-xl font-semibold">Link público</h2>
                    <p class="mt-1 text-sm mc-muted">Este endereço não muda quando você troca o slug ou a capa.</p>
                </div>
                <p class="break-all rounded-lg border border-[var(--mc-line)] p-4 text-sm"><a class="mc-text-link" href="{{ $publicUrl }}">{{ $publicUrl }}</a></p>
                <div class="flex flex-wrap gap-4">
                    <button type="button" class="mc-text-link" data-copy-link="{{ $publicUrl }}">Copiar link</button>
                    <button type="button" class="mc-text-link" data-native-share="{{ $publicUrl }}" data-native-share-title="{{ $vehicle->public_name ?: $vehicle->model }}">Compartilhar</button>
                    <a class="mc-text-link" href="https://wa.me/?text={{ urlencode($publicUrl) }}" target="_blank" rel="noopener">WhatsApp</a>
                    <a class="mc-text-link" href="mailto:?subject={{ urlencode('Veja este veículo no Autoverse') }}&body={{ urlencode($publicUrl) }}">E-mail</a>
                </div>
                <details class="border-t border-[var(--mc-line)] pt-5">
                    <summary class="cursor-pointer text-sm font-semibold">Editar endereço legível</summary>
                    <form method="POST" action="{{ route('garage.share.slug', $vehicle) }}" class="mt-4 space-y-4">
                        @csrf
                        <label class="block"><span class="text-sm font-semibold">Slug</span><input name="slug" value="{{ old('slug', $vehicle->slug) }}" pattern="[a-z][a-z0-9-]*" maxlength="170" required class="mt-2 w-full rounded-lg border border-[var(--mc-line)] bg-transparent px-3 py-3"></label>
                        <button type="submit" class="rounded-lg border border-[var(--mc-line)] px-4 py-2 text-sm font-semibold">Salvar slug</button>
                    </form>
                </details>
            </section>

            <section class="mc-surface rounded-2xl p-6 text-center">
                <h2 class="text-xl font-semibold">QR Code</h2>
                <div class="mx-auto mt-5 max-w-xs rounded-2xl p-4" style="background: {{ $settings->qr_background }}">
                    <img class="mx-auto aspect-square w-full" src="{{ route('garage.share.qr', $vehicle) }}" alt="QR Code para {{ $vehicle->public_name ?: $vehicle->model }}">
                </div>
                <div class="mt-5 flex flex-wrap justify-center gap-4">
                    <a class="mc-text-link" href="{{ route('garage.share.qr.download', [$vehicle, 'format' => 'png']) }}">PNG</a>
                    <a class="mc-text-link" href="{{ route('garage.share.qr.download', [$vehicle, 'format' => 'svg']) }}">SVG</a>
                    <a class="mc-text-link" href="{{ route('garage.share.qr.download', [$vehicle, 'format' => 'sticker']) }}">Etiqueta SVG</a>
                </div>
            </section>
        </div>

        <section class="mc-surface rounded-2xl p-6">
            <h2 class="text-xl font-semibold">Identidade da etiqueta</h2>
            <form method="POST" action="{{ route('garage.share.qr.settings', $vehicle) }}" class="mt-5 grid gap-5 sm:grid-cols-2">
                @csrf
                @method('PUT')
                <label class="block"><span class="text-sm font-semibold">Nome</span><input name="qr_label" value="{{ old('qr_label', $settings->qr_label ?: ($vehicle->public_name ?: $vehicle->model)) }}" maxlength="120" class="mt-2 w-full rounded-lg border border-[var(--mc-line)] bg-transparent px-3 py-3"></label>
                <label class="block"><span class="text-sm font-semibold">Marca</span><input name="qr_brand" value="{{ old('qr_brand', $settings->qr_brand ?: 'Autoverse') }}" maxlength="120" class="mt-2 w-full rounded-lg border border-[var(--mc-line)] bg-transparent px-3 py-3"></label>
                <label class="block"><span class="text-sm font-semibold">Cor do código</span><input name="qr_foreground" type="color" value="{{ old('qr_foreground', $settings->qr_foreground) }}" class="mt-2 h-12 w-full cursor-pointer rounded-lg border border-[var(--mc-line)] bg-transparent p-1"></label>
                <label class="block"><span class="text-sm font-semibold">Fundo</span><input name="qr_background" type="color" value="{{ old('qr_background', $settings->qr_background) }}" class="mt-2 h-12 w-full cursor-pointer rounded-lg border border-[var(--mc-line)] bg-transparent p-1"></label>
                <div class="sm:col-span-2"><button type="submit" class="rounded-lg bg-[var(--mc-action)] px-5 py-3 font-semibold text-white">Salvar identidade</button></div>
            </form>
        </section>
    </div>
</x-layouts.app>
