<x-mail::message>
# Suas fotos estão liberadas

O pagamento simulado do pedido #{{ $payment->id }} foi aprovado.

@foreach($payment->grants as $grant)
<x-mail::button :url="route('shots.photos.download', $grant->shot_photo_id)">
Baixar foto
</x-mail::button>
@endforeach

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
