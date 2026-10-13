@props([
    'name' => 'photos[]',
    'label' => 'Adicionar fotografias',
    'accept' => 'image/jpeg,image/png,image/webp',
    'maxFiles' => 12,
    'maxSize' => '5 MB',
    'hint' => 'JPEG, PNG ou WebP · até 5 MB por foto',
    'inputId' => null,
    'wireModel' => null,
    'capture' => null,
    'required' => false,
])
@php($inputId = $inputId ?: 'photo-uploader-'.str()->uuid())
<div {{ $attributes->merge(['class' => 'mc-photo-uploader']) }} data-photo-uploader data-max-files="{{ $maxFiles }}" data-max-size="{{ $maxSize }}">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <label for="{{ $inputId }}" class="block text-sm font-semibold">{{ $label }}</label>
            <p class="mt-1 text-sm mc-muted">{{ $hint }}</p>
        </div>
        <span data-photo-count class="mc-photo-uploader__count" aria-live="polite">Nenhuma foto selecionada</span>
    </div>
    <button type="button" class="mc-photo-uploader__dropzone mt-4" data-photo-dropzone aria-controls="{{ $inputId }}">
        <span class="mc-photo-uploader__icon" aria-hidden="true">↥</span>
        <span><strong>Arraste as fotos aqui</strong><br><span class="mc-muted">ou escolha da galeria{{ $capture ? ' · câmera disponível' : '' }}</span></span>
    </button>
    <input id="{{ $inputId }}" data-photo-input class="sr-only" type="file" name="{{ $name }}" accept="{{ $accept }}" multiple @if($required) required @endif @if($capture) capture="{{ $capture }}" @endif @if($wireModel) wire:model="{{ $wireModel }}" @endif>
    <p data-photo-status class="mt-3 text-sm mc-muted" role="status" aria-live="polite"></p>
    <div data-photo-preview class="mc-photo-uploader__preview mt-4" aria-live="polite"></div>
</div>
