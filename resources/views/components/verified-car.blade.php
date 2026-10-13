@props(['details'])
<section class="space-y-6" aria-label="Dados públicos verificados">
<p class="mc-edition">Referência pública · Consultada em {{ $details['checked_at'] }}</p>
<h2 class="text-3xl font-bold">{{ $details['title'] }}</h2><p class="max-w-3xl">{{ $details['description'] }}</p>
<dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">@foreach($details['specs'] as $label=>$value)<div class="mc-surface rounded-xl p-5"><dt class="mc-muted text-sm">{{ $label }}</dt><dd class="mt-2 font-semibold">{{ $value }}</dd></div>@endforeach</dl>
<a class="mc-text-link" href="{{ $details['source_url'] }}" target="_blank" rel="noopener noreferrer">Fonte: {{ $details['source_label'] }} ↗</a>
<p class="mc-notice rounded-xl p-5">Quilometragem, gastos, manutenção, modificações e propriedade atual não foram verificados. Dados de fábrica não comprovam a configuração de um veículo específico.</p>
</section>