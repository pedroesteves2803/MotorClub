<div class="mx-auto w-full max-w-7xl space-y-10">
    <header class="mc-intro flex flex-wrap items-end justify-between gap-6 pb-5">
        <div><h1 class="text-5xl font-bold tracking-tight">Minha garagem</h1><p class="mt-4 max-w-lg text-lg mc-muted">Seus carros, fotos e modificações, organizados em um só lugar.</p></div>
        <flux:button wire:click="create" variant="primary">Adicionar carro</flux:button>
    </header><nav class="my-6 flex flex-wrap gap-5" aria-label="Minha atividade"><a class="mc-text-link" href="{{ route('projects.index',['mine'=>1]) }}">Meus projetos →</a>@if(\App\Models\FeatureSetting::enabled('workshops'))<a class="mc-text-link" href="{{ route('quotes.index') }}">Meus orçamentos →</a>@endif @if(\App\Models\FeatureSetting::enabled('events'))<a class="mc-text-link" href="{{ route('events.index',['mine'=>1]) }}">Meus eventos →</a>@endif</nav>
    @if(session('status'))<p role="status" class="rounded-lg border border-green-600 p-4">{{ session('status') }}</p>@endif
    @if($formOpen)
        <form wire:submit="save" class="mc-surface space-y-8 rounded-2xl border border-[var(--mc-line)] p-6 shadow-sm md:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-[var(--mc-line)] pb-6"><div><h2 class="mt-2 text-2xl font-semibold">{{ $editingId ? 'Editar carro e projeto' : 'Novo carro e projeto' }}</h2><p class="mt-2 max-w-2xl mc-muted">Preencha os dados do carro ou da moto. O projeto e as fotos podem ser adicionados agora ou depois.</p></div></div>
            <fieldset class="space-y-5"><legend class="text-lg font-semibold">1. Identidade do veículo</legend><div class="grid gap-5 sm:grid-cols-2">
                <flux:select wire:model="vehicle_type" label="Tipo de veículo">@foreach(config('motorclub.vehicle_types') as $type => $label)<flux:select.option value="{{ $type }}">{{ $label }}</flux:select.option>@endforeach</flux:select>
                <flux:input wire:model="brand" label="Marca" placeholder="Volkswagen" required maxlength="80" />
                <flux:input wire:model="model" label="Modelo" placeholder="Golf" required maxlength="80" />
                <flux:input wire:model="public_name" label="Nome público" placeholder="Venom Black MK7" maxlength="120" />
                <flux:input wire:model="trim" label="Versão / cilindrada" placeholder="GTI MK7 ou 689 cc" required maxlength="100" />
                <flux:input wire:model="generation" label="Geração" placeholder="MK7" maxlength="50" />
                <flux:input wire:model="color" label="Cor" placeholder="Preto" maxlength="50" />
                <flux:input wire:model="model_year" label="Ano do modelo" type="number" min="1886" max="{{ now()->year + 1 }}" required />
                <flux:input wire:model="engine" label="Motorização" placeholder="2.0 TSI" required maxlength="100" />
                <flux:input wire:model="mileage" label="Quilometragem (km)" type="number" min="0" max="2000000" />
                <flux:input wire:model="acquired_on" label="Data de aquisição" type="date" max="{{ now()->toDateString() }}" />
                <flux:input wire:model="location" label="Localização geral" placeholder="São Paulo, SP" maxlength="100" />
                <flux:input wire:model="instagram_url" label="Instagram do carro" type="url" placeholder="https://www.instagram.com/..." maxlength="255" />
                <flux:checkbox wire:model="mileage_approximate" label="Quilometragem aproximada" />
                <flux:select wire:model="visibility" label="Visibilidade"><flux:select.option value="private">Privado — somente você</flux:select.option><flux:select.option value="public">Público — acessível por link</flux:select.option></flux:select>
            </div><p class="text-sm mc-muted">Ao escolher público, você permite compartilhar os dados do carro. Seu e-mail não será exibido.</p></fieldset>

            <fieldset class="space-y-5 border-t border-[var(--mc-line)] pt-7"><div class="flex flex-wrap items-start justify-between gap-4"><div><legend class="text-lg font-semibold">2. Projeto principal</legend><p class="mt-1 mc-muted">Registre o objetivo do projeto. Depois de salvar, adicione modificações e peças.</p></div><flux:checkbox wire:model.live="project_enabled" label="Criar ou atualizar agora" /></div>
                @if($project_enabled)<div class="grid gap-5 sm:grid-cols-2"><flux:input wire:model="project_title" label="Título do projeto" placeholder="Venom Black MK7 · fase 1" maxlength="150" /><flux:select wire:model="project_category" label="Categoria"><flux:select.option value="maintenance">Manutenção</flux:select.option><flux:select.option value="performance">Performance</flux:select.option><flux:select.option value="appearance">Visual</flux:select.option><flux:select.option value="restoration">Restauração</flux:select.option></flux:select><flux:select wire:model="project_status" label="Status"><flux:select.option value="planning">Planejamento</flux:select.option><flux:select.option value="ongoing">Em andamento</flux:select.option><flux:select.option value="completed">Concluído</flux:select.option></flux:select><flux:input wire:model="project_cost" label="Investimento (R$ · opcional)" type="number" min="0" step="0.01" max="9999999999.99" /><flux:textarea class="sm:col-span-2" wire:model="project_description" label="História e objetivo" placeholder="O que você quer registrar neste projeto?" maxlength="5000" /><flux:textarea wire:model="project_parts" label="Peças e modificações (opcional)" maxlength="2000" /><flux:textarea wire:model="project_services" label="Serviços e próximos passos (opcional)" maxlength="2000" /><div class="flex flex-wrap gap-5 sm:col-span-2"><flux:checkbox wire:model="project_show_cost" label="Exibir o investimento publicamente" /><flux:checkbox wire:model="project_is_public" label="Publicar projeto (o carro também precisa estar público)" /></div></div>@else<p class="rounded-xl border border-dashed border-[var(--mc-line)] p-4 text-sm mc-muted">Você poderá criar o projeto depois em <a class="mc-text-link" href="{{ route('projects.index', ['mine' => 1]) }}">Meus projetos</a>.</p>@endif
            </fieldset>

            <fieldset class="space-y-5 border-t border-[var(--mc-line)] pt-7"><legend class="text-lg font-semibold">3. Fotos do carro</legend><p class="mc-muted">Envie várias fotos de uma vez. Estas fotos aparecem na galeria do carro. Fotos de modificações e peças são adicionadas no projeto.</p><flux:input type="file" wire:model="photos" label="Selecionar fotos" accept="image/jpeg,image/png,image/webp" multiple /><p class="text-sm mc-muted">JPG, PNG ou WebP · até 5 MB cada · máximo de 12 fotos por carro.</p><p wire:loading wire:target="photos" class="text-sm mc-muted">Preparando fotos…</p>@error('photos')<p class="text-sm text-red-600" role="alert">{{ $message }}</p>@enderror</fieldset>

            <div class="flex flex-wrap gap-3 border-t border-[var(--mc-line)] pt-7"><flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save,photos">{{ $editingId ? 'Salvar alterações' : ($project_enabled ? 'Salvar carro e projeto' : 'Salvar carro') }}</flux:button><flux:button wire:click="resetForm" type="button">Cancelar</flux:button></div>
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
                <p class="text-sm mc-muted">{{ config('motorclub.vehicle_types.'.$vehicle->vehicle_type, 'Veículo') }} · {{ $vehicle->brand }} · {{ $vehicle->model_year }}</p>
                <h2 class="mt-2 text-4xl font-bold tracking-tight">{{ $vehicle->public_name ?: $vehicle->model }}</h2>
                @if($vehicle->public_name)<p class="mt-1 text-sm mc-muted">{{ $vehicle->model }}</p>@endif
                <p class="mt-2">{{ $vehicle->trim }} · {{ $vehicle->engine }}</p>
                @if($vehicle->color || $vehicle->mileage || $vehicle->location)<p class="mt-2 text-sm mc-muted">{{ $vehicle->color }}{{ $vehicle->mileage ? ' · '.number_format($vehicle->mileage, 0, ',', '.').' km'.($vehicle->mileage_approximate ? ' (aprox.)' : '') : '' }}{{ $vehicle->location ? ' · '.$vehicle->location : '' }}</p>@endif
                <p class="mt-4 text-sm">{{ $vehicle->visibility === 'public' ? 'Público' : 'Privado' }}</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <flux:button href="{{ route('garage.details', $vehicle) }}" wire:navigate>Fotos e histórico</flux:button>
                    <flux:button wire:click="edit({{ $vehicle->id }})">Editar</flux:button>
                    @if($vehicle->visibility === 'public')<flux:button href="{{ $vehicle->slug ? route('vehicles.slug.show', ['slug' => $vehicle->slug]) : route('vehicles.show', $vehicle) }}">Ver página pública</flux:button>@endif
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
