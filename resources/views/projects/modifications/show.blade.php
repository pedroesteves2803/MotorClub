<x-layouts.public :title="$modification->title.' · '.$project->title">
    <div class="mx-auto max-w-6xl space-y-10">
        <a class="mc-text-link" href="{{ route('projects.show',$project) }}">← Voltar ao projeto</a>
        <header class="mc-intro"><p class="mc-edition">Modificação · {{ ['planned'=>'Planejada','in_progress'=>'Em andamento','completed'=>'Concluída'][$modification->status] ?? $modification->status }}</p><h1>{{ $modification->title }}</h1><p>{{ $modification->category }} · {{ $project->title }}</p></header>
        @if($modification->description)<div class="mc-surface rounded-2xl p-6 whitespace-pre-line">{{ $modification->description }}</div>@endif
        <section class="grid gap-6 md:grid-cols-2" aria-label="Fotos da modificação">
            @forelse($modification->photos as $photo)<figure><img class="max-h-[650px] w-full rounded-2xl object-cover" src="{{ route('projects.media.image',$photo) }}" alt="{{ $photo->caption ?: 'Foto da modificação' }}"><figcaption class="mt-2 mc-muted">{{ $photo->caption ?: ucfirst($photo->kind) }}</figcaption></figure>@empty<p class="mc-muted">Esta modificação ainda não tem fotos públicas.</p>@endforelse
        </section>
        @if($modification->parts->isNotEmpty())<section class="space-y-5"><h2 class="text-3xl font-bold">Peças e componentes</h2><div class="grid gap-5 md:grid-cols-2">@foreach($modification->parts as $part)<a class="mc-surface rounded-2xl p-6" href="{{ route('projects.parts.show',[$project,$modification,$part]) }}"><p class="mc-edition">{{ ['planned'=>'Planejada','in_progress'=>'Em andamento','installed'=>'Instalada'][$part->status] ?? $part->status }}</p><h3 class="text-xl font-semibold">{{ $part->name }}</h3><p class="mc-muted">{{ trim($part->brand.' '.$part->model) }}</p><p class="mt-3">Ver fotos e detalhes →</p></a>@endforeach</div></section>@endif
    </div>
</x-layouts.public>
