@props(['details'])
<section class="space-y-8" aria-label="Informações simuladas do carro">
    <div class="border-l-4 border-orange-600 mc-notice p-4 text-sm"><strong>Dados simulados para teste.</strong> Ano, ficha, localização, modificações, gastos e histórico abaixo são fictícios. Não descrevem os veículos ou a rotina dos criadores reais.</div>
    <div><p class="mc-edition mb-3">{{ $details['category'] }}</p><h2 class="text-3xl font-bold">{{ $details['title'] }}</h2><p class="mt-4 max-w-2xl text-lg">{{ $details['description'] }}</p></div>
    <div><h3 class="mb-4 text-xl font-bold">Ficha do projeto</h3><dl class="grid grid-cols-2 gap-px overflow-hidden border border-zinc-300 bg-zinc-300 lg:grid-cols-4">@foreach($details['specs'] as $label => $value)<div class="mc-surface p-5"><dt class="text-sm mc-muted">{{ $label }}</dt><dd class="mt-2 font-semibold">{{ $value }}</dd></div>@endforeach</dl></div>
    <div><h3 class="mb-4 text-xl font-bold">Modificações e cuidados</h3><ul class="flex flex-wrap gap-3">@foreach($details['mods'] as $mod)<li class="border border-zinc-300 mc-surface px-4 py-3 text-sm">{{ $mod }}</li>@endforeach</ul></div>
    <div><h3 class="mb-5 text-xl font-bold">Diário da garagem <span class="text-sm font-normal">· Simulação</span></h3><ol class="space-y-6 border-l border-zinc-400 pl-6">@foreach($details['history'] as $entry)<li><p class="mc-edition">{{ $entry['date'] }}</p><h4 class="mt-2 text-lg font-bold">{{ $entry['title'] }}</h4><p class="mt-2 max-w-2xl">{{ $entry['description'] }}</p><p class="mt-2 text-sm mc-muted">Custo fictício: {{ $entry['cost'] }}</p></li>@endforeach</ol></div>
    <div class="bg-[#223d32] p-6 text-white"><h3 class="text-xl font-bold">Próximos passos do projeto</h3><p class="mt-3">{{ $details['next'] }}</p></div>
</section>
