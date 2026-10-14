<div class="mx-auto max-w-6xl space-y-10">
    <a href="{{ route('dashboard') }}" wire:navigate class="underline">Voltar à garagem</a>

    <header class="mc-intro">
        <p class="mc-edition mb-4">O diário do seu carro</p>
        <h1 class="text-5xl font-bold tracking-tight md:text-7xl">{{ $vehicle->public_name ?: $vehicle->brand.' '.$vehicle->model }}</h1>
        <p>{{ collect([$vehicle->brand.' '.$vehicle->model, $vehicle->trim, $vehicle->model_year, $vehicle->engine])->filter()->implode(' · ') }}</p>
        @if($vehicle->color || $vehicle->mileage || $vehicle->location)
            <p class="mc-muted">{{ $vehicle->color }}{{ $vehicle->mileage ? ' · '.number_format($vehicle->mileage, 0, ',', '.').' km'.($vehicle->mileage_approximate ? ' (aprox.)' : '') : '' }}{{ $vehicle->location ? ' · '.$vehicle->location : '' }}</p>
        @endif
        @if($vehicle->instagram_url)
            <a class="mc-text-link" href="{{ $vehicle->instagram_url }}" target="_blank" rel="noopener noreferrer">Instagram do carro ↗</a>
        @endif
    </header>

    <nav class="mc-section-tabs" aria-label="Seções da garagem" data-section-tabs>
        <a href="#fotos" data-section-tab>Fotos</a>
        @if($project)<a href="{{ route('projects.edit', $project) }}">Projeto</a>@endif
        <a href="{{ route('garage.magazine.edit', $vehicle) }}">Revista</a>
        <a href="#historico" data-section-tab>Histórico</a>
    </nav>

    @if(session('status'))<p role="status" class="mc-notice">{{ session('status') }}</p>@endif

    <section id="fotos" class="space-y-6 rounded-2xl mc-surface p-5 shadow-sm md:p-8">
        <div>
            <p class="mc-edition">Biblioteca do veículo</p>
            <h2 class="mt-2 text-2xl font-semibold">Fotos</h2>
            <p class="mt-2 mc-muted">Adicione várias, organize e use as mesmas fotos no projeto.</p>
        </div>

        <form wire:submit="uploadPhoto" class="space-y-5" aria-labelledby="photo-upload-title">
            <h3 id="photo-upload-title" class="sr-only">Adicionar fotos</h3>
            <x-photo-uploader
                wire-model="photos"
                input-id="vehicle-photos"
                label="Escolha suas fotos"
                hint="Arraste aqui ou abra a galeria. JPG, PNG ou WebP · até {{ number_format(config('motorclub.photo_max_upload_kb', 5120) / 1024, 0) }} MB por foto."
                max-files="{{ config('motorclub.photo_max_files', 12) }}"
                capture="environment"
            />

            <div class="grid gap-4 md:grid-cols-2">
                <flux:select wire:model.live="photo_context" label="Guardar em">
                    <flux:select.option value="vehicle">Biblioteca do veículo</flux:select.option>
                    <flux:select.option value="project">Projeto principal</flux:select.option>
                    <flux:select.option value="new_modification">Nova modificação</flux:select.option>
                    <flux:select.option value="existing_modification">Modificação existente</flux:select.option>
                    <flux:select.option value="existing_part">Peça existente</flux:select.option>
                    <flux:select.option value="maintenance">Registro de manutenção</flux:select.option>
                </flux:select>
                <flux:select wire:model="photo_kind" label="Como usar as fotos">
                    <flux:select.option value="capa">Capa</flux:select.option>
                    <flux:select.option value="galeria">Galeria</flux:select.option>
                    <flux:select.option value="interno">Interior</flux:select.option>
                    <flux:select.option value="externo">Exterior</flux:select.option>
                    <flux:select.option value="evento">Evento</flux:select.option>
                    <flux:select.option value="detalhe">Detalhe</flux:select.option>
                    <flux:select.option value="antes">Antes</flux:select.option>
                    <flux:select.option value="durante">Durante</flux:select.option>
                    <flux:select.option value="depois">Depois</flux:select.option>
                </flux:select>
            </div>

            @if(in_array($photo_context, ['new_modification', 'maintenance'], true))
                <flux:input wire:model="photo_context_title" label="{{ $photo_context === 'maintenance' ? 'Nome da manutenção' : 'Nome da modificação' }}" placeholder="{{ $photo_context === 'maintenance' ? 'Ex.: Troca de óleo' : 'Ex.: Rodas novas' }}" required maxlength="150" />
            @endif
            @if($photo_context === 'maintenance')
                <flux:textarea wire:model="photo_context_description" label="Nota (opcional)" placeholder="O que foi feito?" maxlength="5000" />
            @endif
            @if($photo_context === 'existing_modification')
                <flux:select wire:model="photo_modification_id" label="Modificação">
                    <flux:select.option value="">Escolha uma modificação</flux:select.option>
                    @foreach($modifications as $modification)<flux:select.option value="{{ $modification->id }}">{{ $modification->title }}</flux:select.option>@endforeach
                </flux:select>
            @endif
            @if($photo_context === 'existing_part')
                <flux:select wire:model="photo_part_id" label="Peça">
                    <flux:select.option value="">Escolha uma peça</flux:select.option>
                    @foreach($modifications as $modification)
                        @foreach($modification->parts as $part)
                            <flux:select.option value="{{ $part->id }}">{{ $modification->title }} · {{ $part->name }}</flux:select.option>
                        @endforeach
                    @endforeach
                </flux:select>
            @endif

            @error('photo_context_title')<p class="text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
            @error('photo_modification_id')<p class="text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
            @error('photo_part_id')<p class="text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
            @error('photos')<p class="text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
            @foreach($errors->get('photos.*') as $messages)
                @foreach((array) $messages as $message)<p class="text-sm text-red-600" role="alert">{{ $message }}</p>@endforeach
            @endforeach
            <p wire:loading wire:target="photos" class="text-sm mc-muted" role="status">Preparando fotos…</p>
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="photos,uploadPhoto">Salvar fotos</flux:button>
        </form>

        @if($photo_context !== 'vehicle')
            <div class="rounded-xl border border-dashed border-[var(--mc-line)] p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="font-semibold">Reutilizar da biblioteca</h3>
                        <p class="mt-1 text-sm mc-muted">Escolha fotos que já estão neste veículo.</p>
                    </div>
                    <flux:button type="button" wire:click="reuseLibraryPhotos" wire:loading.attr="disabled" wire:target="reuseLibraryPhotos">Usar selecionadas</flux:button>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4 md:grid-cols-6">
                    @foreach($vehiclePhotos as $item)
                        <label class="group cursor-pointer">
                            <input type="checkbox" class="peer sr-only" wire:model.live="reuse_photo_ids" value="{{ $item->id }}">
                            <span class="block rounded-lg border border-[var(--mc-line)] p-1 transition peer-checked:border-[var(--mc-orange)] peer-checked:ring-2 peer-checked:ring-[var(--mc-orange)]">
                                <img src="{{ route('vehicle-photos.show', $item) }}" alt="Selecionar foto de {{ $vehicle->model }}" class="aspect-square w-full rounded-md object-cover" loading="lazy">
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('reuse_photo_ids')<p class="mt-3 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
            </div>
        @endif

        <div>
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h3 class="text-xl font-semibold">Sua biblioteca</h3>
                    <p class="mt-1 text-sm mc-muted">Arraste para ordenar. A capa aparece primeiro.</p>
                </div>
                <span class="text-sm mc-muted">{{ $vehiclePhotos->count() }} / {{ config('motorclub.photo_max_files', 12) }} fotos</span>
            </div>
            @if($vehiclePhotos->isNotEmpty())
                <div data-photo-library class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($vehiclePhotos as $item)
                        <article wire:key="photo-{{ $item->id }}" data-photo-card data-photo-id="{{ $item->id }}" draggable="true" class="rounded-xl border border-[var(--mc-line)] bg-[var(--mc-surface-strong)] p-2">
                            <img src="{{ route('vehicle-photos.show', $item) }}" alt="Foto de {{ $vehicle->model }}" class="aspect-[4/3] w-full rounded-lg object-cover" loading="lazy">
                            <div class="flex items-center justify-between gap-2 px-1 pt-2">
                                <span class="text-sm">{{ $item->is_primary ? 'Capa' : ucfirst($item->kind ?? 'galeria') }}</span>
                                <span class="text-xs mc-muted">arraste</span>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @if(! $item->is_primary)<flux:button type="button" wire:click="setPrimaryPhoto({{ $item->id }})">Definir capa</flux:button>@else<span class="rounded-full bg-[var(--mc-orange)]/15 px-3 py-1 text-sm">Capa atual</span>@endif
                                <flux:button type="button" wire:click="movePhoto({{ $item->id }}, 'up')" aria-label="Mover foto para cima">↑</flux:button>
                                <flux:button type="button" wire:click="movePhoto({{ $item->id }}, 'down')" aria-label="Mover foto para baixo">↓</flux:button>
                                <flux:button type="button" wire:click="deletePhoto({{ $item->id }})" wire:confirm="Excluir esta foto permanentemente?">Excluir</flux:button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="mt-4 rounded-xl border border-dashed border-[var(--mc-line)] p-8 text-center mc-muted">Sua biblioteca está vazia. Escolha algumas fotos acima.</p>
            @endif
        </div>
    </section>

    @if($shotRequests->isNotEmpty())
        <section class="space-y-5 rounded-2xl mc-surface p-6 shadow-sm md:p-8">
            <h2 class="text-2xl font-semibold">Fotos adquiridas no Shots</h2>
            <p>Prévia autorizada para sua garagem.</p>
            <div class="mc-media-grid grid gap-5 sm:grid-cols-2">
                @foreach($shotRequests as $request)
                    <figure><img class="aspect-[4/3] w-full rounded-xl object-cover" src="{{ route('shots.photos.preview',$request->photo) }}" alt="Foto adquirida de {{ $vehicle->model }}"><figcaption class="mt-2">{{ $request->photo->gallery->title }} · {{ $request->photo->license }}</figcaption></figure>
                @endforeach
            </div>
        </section>
    @endif

    <section class="space-y-5 rounded-2xl mc-surface p-6 shadow-sm md:p-8">
        <div><h2 class="text-2xl font-semibold">Registrar uma modificação</h2><p class="mt-1 mc-muted">Comece pelo nome e pelo status. Fotos podem entrar agora ou depois.</p></div>
        <form wire:submit="saveQuickModification" class="grid gap-4 sm:grid-cols-2">
            <flux:input wire:model="quick_modification_title" label="O que mudou?" placeholder="Rodas forjadas" required maxlength="150" />
            <flux:select wire:model="quick_modification_status" label="Status"><flux:select.option value="planned">Planejada</flux:select.option><flux:select.option value="in_progress">Em andamento</flux:select.option><flux:select.option value="completed">Concluída</flux:select.option></flux:select>
            <div class="sm:col-span-2"><x-photo-uploader wire-model="quick_modification_photos" input-id="quick-modification-photos" label="Fotos da modificação (opcional)" hint="Você pode adicionar até 12 fotos agora ou mais tarde" max-files="{{ config('motorclub.photo_max_files', 12) }}" /></div>
            <div class="sm:col-span-2"><flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="saveQuickModification,quick_modification_photos">Salvar modificação</flux:button></div>
        </form>
    </section>

    <section class="space-y-5 rounded-2xl mc-surface p-6 shadow-sm md:p-8">
        <div><h2 class="text-2xl font-semibold">Adicionar uma peça</h2><p class="mt-1 mc-muted">Registre o essencial. Fotos podem ser reutilizadas pela biblioteca acima.</p></div>
        @if($modifications->isNotEmpty())
            <form wire:submit="saveQuickPart" class="grid gap-4 sm:grid-cols-2">
                <flux:select wire:model="quick_part_modification_id" label="Modificação"><flux:select.option value="">Escolha uma modificação</flux:select.option>@foreach($modifications as $modification)<flux:select.option value="{{ $modification->id }}">{{ $modification->title }}</flux:select.option>@endforeach</flux:select>
                <flux:input wire:model="quick_part_name" label="Nome da peça" placeholder="Pastilha cerâmica" required maxlength="150" />
                <flux:select wire:model="quick_part_status" label="Status"><flux:select.option value="planned">Planejada</flux:select.option><flux:select.option value="in_progress">Em andamento</flux:select.option><flux:select.option value="installed">Instalada</flux:select.option></flux:select>
                <flux:input wire:model="quick_part_brand" label="Marca (opcional)" placeholder="Brembo" maxlength="100" />
                <flux:input wire:model="quick_part_model" label="Modelo (opcional)" placeholder="GT" maxlength="120" />
                <div class="sm:col-span-2">@error('quick_part_modification_id')<p class="text-sm text-red-600" role="alert">{{ $message }}</p>@enderror</div>
                <div class="sm:col-span-2"><flux:button type="submit" variant="primary" wire:loading.attr="disabled">Adicionar peça</flux:button></div>
            </form>
        @else
            <p class="mc-muted">Crie uma modificação primeiro para escolher onde esta peça será registrada.</p>
        @endif
    </section>

    <section class="space-y-5 rounded-2xl mc-surface p-6 shadow-sm md:p-8">
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
            <flux:checkbox wire:model="is_public" label="Publicar este registro na página pública do carro" />
            <div class="flex items-end gap-3"><flux:button type="submit" variant="primary">Salvar registro</flux:button><flux:button wire:click="clearEntry" type="button">Limpar formulário</flux:button></div>
        </form>
    </section>

    <section id="historico" class="space-y-5">
        <h2 class="text-2xl font-semibold">Histórico</h2>
        @forelse($entries as $entry)
            <article wire:key="entry-{{ $entry->id }}" class="space-y-3 rounded-2xl mc-surface p-6 shadow-sm md:p-8">
                <p>{{ $entry->occurred_on?->format('d/m/Y') ?? 'Data não informada' }} · {{ $entry->category === 'maintenance' ? 'Manutenção' : 'Modificação' }}</p>
                <h3 class="text-xl font-semibold">{{ $entry->title }}</h3>
                <p class="whitespace-pre-line">{{ $entry->description }}</p>
                @if($entry->photos->isNotEmpty())<div class="grid gap-3 sm:grid-cols-3">@foreach($entry->photos as $entryPhoto)<img src="{{ route('vehicle-entry-media.show', $entryPhoto) }}" alt="Foto de {{ $entry->title }}" class="aspect-[4/3] w-full rounded-lg object-cover" loading="lazy" />@endforeach</div>@endif
                @if($entry->parts)<p class="whitespace-pre-line">Peças: {{ $entry->parts }}</p>@endif
                @if($entry->services)<p class="whitespace-pre-line">Serviços: {{ $entry->services }}</p>@endif
                @if($entry->cost !== null)<p>Valor: R$ {{ number_format((float) $entry->cost, 2, ',', '.') }}</p>@endif
                <div class="flex flex-wrap gap-2"><flux:button wire:click="editEntry({{ $entry->id }})">Editar registro</flux:button><flux:button wire:click="deleteEntry({{ $entry->id }})" wire:confirm="Excluir este registro permanentemente?">Excluir registro</flux:button></div>
            </article>
        @empty
            <p>Nenhuma manutenção ou modificação registrada.</p>
        @endforelse
        {{ $entries->links() }}
    </section>
</div>
