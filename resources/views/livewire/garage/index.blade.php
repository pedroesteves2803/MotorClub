<div class="mx-auto w-full max-w-7xl space-y-10">
    <header class="mc-intro flex flex-wrap items-end justify-between gap-6 pb-5">
        <div><p class="mb-4 text-xs font-bold uppercase tracking-[0.25em] text-[var(--mc-orange)]">Sua coleção de histórias</p><h1 class="text-5xl font-bold tracking-tight md:text-7xl">Mais que um carro.<br><span class="mc-muted">Parte de você.</span></h1><p class="mt-6 max-w-lg text-lg mc-muted">Um lugar para cuidar, registrar e viver cada capítulo da sua garagem.</p></div>
        <flux:button wire:click="create" variant="primary">Adicionar veículo</flux:button>
    </header><nav class="my-6 flex flex-wrap gap-5" aria-label="Minha atividade"><a class="mc-text-link" href="{{ route('projects.index',['mine'=>1]) }}">Meus projetos →</a>@if(\App\Models\FeatureSetting::enabled('workshops'))<a class="mc-text-link" href="{{ route('quotes.index') }}">Meus orçamentos →</a>@endif @if(\App\Models\FeatureSetting::enabled('events'))<a class="mc-text-link" href="{{ route('events.index',['mine'=>1]) }}">Meus eventos →</a>@endif</nav>
    @if(session('status'))<p role="status" class="rounded-lg border border-green-600 p-4">{{ session('status') }}</p>@endif
    @if($formOpen)
        <form wire:submit="save" class="space-y-5 rounded-xl border border-zinc-300 p-6 dark:border-zinc-600">
            <h2 class="text-xl font-semibold">{{ $editingId ? 'Editar veículo' : 'Novo veículo' }}</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                <flux:input wire:model="brand" label="Marca" placeholder="Volkswagen" required maxlength="80" />
                <flux:input wire:model="model" label="Modelo" placeholder="Golf" required maxlength="80" />
                <flux:input wire:model="trim" label="Versão" placeholder="GTI MK7" required maxlength="100" />
                <flux:input wire:model="model_year" label="Ano do modelo" type="number" min="1886" max="{{ now()->year + 1 }}" required />
                <flux:input wire:model="engine" label="Motorização" placeholder="2.0 TSI" required maxlength="100" />
                <flux:select wire:model="visibility" label="Visibilidade"><flux:select.option value="private">Privado — somente você</flux:select.option><flux:select.option value="public">Público — acessível por link</flux:select.option></flux:select>
            </div>
            <p class="text-sm mc-muted">Ao escolher público, você permite compartilhar os dados do veículo. Seu e-mail não será exibido.</p>
            <div class="flex gap-3"><flux:button type="submit" variant="primary">Salvar veículo</flux:button><flux:button wire:click="resetForm" type="button">Cancelar</flux:button></div>
        </form>
    @endif
    <section class="mc-collection grid gap-8 lg:grid-cols-2" aria-label="Veículos cadastrados">
        @forelse($vehicles as $vehicle)
            <article wire:key="vehicle-{{ $vehicle->id }}" class="mc-car overflow-hidden rounded-2xl mc-surface shadow-sm ">
                <a href="{{ route('garage.details', $vehicle) }}" wire:navigate class="group block overflow-hidden bg-zinc-200 dark:bg-zinc-800">
                    @if($vehicle->photos->first())
                        <img src="{{ route('vehicle-photos.show', $vehicle->photos->first()) }}" alt="{{ $vehicle->brand }} {{ $vehicle->model }}" class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-105" />
                    @else
                        <div class="flex aspect-[16/10] items-center justify-center text-5xl font-black tracking-tighter mc-muted">{{ $vehicle->model }}<span class="text-[var(--mc-orange)]">/</span></div>
                    @endif
                </a>
                <div class="p-6 md:p-8">
                <p class="text-sm mc-muted">{{ $vehicle->brand }} · {{ $vehicle->model_year }}</p>
                <h2 class="mt-2 text-4xl font-bold tracking-tight">{{ $vehicle->model }}</h2>
                <p class="mt-2">{{ $vehicle->trim }} · {{ $vehicle->engine }}</p>
                <p class="mt-4 text-sm">{{ $vehicle->visibility === 'public' ? 'Público' : 'Privado' }}</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <flux:button href="{{ route('garage.details', $vehicle) }}" wire:navigate>Fotos e histórico</flux:button>
                    <flux:button wire:click="edit({{ $vehicle->id }})">Editar</flux:button>
                    @if($vehicle->visibility === 'public')<flux:button href="{{ route('vehicles.show', $vehicle) }}">Ver página pública</flux:button>@endif
                    <flux:button wire:click="delete({{ $vehicle->id }})" wire:confirm="Excluir este veículo permanentemente? Esta ação não pode ser desfeita." variant="danger">Excluir</flux:button>
                </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-zinc-400 p-10 md:col-span-2"><h2 class="text-xl font-semibold">Sua garagem começa aqui</h2><p class="mt-3 mc-muted">Adicione seu primeiro veículo para organizar as informações do seu carro. Ele será privado por padrão.</p></div>
        @endforelse
    </section>
    {{ $vehicles->links() }}
</div>
