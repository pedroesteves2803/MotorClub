<div class="mx-auto max-w-6xl space-y-10">
    <a href="{{ route('dashboard') }}" wire:navigate class="underline">Voltar à garagem</a>
    <header class="mc-intro"><p class="mc-edition mb-4">O diário do seu carro</p><h1 class="text-5xl font-bold tracking-tight md:text-7xl">{{ $vehicle->public_name ?: $vehicle->brand.' '.$vehicle->model }}</h1><p>{{ $vehicle->brand }} {{ $vehicle->model }} · {{ $vehicle->trim }} · {{ $vehicle->model_year }} · {{ $vehicle->engine }}</p>@if($vehicle->color || $vehicle->mileage || $vehicle->location)<p class="mc-muted">{{ $vehicle->color }}{{ $vehicle->mileage ? ' · '.number_format($vehicle->mileage, 0, ',', '.').' km'.($vehicle->mileage_approximate ? ' (aprox.)' : '') : '' }}{{ $vehicle->location ? ' · '.$vehicle->location : '' }}</p>@endif @if($vehicle->instagram_url)<a class="mc-text-link" href="{{ $vehicle->instagram_url }}" target="_blank" rel="noopener noreferrer">Instagram do carro ↗</a>@endif</header>
    <nav class="mc-section-tabs" aria-label="Seções da garagem" data-section-tabs><a href="#fotos" data-section-tab>Fotos</a><a href="{{ route('projects.index', ['mine' => 1]) }}">Projetos</a><a href="#historico" data-section-tab>Histórico</a></nav>
    @if(session('status'))<p role="status">{{ session('status') }}</p>@endif
    <section id="fotos" class="space-y-5 rounded-2xl mc-surface p-6 shadow-sm  md:p-8">
        <h2 class="text-2xl font-semibold">Fotos do veículo</h2>
        <p>As fotos acompanham a privacidade do veículo. JPEG, PNG ou WebP, até 5 MB e 4096 × 4096 pixels. Limite de 12 fotos.</p>
        <form wire:submit="uploadPhoto" class="space-y-4"><flux:input type="file" wire:model="photo" label="Escolher foto" accept="image/jpeg,image/png,image/webp" /><p wire:loading wire:target="photo">Carregando foto…</p><flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="photo,uploadPhoto">Adicionar foto</flux:button></form>
        <div class="mc-media-grid grid gap-6 sm:grid-cols-2">@foreach($photos as $item)<figure wire:key="photo-{{ $item->id }}"><img src="{{ route('vehicle-photos.show', $item) }}" alt="Foto de {{ $vehicle->model }}" class="aspect-[16/10] w-full rounded-xl object-cover" /><flux:button class="mt-2" wire:click="deletePhoto({{ $item->id }})" wire:confirm="Excluir esta foto permanentemente?">Excluir foto</flux:button></figure>@endforeach</div>
    </section>
    @if($shotRequests->isNotEmpty())<section class="space-y-5 rounded-2xl mc-surface p-6 shadow-sm md:p-8"><h2 class="text-2xl font-semibold">Fotos adquiridas no Shots</h2><p>Prévia autorizada para sua garagem. O arquivo original continua protegido até uma futura liberação do fotógrafo.</p><div class="mc-media-grid grid gap-5 sm:grid-cols-2">@foreach($shotRequests as $request)<figure><img class="aspect-[4/3] w-full rounded-xl object-cover" src="{{ route('shots.photos.preview',$request->photo) }}" alt="Foto adquirida de {{ $vehicle->model }}"><figcaption class="mt-2">{{ $request->photo->gallery->title }} · {{ $request->photo->license }}</figcaption></figure>@endforeach</div></section>@endif
    <section class="space-y-5 rounded-2xl mc-surface p-6 shadow-sm  md:p-8">
        <h2 class="text-2xl font-semibold">{{ $entryId ? 'Editar registro' : 'Registrar manutenção ou modificação' }}</h2>
        <p>O histórico e os valores ficam visíveis somente para você nesta etapa.</p>
        <form wire:submit="saveEntry" class="grid gap-4 sm:grid-cols-2">
            <flux:select wire:model="category" label="Tipo"><flux:select.option value="maintenance">Manutenção</flux:select.option><flux:select.option value="modification">Modificação</flux:select.option></flux:select>
            <flux:input wire:model="occurred_on" type="date" label="Data (opcional)" max="{{ now()->toDateString() }}" />
            <flux:input wire:model="title" label="Título" maxlength="150" required />
            <flux:input wire:model="cost" label="Valor em R$ (opcional)" type="number" min="0" max="9999999999.99" step="0.01" />
            <flux:textarea wire:model="description" label="Descrição" maxlength="5000" required />
            <flux:textarea wire:model="parts" label="Peças utilizadas (opcional)" maxlength="2000" />
            <flux:textarea wire:model="services" label="Serviços realizados (opcional)" maxlength="2000" />
            <div class="flex items-end gap-3"><flux:button type="submit" variant="primary">Salvar registro</flux:button><flux:button wire:click="clearEntry" type="button">Limpar formulário</flux:button></div>
        </form>
    </section>
    <section id="historico" class="space-y-5"><h2 class="text-2xl font-semibold">Histórico</h2>
        @forelse($entries as $entry)<article wire:key="entry-{{ $entry->id }}" class="space-y-3 rounded-2xl mc-surface p-6 shadow-sm  md:p-8"><p>{{ $entry->occurred_on?->format('d/m/Y') ?? 'Data não informada' }} · {{ $entry->category === 'maintenance' ? 'Manutenção' : 'Modificação' }}</p><h3 class="text-xl font-semibold">{{ $entry->title }}</h3><p class="whitespace-pre-line">{{ $entry->description }}</p>@if($entry->parts)<p class="whitespace-pre-line">Peças: {{ $entry->parts }}</p>@endif @if($entry->services)<p class="whitespace-pre-line">Serviços: {{ $entry->services }}</p>@endif @if($entry->cost !== null)<p>Valor: R$ {{ number_format((float) $entry->cost, 2, ',', '.') }}</p>@endif<flux:button wire:click="editEntry({{ $entry->id }})">Editar registro</flux:button><flux:button wire:click="deleteEntry({{ $entry->id }})" wire:confirm="Excluir este registro permanentemente?">Excluir registro</flux:button></article>@empty<p>Nenhuma manutenção ou modificação registrada.</p>@endforelse
        {{ $entries->links() }}
    </section>
</div>
