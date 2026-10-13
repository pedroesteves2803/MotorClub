<x-layouts.public :title="$part->name.' · '.$modification->title">
    <div class="mx-auto max-w-6xl space-y-10">
        <a class="mc-text-link" href="{{ route('projects.modifications.show',[$project,$modification]) }}">← Voltar à modificação</a>
        <header class="mc-intro"><p class="mc-edition">Peça · {{ ['planned'=>'Planejada','in_progress'=>'Em andamento','installed'=>'Instalada'][$part->status] ?? $part->status }}</p><h1>{{ $part->name }}</h1><p>{{ trim($part->brand.' '.$part->model) ?: 'Componente do projeto' }}</p></header>
        @if($part->description)<div class="mc-surface rounded-2xl p-6 whitespace-pre-line">{{ $part->description }}</div>@endif
        <section class="grid gap-6 md:grid-cols-3" aria-label="Fotos da peça">
            @forelse($part->photos as $photo)<figure><img class="aspect-square w-full rounded-2xl object-cover" src="{{ route('projects.media.image',$photo) }}" alt="{{ $photo->caption ?: 'Foto da peça' }}"><figcaption class="mt-2 mc-muted">{{ $photo->caption ?: ucfirst($photo->kind) }}</figcaption></figure>@empty<p class="mc-muted">Esta peça ainda não tem fotos públicas.</p>@endforelse
        </section>
    </div>
</x-layouts.public>
