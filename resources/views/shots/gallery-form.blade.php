<x-layouts.public :title="$gallery->exists ? 'Editar galeria · Shots' : 'Nova galeria · Shots'">
    <section class="shots-editor">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="mc-edition">Autoverse Shots</p>
                <h1 class="mc-title">{{ $gallery->exists ? 'Gerenciar galeria' : 'Nova galeria' }}</h1>
            </div>
            <a class="mc-text-link" href="{{ route('shots.manage') }}">Voltar para galerias →</a>
        </div>

        <form class="shots-editor__form mc-surface mc-form mt-6" method="POST" action="{{ $gallery->exists ? route('shots.galleries.update', $gallery) : route('shots.galleries.store') }}">
            @csrf
            @if($gallery->exists)
                @method('PUT')
            @endif

            <x-field name="title" label="Título" :value="$gallery->title" required maxlength="150" />
            <x-area name="description" label="Descrição" :value="$gallery->description" required maxlength="5000" />
            <div class="grid gap-5 md:grid-cols-2">
                <x-field name="city" label="Cidade" :value="$gallery->city" required maxlength="100" />
                <x-select-field name="state" label="Estado" :value="$gallery->state" :options="config('motorclub.states')" />
                <x-field name="shot_on" label="Data das fotos" type="date" :value="$gallery->shot_on?->format('Y-m-d')" required />
                @if(\App\Models\FeatureSetting::enabled("events"))
                    <x-select-field name="car_event_id" label="Evento relacionado" :value="$gallery->car_event_id" :options="[''=>'Sem evento']+$events->pluck('title','id')->all()" />
                @endif
            </div>
            <x-check name="is_public" label="Publicar galeria" :checked="$gallery->is_public" />
            <button class="mc-link-button w-full sm:w-auto">Salvar galeria</button>
        </form>
    </section>

    @if($gallery->exists)
        <section class="shots-editor__media mt-10 space-y-5">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <h2 class="text-3xl font-bold">Fotos da galeria</h2>
                <p class="mc-muted">Adicione, substitua ou remova fotos sem sair da página.</p>
            </div>

            <form class="shots-editor__upload mc-surface mc-form" method="POST" enctype="multipart/form-data" action="{{ route('shots.photos.upload', $gallery) }}">
                @csrf
                <x-photo-uploader name="photo[]" input-id="shots-photos" label="Adicionar fotografias à galeria" hint="JPG, PNG ou WebP · até 10 MB por foto · selecione várias" max-files="12" />
                <div class="grid gap-5 md:grid-cols-3">
                    <x-field name="brand" label="Marca do carro (opcional)" maxlength="80" />
                    <x-field name="model" label="Modelo (opcional)" maxlength="80" />
                    <x-field name="color" label="Cor (opcional)" maxlength="50" />
                </div>
                <x-field name="price" label="Preço em R$ (deixe vazio para gratuita)" type="number" min="0" step="0.01" />
                <x-field name="license" label="Licença de uso" value="Uso pessoal na Autoverse" required maxlength="120" />
                <x-check name="is_published" label="Publicar fotos" :checked="true" />
                <button class="mc-link-button w-full sm:w-auto">Salvar fotos</button>
            </form>

            <div class="shots-editor__photos grid gap-5 md:grid-cols-3">
                @foreach($gallery->photos as $photo)
                    <article class="mc-surface rounded-xl p-4">
                        <img class="aspect-[4/3] w-full rounded-xl object-cover" src="{{ route('shots.photos.preview', $photo) }}" alt="Prévia da foto">
                        <p class="mt-3">{{ $photo->price === null ? 'Gratuita' : 'R$ '.number_format($photo->price, 2, ',', '.') }}</p>
                        <p class="mc-muted">{{ $photo->processing_status === 'ready' ? 'Prévia protegida pronta' : 'Processamento: '.$photo->processing_status }} · {{ $photo->uuid ? 'ID '.Str::limit($photo->uuid, 8, '') : 'ID pendente' }}</p>
                        <div class="shots-editor__photo-actions mt-3">
                            <form class="grid gap-2" method="POST" enctype="multipart/form-data" action="{{ route('shots.photos.replace', $photo) }}">
                                @csrf
                                <input class="mc-input w-full" name="photo" type="file" accept="image/jpeg,image/png,image/webp" required>
                                <button class="mc-text-link w-full text-left">Substituir foto</button>
                            </form>
                            <form method="POST" action="{{ route('shots.photos.destroy', $photo) }}">
                                @csrf
                                @method('DELETE')
                                <button class="mc-text-link w-full text-left">Excluir</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <form method="POST" action="{{ route('shots.galleries.destroy', $gallery) }}" onsubmit="return confirm('Excluir galeria e suas fotos?')">
                @csrf
                @method('DELETE')
                <button class="mc-text-link">Excluir galeria</button>
            </form>
        </section>
    @endif
</x-layouts.public>
