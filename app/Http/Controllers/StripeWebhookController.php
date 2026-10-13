<?php

namespace App\Http\Controllers;

use App\Services\StripeBilling;
use Illuminate\Http\Request;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeBilling $stripe)
    {
        abort_unless($stripe->configured(), 503);
        try {
            $event = Webhook::constructEvent($request->getContent(), $request->header('Stripe-Signature', ''), config('billing.webhook_secret'), 300);
        } catch (\Throwable) {
            return response()->json(['erro' => 'Assinatura inválida.'], 400);
        }
        if ($event->livemode !== false) {
            return response()->json(['erro' => 'Pagamentos reais desativados.'], 400);
        }
        if (in_array($event->type, ['customer.subscription.created', 'customer.subscription.updated', 'customer.subscription.deleted'], true)) {
            try {
                $stripe->sync($event->data->object->toArray());
            } catch (\Throwable) {
                // 503 solicita nova entrega; nunca confirmar processamento que falhou.
                return response()->json(['erro' => 'Tente entregar novamente.'], 503);
            }
        }

        return response()->json(['recebido' => true]);
    }
}
