<?php

namespace App\Services;

use App\Models\BillingAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Uuid;

class StripeBilling
{
    public function configured(): bool
    {
        return config('billing.driver') === 'stripe' && str_starts_with((string) config('billing.secret'), 'sk_test_')
            && str_starts_with((string) config('billing.webhook_secret'), 'whsec_')
            && str_starts_with((string) config('billing.price'), 'price_');
    }

    public function request(string $method, string $path, array $data = [], ?string $key = null): array
    {
        if (! $this->configured()) {
            throw ValidationException::withMessages(['billing' => 'Configure as credenciais de teste da Stripe para continuar. Pagamentos reais estão desativados.']);
        }
        $http = Http::withToken(config('billing.secret'))->acceptJson()->asForm()->connectTimeout(5)->timeout(15);
        if ($key) {
            $http = $http->withHeaders(['Idempotency-Key' => $key]);
        }

        return $http->$method('https://api.stripe.com/v1/'.$path, $data)->throw()->json();
    }

    public function checkout(BillingAccount $account): string
    {
        return DB::transaction(function () use ($account) {
            $account = BillingAccount::lockForUpdate()->findOrFail($account->id);
            if (! in_array($account->status, ['none', 'canceled', 'incomplete_expired', 'pending'])) {
                throw ValidationException::withMessages(['billing' => 'Você já possui uma assinatura. Use o botão de gerenciamento.']);
            }
            if ($account->checkout_id) {
                $previous = $this->request('get', 'checkout/sessions/'.rawurlencode($account->checkout_id));
                if (($previous['status'] ?? null) === 'open') {
                    return $this->safeUrl($previous['url'] ?? '', 'checkout.stripe.com');
                }
                if (($previous['status'] ?? null) === 'complete' && ! in_array($account->status, ['canceled', 'incomplete_expired'])) {
                    throw ValidationException::withMessages(['billing' => 'Seu pagamento está sendo confirmado. Aguarde a atualização do plano.']);
                }
                $account->attempt = (string) Uuid::uuid5(Uuid::NAMESPACE_URL, 'motorclub:'.$account->checkout_id);
                $account->checkout_id = null;
                // A tentativa é determinística para permitir repetição segura mesmo se a transação falhar.
                $account->save();
            }
            $price = $this->request('get', 'prices/'.rawurlencode(config('billing.price')));
            if (($price['livemode'] ?? true) || ! ($price['active'] ?? false) || ($price['unit_amount'] ?? null) !== config('billing.amount') || ($price['currency'] ?? '') !== 'brl' || data_get($price, 'recurring.interval') !== 'month' || data_get($price, 'recurring.interval_count') !== 1) {
                throw ValidationException::withMessages(['billing' => 'O plano Stripe deve ser de teste, R$ 99,00 por mês, em reais.']);
            }
            $data = [
                'mode' => 'subscription', 'locale' => 'pt-BR',
                'payment_method_types' => ['card'],
                'line_items' => [['price' => config('billing.price'), 'quantity' => 1]],
                'client_reference_id' => $account->reference,
                'subscription_data' => ['metadata' => ['motorclub_account' => $account->reference, 'motorclub_attempt' => $account->attempt]],
                'success_url' => route('billing.index', ['retorno' => 'sucesso']),
                'cancel_url' => route('billing.index', ['retorno' => 'cancelado']),
            ];
            if ($account->customer_id) {
                $data['customer'] = $account->customer_id;
            }
            $session = $this->request('post', 'checkout/sessions', $data, 'motorclub-'.$account->attempt);
            if (($session['livemode'] ?? true) || empty($session['id'])) {
                throw new \RuntimeException('Checkout inválido.');
            }
            $url = $this->safeUrl($session['url'] ?? '', 'checkout.stripe.com');
            $account->update(['checkout_id' => $session['id'], 'status' => 'pending']);

            return $url;
        });
    }

    public function portal(BillingAccount $account): string
    {
        abort_unless($account->customer_id, 422, 'Você ainda não possui cadastro de cobrança.');
        $session = $this->request('post', 'billing_portal/sessions', ['customer' => $account->customer_id, 'return_url' => route('billing.index'), 'locale' => 'pt-BR']);

        return $this->safeUrl($session['url'] ?? '', 'billing.stripe.com');
    }

    public function sync(array $object): void
    {
        $reference = data_get($object, 'metadata.motorclub_account');
        if (! is_string($reference) || empty($object['id'])) {
            return;
        }
        DB::transaction(function () use ($reference, $object) {
            $account = BillingAccount::where('reference', $reference)->lockForUpdate()->first();
            if (! $account) {
                return;
            }
            // Reconsulta o estado atual: eventos repetidos ou fora de ordem não reativam plano cancelado.
            $subscription = $this->request('get', 'subscriptions/'.rawurlencode($object['id']));
            if (($subscription['livemode'] ?? true) || data_get($subscription, 'metadata.motorclub_account') !== $account->reference || data_get($subscription, 'metadata.motorclub_attempt') !== $account->attempt || data_get($subscription, 'items.data.0.price.id') !== config('billing.price')) {
                return;
            }
            if ($account->customer_id && $account->customer_id !== ($subscription['customer'] ?? null)) {
                return;
            }
            $end = data_get($subscription, 'items.data.0.current_period_end') ?? ($subscription['current_period_end'] ?? null);
            $account->update([
                'customer_id' => $subscription['customer'], 'subscription_id' => $subscription['id'],
                'status' => $subscription['status'], 'cancel_at_period_end' => $subscription['cancel_at_period_end'] ?? false,
                'period_end' => $end ? Carbon::createFromTimestampUTC($end) : null,
            ]);
        });
    }

    private function safeUrl(string $url, string $host): string
    {
        if (parse_url($url, PHP_URL_SCHEME) !== 'https' || parse_url($url, PHP_URL_HOST) !== $host) {
            throw new \RuntimeException('Endereço de pagamento inválido.');
        }

        return $url;
    }
}
