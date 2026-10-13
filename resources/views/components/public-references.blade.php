@props(['items','type'])
@if($items->isNotEmpty())
<section class="space-y-6" aria-label="Referências públicas reais">
<h2 class="text-3xl font-bold">{{ $type==='events' ? 'Eventos reais anunciados' : 'Oficinas reais para conhecer' }}</h2>
<p class="mc-muted">{{ $type==='events' ? 'Datas divulgadas pelos organizadores. Confira a programação na fonte antes de planejar a visita.' : 'Referências de sites oficiais, sem vínculo ou conta administrada pela empresa no MotorClub. Pedidos de teste não são enviados a essas oficinas.' }}</p>
<div class="grid gap-6 md:grid-cols-2">@foreach($items as $item)<article class="mc-surface mc-form"><p class="mc-edition">Dados públicos · {{ $item['city'] }} / {{ $item['state'] }}</p><h3 class="text-2xl font-bold">{{ $item['title'] }}</h3>@if(isset($item['date_label']))<p class="font-semibold">{{ $item['date_label'] }}</p>@endif<p>{{ $item['description'] }}</p><a class="mc-text-link" href="{{ $item['source_url'] }}" target="_blank" rel="noopener noreferrer">{{ $item['source_label'] }} ↗</a><p class="mc-muted text-sm">Consultado em {{ $item['checked_at'] }}</p></article>@endforeach</div>
</section>
@endif
