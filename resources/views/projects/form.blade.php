@php
    $photoKinds = ['capa' => 'Capa', 'editorial' => 'Editorial', 'galeria' => 'Galeria', 'interno' => 'Interior', 'externo' => 'Exterior', 'detalhe' => 'Detalhe', 'antes' => 'Antes', 'durante' => 'Durante', 'depois' => 'Depois', 'instalada' => 'Instalada', 'peca' => 'Peça', 'revista' => 'Revista', 'destaque' => 'Destaque'];
    $modificationStatuses = ['planned' => 'Planejada', 'in_progress' => 'Em andamento', 'completed' => 'Concluída'];
    $partStatuses = ['planned' => 'Planejada', 'in_progress' => 'Em andamento', 'installed' => 'Instalada'];
@endphp
<x-layouts.public :title="$project->exists ? 'Editar projeto · Autoverse' : 'Novo projeto · Autoverse'">
    <div class="mx-auto max-w-6xl space-y-10">
        <header>
            <h1 class="mc-title">{{ $project->exists ? 'Editar projeto' : 'Criar projeto' }}</h1>
            <p class="mc-muted">Um carro tem um projeto principal. Dentro dele, cada modificação e peça mantém suas próprias fotos e informações.</p>
        </header>
        @if($vehicles->isEmpty())
            <p>Comece <a class="underline" href="{{ route('dashboard') }}">cadastrando um veículo na garagem</a>.</p>
        @else
            <form class="mc-surface mc-form" method="POST" action="{{ $project->exists ? route('projects.update', $project) : route('projects.store') }}">
                @csrf @if($project->exists) @method('PUT') @endif
                <x-field name="title" label="Nome do projeto" :value="$project->title" maxlength="150" required />
                <x-select-field name="vehicle_id" label="Veículo principal" :value="$project->vehicle_id" :options="$vehicles->mapWithKeys(fn($v)=>[$v->id=>$v->brand.' '.$v->model.' '.$v->model_year])" />
                <div class="grid gap-5 md:grid-cols-2"><x-select-field name="category" label="Categoria" :value="$project->category" :options="config('motorclub.categories')" /><x-select-field name="status" label="Etapa" :value="$project->status" :options="config('motorclub.project_status')" /></div>
                <x-area name="description" label="História e objetivo" :value="$project->description" maxlength="5000" required />
                <p class="mc-muted">Use este campo para a visão geral. Registre cada modificação e peça na seção própria abaixo.</p>
                <x-area name="parts" label="Resumo de peças e modificações" :value="$project->parts" maxlength="2000" />
                <x-area name="services" label="Resumo de serviços e próximos passos" :value="$project->services" maxlength="2000" />
                <x-field name="cost" label="Custo total opcional (R$)" type="number" min="0" step="0.01" :value="$project->cost" />
                <x-check name="show_cost" label="Exibir o custo na página pública" :checked="$project->show_cost" /><x-check name="is_public" label="Publicar projeto (o veículo também precisa estar público)" :checked="$project->is_public" />
                <x-select-field name="workshop_id" label="Oficina participante" :value="$project->workshop_id" :options="[''=>'Sem oficina']+$workshops->pluck('name','id')->all()" />
                <p class="mc-muted">A oficina receberá o título e o veículo para autorizar o vínculo. Só vínculos aprovados aparecem publicamente.</p>
                <button class="mc-link-button">Salvar projeto</button>
            </form>
        @endif

        @if($project->exists)
            <section class="space-y-6">
                <div><h2 class="text-3xl font-bold">Fotos do projeto</h2><p class="mc-muted">Fotos editoriais, de evolução e para a revista. Fotos gerais do veículo ficam na garagem.</p></div>
                <form method="POST" enctype="multipart/form-data" action="{{ route('projects.photo', $project) }}" class="mc-surface mc-form">
                    @csrf
                    <x-photo-uploader name="photos[]" input-id="project-photos" label="Fotos do projeto" hint="JPEG, PNG ou WebP · até 5 MB por foto · máximo de {{ config('motorclub.photo_max_files', 12) }} fotos" max-files="{{ config('motorclub.photo_max_files', 12) }}" required />
                    <div class="grid gap-4 md:grid-cols-3"><x-select-field name="kind" label="Classificação" :options="$photoKinds" /><x-field name="caption" label="Legenda opcional" maxlength="255" /><p class="mc-muted">A primeira foto vira a capa se ainda não houver uma definida.</p></div>
                    <button class="mc-link-button">Enviar fotos do projeto</button>
                </form>
                <div class="grid gap-5 md:grid-cols-3">
                    @foreach($project->photos as $photo)
                        <article class="mc-surface p-4"><img class="h-48 w-full rounded-xl object-cover" src="{{ route('projects.image',$photo) }}" alt="{{ $photo->caption ?: 'Foto do projeto' }}"><form class="mt-3 space-y-2" method="POST" action="{{ route('projects.photo.update',$photo) }}">@csrf @method('PUT')<select class="mc-input" name="kind">@foreach($photoKinds as $value => $label)<option value="{{ $value }}" @selected($photo->kind === $value)>{{ $label }}</option>@endforeach</select><input class="mc-input" name="caption" maxlength="255" value="{{ $photo->caption }}" placeholder="Legenda"><input class="mc-input" name="sort_order" type="number" min="0" value="{{ $photo->sort_order }}" aria-label="Ordem"><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_primary" value="1" @checked($photo->is_primary)> Foto principal</label><button class="underline">Salvar dados</button></form><form class="mt-2" method="POST" action="{{ route('projects.photo.delete',$photo) }}">@csrf @method('DELETE')<button class="underline py-2">Excluir foto</button></form></article>
                    @endforeach
                </div>
            </section>

            <section class="space-y-6">
                <div><h2 class="text-3xl font-bold">Modificações do projeto</h2><p class="mc-muted">Registre o que mudou no carro, acompanhe o andamento e adicione fotos e peças.</p></div>
                <form method="POST" action="{{ route('projects.modifications.store',$project) }}" class="mc-surface mc-form">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-2"><x-field name="title" label="Nome da modificação" maxlength="150" required /><x-field name="category" label="Categoria" maxlength="50" placeholder="Rodas, freios, suspensão…" required /><x-select-field name="status" label="Status" :options="$modificationStatuses" /><x-field name="started_on" label="Início" type="date" /></div>
                    <x-area name="description" label="O que foi feito ou planejado" maxlength="5000" /><label class="flex items-center gap-2"><input type="checkbox" name="is_public" value="1"> Publicar esta modificação</label><button class="mc-link-button">Adicionar modificação</button>
                </form>

                @forelse($project->modifications as $modification)
                    <article class="mc-surface space-y-6 p-6 md:p-8">
                        <form method="POST" action="{{ route('projects.modifications.update',[$project,$modification]) }}" class="space-y-4">@csrf @method('PUT')<div class="grid gap-4 md:grid-cols-3"><input class="mc-input" name="title" value="{{ $modification->title }}" required><input class="mc-input" name="category" value="{{ $modification->category }}" required><select class="mc-input" name="status">@foreach($modificationStatuses as $value => $label)<option value="{{ $value }}" @selected($modification->status === $value)>{{ $label }}</option>@endforeach</select></div><textarea class="mc-input min-h-28" name="description" maxlength="5000">{{ $modification->description }}</textarea><label class="flex items-center gap-2"><input type="checkbox" name="is_public" value="1" @checked($modification->is_public)> Publicar modificação</label><button class="underline">Salvar modificação</button></form>
                        <div><h3 class="text-xl font-semibold">Fotos da modificação</h3><p class="mc-muted">Antes, durante, depois ou resultado instalado. As fotos ficam associadas a esta modificação.</p><form method="POST" enctype="multipart/form-data" action="{{ route('projects.modifications.photos.store',[$project,$modification]) }}" class="mt-3 space-y-3">@csrf<x-photo-uploader name="photos[]" input-id="modification-{{ $modification->id }}-photos" label="Adicionar fotos desta modificação" hint="Selecione várias, revise as miniaturas e salve de uma vez" max-files="12" /><div class="grid gap-3 md:grid-cols-3"><select class="mc-input" name="kind">@foreach($photoKinds as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select><input class="mc-input" name="caption" placeholder="Legenda"><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_public" value="1"> Publicar fotos</label></div><button class="mc-link-button">Salvar fotos da modificação</button></form></div>
                        @if($modification->photos->isNotEmpty())<div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">@foreach($modification->photos as $photo)<figure><img class="aspect-[4/3] w-full rounded-xl object-cover" src="{{ route('projects.media.image',$photo) }}" alt="{{ $photo->caption ?: 'Foto da modificação' }}"><figcaption class="mt-2 text-sm">{{ $photo->caption ?: ucfirst($photo->kind) }}@if($photo->is_primary) · principal @endif</figcaption><form method="POST" action="{{ route('projects.media.destroy',$photo) }}">@csrf @method('DELETE')<button class="underline">Excluir</button></form></figure>@endforeach</div>@endif
                        <div><h3 class="text-xl font-semibold">Peças e componentes</h3><p class="mc-muted">Cadastre roda, pneu, pastilha, farol ou qualquer componente separadamente.</p><form method="POST" action="{{ route('projects.parts.store',[$project,$modification]) }}" class="mt-3 grid gap-3 md:grid-cols-4">@csrf<input class="mc-input" name="name" placeholder="Nome da peça" required><input class="mc-input" name="brand" placeholder="Marca"><input class="mc-input" name="model" placeholder="Modelo"><select class="mc-input" name="status">@foreach($partStatuses as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select><textarea class="mc-input md:col-span-3" name="description" placeholder="Detalhes técnicos"></textarea><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_public" value="1"> Publicar peça</label><button class="mc-link-button md:col-span-2">Adicionar peça</button></form></div>
                        @foreach($modification->parts as $part)
                            <div class="rounded-xl border border-[var(--mc-line)] p-4"><form method="POST" action="{{ route('projects.parts.update',[$project,$modification,$part]) }}" class="grid gap-3 md:grid-cols-4">@csrf @method('PUT')<input class="mc-input" name="name" value="{{ $part->name }}" required><input class="mc-input" name="brand" value="{{ $part->brand }}" placeholder="Marca"><input class="mc-input" name="model" value="{{ $part->model }}" placeholder="Modelo"><select class="mc-input" name="status">@foreach($partStatuses as $value => $label)<option value="{{ $value }}" @selected($part->status === $value)>{{ $label }}</option>@endforeach</select><textarea class="mc-input md:col-span-3" name="description">{{ $part->description }}</textarea><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_public" value="1" @checked($part->is_public)> Publicar peça</label><button class="underline">Salvar peça</button></form><form method="POST" enctype="multipart/form-data" action="{{ route('projects.parts.photos.store',[$project,$modification,$part]) }}" class="mt-4 grid gap-3 md:grid-cols-3">@csrf<x-photo-uploader name="photos[]" input-id="part-{{ $part->id }}-photos" label="Fotos desta peça" hint="Produto, detalhe, instalação ou peça instalada" max-files="12" /><select class="mc-input" name="kind"><option value="peca">Peça</option><option value="detalhe">Detalhe</option><option value="instalada">Instalada</option><option value="antes">Antes</option><option value="depois">Depois</option></select><input class="mc-input" name="caption" placeholder="Legenda"><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_public" value="1"> Publicar fotos</label><button class="mc-link-button">Enviar fotos da peça</button></form>@if($part->photos->isNotEmpty())<div class="mt-4 grid gap-3 sm:grid-cols-3">@foreach($part->photos as $photo)<figure><img class="aspect-square w-full rounded-lg object-cover" src="{{ route('projects.media.image',$photo) }}" alt="{{ $photo->caption ?: 'Foto da peça' }}"><figcaption class="text-sm">{{ $photo->caption ?: ucfirst($photo->kind) }}</figcaption><form method="POST" action="{{ route('projects.media.destroy',$photo) }}">@csrf @method('DELETE')<button class="underline">Excluir</button></form></figure>@endforeach</div>@endif<form class="mt-3" method="POST" action="{{ route('projects.parts.destroy',[$project,$modification,$part]) }}">@csrf @method('DELETE')<button class="underline">Excluir peça</button></form></div>
                        @endforeach
                        <form method="POST" action="{{ route('projects.modifications.destroy',[$project,$modification]) }}">@csrf @method('DELETE')<button class="underline">Excluir modificação</button></form>
                    </article>
                @empty
                    <p class="mc-muted">Nenhuma modificação cadastrada ainda.</p>
                @endforelse
            </section>
            <div class="flex flex-wrap gap-3"><a class="mc-link-button" href="{{ route('projects.show',$project) }}">Ver projeto</a><form method="POST" action="{{ route('projects.destroy',$project) }}" onsubmit="return confirm('Excluir este projeto e suas fotos?')">@csrf @method('DELETE')<button class="underline">Excluir projeto</button></form></div>
        @endif
    </div>
</x-layouts.public>
