<div class="mx-auto w-full max-w-7xl space-y-10">
    <header class="mc-intro flex flex-wrap items-end justify-between gap-6 pb-5">
        <div><h1 class="text-5xl font-bold tracking-tight">Minha garagem</h1><p class="mt-4 max-w-lg text-lg mc-muted">Seus carros, fotos e modificações, organizados em um só lugar.</p></div>
        <flux:button wire:click="create" variant="primary">Adicionar carro</flux:button>
    </header>
    <nav class="my-6 flex flex-wrap gap-5" aria-label="Minha atividade"><a class="mc-text-link" href="{{ route('projects.index',['mine'=>1]) }}">Meus projetos →</a>@if(\App\Models\FeatureSetting::enabled('workshops'))<a class="mc-text-link" href="{{ route('quotes.index') }}">Meus orçamentos →</a>@endif @if(\App\Models\FeatureSetting::enabled('events'))<a class="mc-text-link" href="{{ route('events.index',['mine'=>1]) }}">Meus eventos →</a>@endif</nav>
    @if(session('status'))<p role="status" class="rounded-lg border border-green-600 p-4">{{ session('status') }}</p>@endif
    @if($formOpen)
        <form wire:submit="save" class="mc-surface space-y-6 rounded-2xl border border-[var(--mc-line)] p-4 shadow-sm sm:p-6 md:space-y-8 md:p-8">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-[var(--mc-line)] pb-4 md:gap-4 md:pb-6"><div><h2 class="mt-2 text-2xl font-semibold">{{ $editingId ? 'Editar carro e projeto' : 'Novo carro e projeto' }}</h2><p class="mt-2 max-w-2xl mc-muted">Comece pela identidade. O projeto principal é criado automaticamente e os detalhes ficam para depois.</p></div></div>
            <fieldset class="space-y-4 md:space-y-5"><legend class="text-lg font-semibold">Identidade do veículo</legend><div class="grid gap-4 sm:grid-cols-2 md:gap-5">
                <flux:select wire:model="vehicle_type" label="Tipo de veículo">@foreach(config('motorclub.vehicle_types') as $type => $label)<flux:select.option value="{{ $type }}">{{ $label }}</flux:select.option>@endforeach</flux:select>
                <flux:input wire:model="brand" label="Marca" placeholder="Volkswagen" required maxlength="80" />
                <flux:input wire:model="model" label="Modelo" placeholder="Golf" required maxlength="80" />
                <flux:input wire:model="model_year" label="Ano do modelo" type="number" min="1886" max="{{ now()->year + 1 }}" required />
            </div></fieldset>

            @if(! $editingId)
                <div class="rounded-xl border border-dashed border-[var(--mc-line)] p-4"><p class="font-semibold">Quer adicionar mais agora?</p><p class="mt-1 text-sm mc-muted">É opcional. Você poderá completar o diário depois.</p><flux:button type="button" wire:click="$toggle('showAdvanced')" class="mt-3">{{ $showAdvanced ? 'Ocultar detalhes' : 'Adicionar detalhes opcionais' }}</flux:button></div>
            @endif

            @if($editingId || $showAdvanced)
                <fieldset class="space-y-4 border-t border-[var(--mc-line)] pt-6 md:space-y-5 md:pt-7"><legend class="text-lg font-semibold">Detalhes do veículo</legend><div class="grid gap-4 sm:grid-cols-2 md:gap-5">
                    <flux:input wire:model="public_name" label="Nome público (opcional)" placeholder="Venom Black MK7" maxlength="120" />
                    <flux:input wire:model="trim" label="Versão / cilindrada (opcional)" placeholder="GTI MK7 ou 689 cc" maxlength="100" />
                    <flux:input wire:model="generation" label="Geração (opcional)" placeholder="MK7" maxlength="50" />
                    <flux:input wire:model="color" label="Cor (opcional)" placeholder="Preto" maxlength="50" />
                    <flux:input wire:model="engine" label="Motorização (opcional)" placeholder="2.0 TSI" maxlength="100" />
                    <flux:input wire:model="mileage" label="Quilometragem (opcional)" type="number" min="0" max="2000000" />
                    <flux:input wire:model="acquired_on" label="Data de aquisição (opcional)" type="date" max="{{ now()->toDateString() }}" />
                    <flux:input wire:model="location" label="Localização geral (opcional)" placeholder="São Paulo, SP" maxlength="100" />
                    <flux:input wire:model="instagram_url" label="Instagram do carro (opcional)" type="url" placeholder="https://www.instagram.com/..." maxlength="255" />
                    <flux:checkbox wire:model="mileage_approximate" label="Quilometragem aproximada" />
                    <flux:select wire:model="visibility" label="Visibilidade"><flux:select.option value="private">Privado — somente você</flux:select.option><flux:select.option value="public">Público — acessível por link</flux:select.option></flux:select>
                </div><p class="text-sm mc-muted">Ao escolher público, você permite compartilhar os dados do carro. Seu e-mail não será exibido.</p></fieldset>

                <fieldset class="space-y-4 border-t border-[var(--mc-line)] pt-6 md:space-y-5 md:pt-7"><legend class="text-lg font-semibold">Projeto principal</legend><p class="mc-muted">O projeto já é criado automaticamente. Estes campos apenas personalizam o registro inicial.</p><div class="grid gap-4 sm:grid-cols-2 md:gap-5"><flux:input wire:model="project_title" label="Título do projeto (opcional)" placeholder="Venom Black MK7 · fase 1" maxlength="150" /><flux:select wire:model="project_category" label="Categoria"><flux:select.option value="maintenance">Manutenção</flux:select.option><flux:select.option value="performance">Performance</flux:select.option><flux:select.option value="appearance">Visual</flux:select.option><flux:select.option value="restoration">Restauração</flux:select.option></flux:select><flux:select wire:model="project_status" label="Status"><flux:select.option value="planning">Planejamento</flux:select.option><flux:select.option value="ongoing">Em andamento</flux:select.option><flux:select.option value="completed">Concluído</flux:select.option></flux:select><flux:input wire:model="project_cost" label="Investimento (opcional)" type="number" min="0" step="0.01" max="9999999999.99" /><flux:textarea class="sm:col-span-2" wire:model="project_description" label="História e objetivo (opcional)" placeholder="O que você quer registrar neste projeto?" maxlength="5000" /><flux:textarea wire:model="project_parts" label="Peças e modificações (opcional)" maxlength="2000" /><flux:textarea wire:model="project_services" label="Serviços e próximos passos (opcional)" maxlength="2000" /><div class="flex flex-wrap gap-4 sm:col-span-2 md:gap-5"><flux:checkbox wire:model="project_show_cost" label="Exibir o investimento publicamente" /><flux:checkbox wire:model="project_is_public" label="Publicar projeto (o carro também precisa estar público)" /></div></div></fieldset>

                <fieldset class="space-y-4 border-t border-[var(--mc-line)] pt-6 md:space-y-5 md:pt-7"><legend class="text-lg font-semibold">Fotos do carro</legend><p class="mc-muted">Opcional agora. Você pode organizar tudo depois na biblioteca do veículo.</p><x-photo-uploader wire-model="photos" input-id="new-vehicle-photos" label="Escolher fotos" hint="Arraste aqui ou abra a galeria. JPG, PNG ou WebP · até 5 MB por foto." max-files="{{ config('motorclub.photo_max_files', 12) }}" capture="environment" /><p wire:loading wire:target="photos" class="text-sm mc-muted">Preparando fotos…</p>@error('photos')<p class="text-sm text-red-600" role="alert">{{ $message }}</p>@enderror</fieldset>
            @endif

            <div class="flex flex-wrap gap-3 border-t border-[var(--mc-line)] pt-6 md:pt-7"><flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save,photos">{{ $editingId ? 'Salvar alterações' : 'Criar meu veículo' }}</flux:button><flux:button wire:click="resetForm" type="button">Cancelar</flux:button></div>
        </form>
    @endif
    @if($createdVehicleId)
        <section class="rounded-2xl border border-[var(--mc-orange)] bg-[color:var(--mc-orange)]/10 p-5" role="status"><h2 class="text-xl font-semibold">Seu veículo está na garagem</h2><p class="mt-1 mc-muted">O projeto principal já está pronto. Continue quando quiser.</p><div class="mt-4 flex flex-wrap gap-3"><flux:button href="{{ route('garage.details', $createdVehicleId) }}" wire:navigate variant="primary">Adicionar fotos e registros</flux:button><flux:button href="{{ route('projects.index', ['mine' => 1]) }}" wire:navigate>Ver projeto</flux:button><flux:button wire:click="$set('createdVehicleId', null)">Agora não</flux:button></div></section>
    @endif
    <section class="mc-collection grid gap-8 lg:grid-cols-2" aria-label="Veículos cadastrados">
        @forelse($vehicles as $vehicle)
            <article wire:key="vehicle-{{ $vehicle->id }}" class="mc-car overflow-hidden rounded-2xl mc-surface shadow-sm">
                <a href="{{ route('garage.details', $vehicle) }}" wire:navigate class="group block overflow-hidden bg-zinc-200 dark:bg-zinc-800">
                    @if($vehicle->photos->first())<img src="{{ route('vehicle-photos.show', $vehicle->photos->first()) }}" alt="{{ $vehicle->brand }} {{ $vehicle->model }}" class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-105" />@else<div class="flex aspect-[16/10] items-center justify-center text-5xl font-black tracking-tighter mc-muted">{{ $vehicle->model }}<span class="text-[var(--mc-orange)]">/</span></div>@endif
                </a>
                <div class="p-6 md:p-8">
                    <p class="text-sm mc-muted">{{ config('motorclub.vehicle_types.'.$vehicle->vehicle_type, 'Veículo') }} · {{ $vehicle->brand }} · {{ $vehicle->model_year }}</p>
                    <h2 class="mt-2 text-4xl font-bold tracking-tight">{{ $vehicle->public_name ?: $vehicle->model }}</h2>
                    @if($vehicle->public_name)<p class="mt-1 text-sm mc-muted">{{ $vehicle->model }}</p>@endif
                    @if($vehicle->trim || $vehicle->engine)<p class="mt-2">{{ collect([$vehicle->trim, $vehicle->engine])->filter()->join(' · ') }}</p>@endif
                    @if($vehicle->color || $vehicle->mileage || $vehicle->location)<p class="mt-2 text-sm mc-muted">{{ $vehicle->color }}{{ $vehicle->mileage ? ' · '.number_format($vehicle->mileage, 0, ',', '.').' km'.($vehicle->mileage_approximate ? ' (aprox.)' : '') : '' }}{{ $vehicle->location ? ' · '.$vehicle->location : '' }}</p>@endif
                    <p class="mt-4 text-sm">{{ $vehicle->visibility === 'public' ? 'Público' : 'Privado' }}</p>
                    <div class="mt-5 flex flex-wrap gap-3"><flux:button href="{{ route('garage.details', $vehicle) }}" wire:navigate>Fotos e histórico</flux:button><flux:button wire:click="edit({{ $vehicle->id }})">Editar</flux:button>@if($vehicle->visibility === 'public')<flux:button href="{{ $vehicle->slug ? route('vehicles.slug.show', ['slug' => $vehicle->slug]) : route('vehicles.show', $vehicle) }}">Ver página pública</flux:button>@endif<flux:button wire:click="delete({{ $vehicle->id }})" wire:confirm="Excluir este veículo permanentemente? Esta ação não pode ser desfeita." variant="danger">Excluir</flux:button></div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-zinc-400 p-10 md:col-span-2"><h2 class="text-xl font-semibold">Sua garagem começa aqui</h2><p class="mt-3 mc-muted">Adicione seu primeiro veículo. Ele será privado por padrão e o projeto principal será criado automaticamente.</p></div>
        @endforelse
    </section>
    {{ $vehicles->links() }}
</div>
