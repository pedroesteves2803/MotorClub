<x-layouts.public :title="$garage['name'].' — Garagem de demonstração — Autoverse'" :noindex="true">
    <a href="{{ route('community') }}" class="mc-text-link">← Voltar à comunidade</a>
    <header class="mc-intro"><p class="mc-edition mb-5">Garagem de demonstração · Não oficial</p><h1>{{ $garage['name'] }}<br><span>Dentro da garagem.</span></h1><p class="mt-6 max-w-xl text-xl">{{ $garage['description'] }}</p></header>
    <p role="note" class="border-l-4 border-orange-600 mc-notice p-4 text-sm">Prévia criada pelo Autoverse, sem participação ou vínculo com o criador. Os modelos foram associados a ele em fontes públicas; esta página não confirma sua garagem atual. Fotos reais das publicações indicadas. As referências públicas abaixo têm fontes; dados particulares não confirmados foram omitidos.</p>
    <section class="grid gap-8 md:grid-cols-2" aria-label="Carros da garagem">
        @foreach($garage['cars'] as $car)
            <article class="overflow-hidden border border-zinc-300 mc-surface">@if($car['image'])<img src="{{ asset('images/demo/'.$car['image']) }}" alt="Foto real de {{ $car['brand'] }} {{ $car['model'] }}" class="aspect-[3/2] w-full object-contain bg-zinc-100" />@else<div class="flex aspect-[3/2] items-center justify-center bg-[#223d32] p-8 text-xl text-white">Foto do carro pendente de confirmação</div>@endif<div class="space-y-3 p-6"><p class="mc-edition">{{ $car['brand'] }}</p><h2 class="text-3xl font-bold">{{ $car['model'] }}</h2><p class="text-sm">Referências públicas disponíveis abaixo, com identificação da versão e da fonte.</p>@if($car['post'])<a href="{{ $car['post'] }}" target="_blank" rel="noopener noreferrer" class="mc-text-link text-sm">Foto: {{ $car['credit'] }} · Ver publicação ↗</a>@endif</div></article>
        @endforeach
    </section>
    @foreach($garage['cars'] as $car)
        <x-verified-car :details="config('verified_cars.'.$car['demo_key'])" />
    @endforeach
    <p class="text-sm">Foto do perfil: <a href="{{ $garage['instagram'] }}" target="_blank" rel="noopener noreferrer" class="mc-text-link">Instagram de {{ $garage['name'] }} ↗</a></p>
    <p class="text-sm">Referência dos modelos: <a href="{{ $garage['source_url'] }}" target="_blank" rel="noopener noreferrer" class="mc-text-link">{{ $garage['source_label'] }} ↗</a></p>
</x-layouts.public>
