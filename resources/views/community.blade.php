<x-layouts.public title="Conheça a comunidade — Autoverse" :noindex="true">
    <header class="mc-intro"><p class="mc-edition mb-5">Pessoas diferentes. A mesma paixão.</p><h1>Uma garagem.<br><span>Muitas histórias.</span></h1><p class="mt-6 max-w-xl text-lg">Clássicos, esportivos e carros do dia a dia. Conheça as histórias e os projetos de quem compartilha a cultura automotiva na Autoverse.</p></header>
    <p role="note" class="mc-notice border-l-2 border-[var(--mc-action)] p-4 text-sm"><strong>Ambiente de demonstração.</strong> Perfis fictícios e garagens demonstrativas inspiradas em criadores reais. Os criadores não possuem contas oficiais nesta prévia. Fotos dos criadores têm crédito da publicação original; os demais carros fictícios usam imagens ilustrativas.</p>
    <form method="GET" class="flex gap-3"><input class="mc-input" name="q" value="{{ request('q') }}" placeholder="Pessoa, marca ou modelo" aria-label="Buscar na comunidade"><button class="mc-link-button">Buscar</button></form>
    @if($people->isEmpty())<p>Nenhum perfil encontrado.</p>@endif
    <section class="grid items-start gap-6 sm:grid-cols-2 lg:grid-cols-3" aria-label="Perfis da comunidade">
        @foreach($people as $person)
            <article class="overflow-hidden border border-zinc-300 mc-surface ">
                <div class="flex items-center gap-3 p-5">@if(!empty($person['avatar']))<img src="{{ $person['avatar'] }}" alt="Foto pública de {{ $person['name'] }} no Instagram" class="h-12 w-12 shrink-0 rounded-full object-cover" />@else<span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#223d32] font-bold text-white">{{ mb_substr($person['name'],0,1) }}</span>@endif<div><h2 class="font-bold">@if(!empty($person['url']))<a href="{{ $person['url'] }}">{{ $person['name'] }}</a>@else{{ $person['name'] }}@endif</h2><p class="text-xs mc-muted">{{ $person['label'] }}</p></div></div>
                @foreach($person['vehicles'] as $vehicle)
                    <a href="{{ $vehicle['url'] }}">
                        @if($vehicle['image'])
                            <img src="{{ $vehicle['image'] }}" alt="{{ !empty($vehicle['post']) ? 'Foto real de' : 'Imagem ilustrativa de' }} {{ $vehicle['name'] }}" loading="lazy" class="aspect-[3/2] w-full {{ !empty($vehicle['post']) ? 'object-contain bg-zinc-900' : 'object-cover' }}" />
                        @else
                            <div class="flex min-h-48 items-center bg-[#223d32] p-6 text-4xl font-bold italic text-[#f2f0e9]">{{ $vehicle['name'] }}</div>
                        @endif
                    </a>
                    <div class="space-y-4 p-5"><h3 class="font-semibold">{{ $vehicle['name'] }}</h3><p class="text-sm mc-muted">{{ $vehicle['note'] }}</p>@if(!empty($vehicle['post']))<a href="{{ $vehicle['post'] }}" target="_blank" rel="noopener noreferrer" class="block text-xs underline">Foto: {{ $vehicle['credit'] }}</a>@endif<a href="{{ $vehicle['url'] }}" class="mc-text-link w-fit">Conhecer o carro</a></div>
                @endforeach
            </article>
        @endforeach
    </section>
</x-layouts.public>
