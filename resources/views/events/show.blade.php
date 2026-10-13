@php
    $eventDescription = \Illuminate\Support\Str::of($event->description)->squish()->limit(155);
    $eventBreadcrumb = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [['@type' => 'ListItem', 'position' => 1, 'name' => 'Eventos', 'item' => route('events.index')], ['@type' => 'ListItem', 'position' => 2, 'name' => $event->title, 'item' => route('events.show', $event)]]];
    $eventStructuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => $event->title,
        'description' => (string) $eventDescription,
        'startDate' => $event->starts_at->toIso8601String(),
        'eventStatus' => 'https://schema.org/EventScheduled',
        'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        'location' => ['@type' => 'Place', 'name' => $event->venue, 'address' => ['@type' => 'PostalAddress', 'addressLocality' => $event->city, 'addressRegion' => $event->state, 'addressCountry' => 'BR']],
        'organizer' => ['@type' => 'Organization', 'name' => $event->organizer],
        'url' => route('events.show', $event),
    ];
@endphp
<x-layouts.public :title="$event->title.' — Evento automotivo | Autoverse'" :description="$eventDescription" :noindex="!$event->is_public || $event->user->is_demo" :canonical="route('events.show', $event)" :jsonLd="[$eventStructuredData, $eventBreadcrumb]"><section class="space-y-5 mc-page-intro"><p class="mc-edition">{{ config('motorclub.event_categories.'.$event->category) }}</p><h1 class="mc-title">{{ $event->title }}</h1><p class="text-2xl">{{ $event->starts_at->timezone('America/Sao_Paulo')->format('d/m/Y · H:i') }} · Horário de Brasília</p><p>{{ $event->venue }} · {{ $event->city }} / {{ $event->state }}</p><p>Organização: {{ $event->organizer }}</p>@if($event->user->is_demo)<p class="mc-notice p-5 rounded-xl">Evento fictício para demonstração. Não compareça ao endereço.</p>@endif<p class="whitespace-pre-line">{{ $event->description }}</p>@if(auth()->id()===$event->user_id)<a class="mc-link-button" href="{{ route('events.edit',$event) }}">Editar evento</a><form method="POST" action="{{ route('events.destroy',$event) }}" onsubmit="return confirm('Excluir este evento?')">@csrf @method('DELETE')<button class="underline">Excluir evento</button></form>@endif</section>@if($shotGalleries->isNotEmpty())<section class="mt-10"><h2 class="text-3xl font-bold">Shots do evento</h2><div class="mc-gallery-grid mt-5 grid gap-5 md:grid-cols-3">@foreach($shotGalleries as $gallery)<a class="mc-surface overflow-hidden rounded-xl" href="{{ route('shots.galleries.show',$gallery) }}">@if($gallery->previewPhoto)<img loading="lazy" decoding="async" class="aspect-[4/3] w-full object-cover" src="{{ route('shots.photos.preview',$gallery->previewPhoto) }}" alt="{{ $gallery->title }}">@endif<div class="p-5">{{ $gallery->title }} · {{ $gallery->user->photographerProfile->professional_name }}</div></a>@endforeach</div></section>@endif</x-layouts.public>
