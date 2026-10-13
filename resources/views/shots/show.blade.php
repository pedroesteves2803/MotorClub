@php
    $galleryDescription = \Illuminate\Support\Str::of($gallery->description ?: 'Galeria de fotografia automotiva no Autoverse Shots.')->squish()->limit(155);
    $galleryBreadcrumb = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [['@type' => 'ListItem', 'position' => 1, 'name' => 'Shots', 'item' => route('shots.index')], ['@type' => 'ListItem', 'position' => 2, 'name' => $gallery->title, 'item' => route('shots.galleries.show', $gallery)]]];
    $galleryStructuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'ImageGallery',
        'name' => $gallery->title,
        'description' => (string) $galleryDescription,
        'url' => route('shots.galleries.show', $gallery),
        'inLanguage' => 'pt-BR',
        'datePublished' => $gallery->shot_on?->toDateString(),
        'author' => ['@type' => 'Person', 'name' => $gallery->user->photographerProfile->professional_name],
    ];
    if ($gallery->previewPhoto) {
        $galleryStructuredData['image'] = route('shots.photos.preview', $gallery->previewPhoto);
    }
@endphp
<x-layouts.public :title="$gallery->title.' — Fotos automotivas | Autoverse Shots'" :description="$galleryDescription" :noindex="!$gallery->is_public" ogType="article" :canonical="route('shots.galleries.show', $gallery)" :jsonLd="[$galleryStructuredData, $galleryBreadcrumb]" :image="$gallery->previewPhoto ? route('shots.photos.preview', $gallery->previewPhoto) : null">
    <section class="space-y-4">
        <p class="mc-edition">Autoverse Shots · {{ $gallery->city }} / {{ $gallery->state }}</p>
        <h1 class="mc-title">{{ $gallery->title }}</h1>
        <p>{{ $gallery->description }}</p>
        <p>Por <a class="mc-text-link" href="{{ route('profiles.show', $gallery->user) }}">{{ $gallery->user->photographerProfile->professional_name }}</a> · {{ $gallery->shot_on->format('d/m/Y') }}</p>
        @if($gallery->event && $gallery->event->is_public && \App\Models\FeatureSetting::enabled('events'))
            <p>Evento: <a class="mc-text-link" href="{{ route('events.show', $gallery->event) }}">{{ $gallery->event->title }}</a></p>
        @endif
    </section>
    <section class="mc-gallery-grid mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse($gallery->photos as $photo)
            <article class="mc-surface overflow-hidden rounded-xl">
                <img class="aspect-[4/3] w-full object-cover" src="{{ route('shots.photos.preview', $photo) }}" alt="Prévia de {{ $gallery->title }}">
                @if($photo->brand || $photo->model)
                    <p class="px-5 pt-4">{{ trim($photo->brand.' '.$photo->model) }} {{ $photo->color ? '· '.$photo->color : '' }}</p>
                @endif
                <div class="p-5">
                    <p>{{ $photo->price === null ? 'Foto gratuita' : 'R$ '.number_format($photo->price, 2, ',', '.') }}</p>
                    <p class="mt-1 text-sm mc-muted">{{ $photo->license }}</p>
                    @auth
                        @if(auth()->id() !== $gallery->user_id)
                            <form class="mt-4 space-y-3" method="POST" action="{{ route('shots.requests.store', $photo) }}">
                                @csrf
                                @if($vehicles->isNotEmpty())
                                    <select class="mc-input w-full" name="vehicle_id">
                                        <option value="">Sem associar à garagem</option>
                                        @foreach($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}">{{ $vehicle->brand }} {{ $vehicle->model }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                <button class="mc-link-button">Tenho interesse</button>
                            </form>
                            <form class="mt-3 flex gap-2" method="POST" action="{{ route('shots.reports.store', $photo) }}">
                                @csrf
                                <input class="mc-input min-w-0" name="reason" maxlength="120" placeholder="Denunciar ou pedir remoção" required>
                                <button class="mc-text-link">Enviar</button>
                            </form>
                        @endif
                    @else
                        <a class="mc-text-link mt-4 inline-block" href="{{ route('login') }}">Entre para solicitar →</a>
                    @endauth
                </div>
            </article>
        @empty
            <p>Nenhuma foto publicada nesta galeria.</p>
        @endforelse
    </section>
</x-layouts.public>
