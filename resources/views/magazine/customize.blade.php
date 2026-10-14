@php
    $vehicleName = $vehicle->public_name ?: trim($vehicle->brand.' '.$vehicle->model);
    $highlightIds = collect(old('highlight_photo_ids', $settings->highlight_photo_ids ?? []))->map(fn ($id) => (int) $id);
    $chapterOrder = collect(old('chapter_order', $settings->chapter_order ?? []));
    $coverPhoto = $vehicle->photos->firstWhere('id', $settings->cover_photo_id) ?: $vehicle->photos->first();
@endphp
<x-layouts.app>
    <div class="mx-auto w-full max-w-6xl space-y-8">
        <header class="mc-intro">
            <p class="mc-edition">Revista digital</p>
            <h1 class="text-5xl font-bold tracking-tight">Sua edição, do seu jeito</h1>
            <p class="mt-4 max-w-2xl text-lg mc-muted">Escolha a capa, o ritmo e os destaques. O conteúdo vem da sua garagem.</p>
        </header>

        @if (session('status'))
            <p class="mc-notice rounded-xl p-4" role="status">{{ session('status') }}</p>
        @endif

        @if (! $project)
            <section class="mc-surface rounded-2xl p-6">
                <h2 class="text-xl font-semibold">Ainda falta um projeto</h2>
                <p class="mt-2 mc-muted">Crie o primeiro projeto deste veículo para abrir a revista.</p>
                <a class="mc-text-link mt-5 inline-block" href="{{ route('projects.create', ['vehicle_id' => $vehicle->id]) }}">Criar projeto</a>
            </section>
        @else
            <form method="POST" action="{{ route('garage.magazine.update', $vehicle) }}" class="grid gap-8 lg:grid-cols-[minmax(18rem,.8fr)_minmax(0,1.2fr)]">
                @csrf
                @method('PUT')

                <div class="space-y-6 lg:sticky lg:top-6">
                    <section class="mc-surface rounded-2xl p-6">
                        <h2 class="text-xl font-semibold">Identidade</h2>
                        <div class="mt-5 space-y-5">
                            <label class="block">
                                <span class="text-sm font-semibold">Título da edição</span>
                                <input name="title" value="{{ old('title', $settings->title ?: $magazine['title']) }}" maxlength="160" class="mt-2 w-full rounded-lg border border-[var(--mc-line)] bg-transparent px-3 py-3" placeholder="{{ $magazine['title'] }}">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold">Linha de apoio</span>
                                <input name="subtitle" value="{{ old('subtitle', $settings->subtitle ?: $vehicleName) }}" maxlength="240" class="mt-2 w-full rounded-lg border border-[var(--mc-line)] bg-transparent px-3 py-3" placeholder="{{ $vehicleName }}">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold">Estilo</span>
                                <select name="editorial_style" class="mt-2 w-full rounded-lg border border-[var(--mc-line)] bg-transparent px-3 py-3">
                                    <option value="premium" @selected(old('editorial_style', $settings->editorial_style) === 'premium')>Premium</option>
                                    <option value="racing" @selected(old('editorial_style', $settings->editorial_style) === 'racing')>Racing</option>
                                    <option value="classic" @selected(old('editorial_style', $settings->editorial_style) === 'classic')>Clássico</option>
                                    <option value="editorial" @selected(old('editorial_style', $settings->editorial_style) === 'editorial')>Editorial</option>
                                    <option value="technical" @selected(old('editorial_style', $settings->editorial_style) === 'technical')>Técnico</option>
                                    <option value="minimal" @selected(old('editorial_style', $settings->editorial_style) === 'minimal')>Minimalista</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold">Fundo</span>
                                <select name="theme" class="mt-2 w-full rounded-lg border border-[var(--mc-line)] bg-transparent px-3 py-3">
                                    <option value="dark" @selected(old('theme', $settings->theme) === 'dark')>Escuro</option>
                                    <option value="light" @selected(old('theme', $settings->theme) === 'light')>Claro</option>
                                </select>
                            </label>
                            <fieldset>
                                <legend class="text-sm font-semibold">Paleta</legend>
                                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                                    @foreach (['carbon' => 'Carbono', 'minimal' => 'Branco', 'racing' => 'Vermelho', 'performance' => 'Azul', 'sporty' => 'Verde', 'custom' => 'Personalizada'] as $value => $label)
                                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-[var(--mc-line)] px-3 py-2 text-sm has-[:checked]:border-[var(--mc-action)]">
                                            <input type="radio" name="palette" value="{{ $value }}" @checked(old('palette', $settings->palette ?: 'carbon') === $value)>
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                            <label class="flex items-center gap-3 text-sm">
                                <input type="hidden" name="is_published" value="0">
                                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $settings->is_published))>
                                <span>Publicar a revista</span>
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold">Cor de destaque</span>
                                <input class="mt-2 h-12 w-full cursor-pointer rounded-lg border border-[var(--mc-line)] bg-transparent p-1" type="color" name="primary_color" value="{{ old('primary_color', $settings->primary_color) }}">
                            </label>
                        </div>
                    </section>

                    <section class="mc-surface rounded-2xl p-6">
                        <div class="flex items-end justify-between gap-4">
                            <div><h2 class="text-xl font-semibold">Capa</h2><p class="mt-1 text-sm mc-muted">Escolha uma foto já cadastrada.</p></div>
                            <span class="text-sm mc-muted">{{ $vehicle->photos->count() }} fotos</span>
                        </div>
                        <select name="cover_photo_id" class="mt-5 w-full rounded-lg border border-[var(--mc-line)] bg-transparent px-3 py-3">
                            <option value="">Usar o primeiro destaque</option>
                            @foreach ($vehicle->photos as $photo)
                                <option value="{{ $photo->id }}" @selected((string) old('cover_photo_id', $settings->cover_photo_id) === (string) $photo->id)>{{ $photo->caption ?: 'Foto '.$loop->iteration }}</option>
                            @endforeach
                        </select>
                        @if ($vehicle->photos->isNotEmpty())
                            <div class="mt-4 grid grid-cols-3 gap-2">
                                @foreach ($vehicle->photos->take(6) as $photo)
                                    <label class="relative cursor-pointer overflow-hidden rounded-lg border border-transparent has-[:checked]:border-[var(--mc-action)]">
                                        <input class="sr-only" type="checkbox" name="highlight_photo_ids[]" value="{{ $photo->id }}" @checked($highlightIds->contains($photo->id))>
                                        <img class="aspect-square w-full object-cover" src="{{ route('vehicle-photos.show', $photo) }}" alt="{{ $photo->caption ?: 'Foto do veículo' }}">
                                        <span class="absolute inset-x-1 bottom-1 rounded bg-black/65 px-1 py-0.5 text-center text-[10px] text-white">Destaque</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </section>
                </div>

                <div class="space-y-6">
                    <section class="mc-surface rounded-2xl p-6">
                        <div class="flex items-end justify-between gap-4">
                            <div><h2 class="text-xl font-semibold">Ordem dos capítulos</h2><p class="mt-1 text-sm mc-muted">Arraste ou use as setas.</p></div>
                            <span class="text-sm mc-muted">{{ count($magazine['chapters']) }} capítulos</span>
                        </div>
                        <ol class="mt-5 space-y-2" data-magazine-order>
                            @php
                                $chapters = collect($magazine['chapters']);
                                $orderedChapters = $chapterOrder->isNotEmpty()
                                    ? $chapters->sortBy(fn ($chapter) => ($position = $chapterOrder->search($chapter['id'])) === false ? 999 : $position)
                                    : $chapters;
                            @endphp
                            @foreach ($orderedChapters as $chapter)
                                <li class="flex items-center gap-3 rounded-lg border border-[var(--mc-line)] px-3 py-3" draggable="true" data-magazine-chapter="{{ $chapter['id'] }}">
                                    <span class="cursor-grab text-lg mc-muted" aria-hidden="true">⠿</span>
                                    <input type="hidden" name="chapter_order[]" value="{{ $chapter['id'] }}">
                                    <span class="min-w-0 flex-1 truncate text-sm font-semibold">{{ $chapter['title'] }}</span>
                                    <button type="button" class="mc-icon-button" data-magazine-move="up" aria-label="Mover {{ $chapter['title'] }} para cima">↑</button>
                                    <button type="button" class="mc-icon-button" data-magazine-move="down" aria-label="Mover {{ $chapter['title'] }} para baixo">↓</button>
                                </li>
                            @endforeach
                        </ol>
                    </section>

                    <section class="mc-surface rounded-2xl p-6">
                        <div class="flex items-start justify-between gap-4"><div><p class="mc-edition">Prévia</p><h2 class="mt-2 text-xl font-semibold">Assim a capa aparece</h2></div><span class="rounded-full border border-[var(--mc-line)] px-2.5 py-1 text-xs mc-muted">{{ $settings->is_published ? 'Publicada' : 'Rascunho' }}</span></div>
                        <div class="av-magazine-editor-preview mt-4" style="--magazine-accent: {{ $settings->primary_color }}">@if($coverPhoto)<img src="{{ route('vehicle-photos.show', $coverPhoto) }}" alt="Prévia da capa" loading="lazy">@endif<div class="av-magazine-editor-preview__veil"></div><div class="av-magazine-editor-preview__copy"><p class="text-xs uppercase tracking-[.2em]" style="color: var(--magazine-accent)">Revista do projeto</p><h3>{{ old('title', $settings->title ?: $magazine['title']) }}</h3><p>{{ old('subtitle', $settings->subtitle ?: $vehicleName) }}</p><div><span>{{ count($magazine['chapters']) }} capítulos</span><span>{{ $vehicle->photos->count() }} fotos disponíveis</span></div></div></div>
                        <div class="mt-5 flex flex-wrap gap-4"><a class="mc-text-link" href="{{ route('projects.show', $project) }}">Abrir revista</a><a class="mc-text-link" href="{{ route('garage.share.edit', $vehicle) }}">QR Code e compartilhamento</a></div>
                    </section>

                    <button class="w-full rounded-lg bg-[var(--mc-action)] px-5 py-3 font-semibold text-white" type="submit">Salvar edição</button>
                </div>
            </form>
        @endif
    </div>
</x-layouts.app>
