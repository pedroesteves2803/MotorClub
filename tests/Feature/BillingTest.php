<?php

use App\Models\BillingAccount;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    config(['billing.driver' => 'stripe', 'billing.secret' => 'sk_test_example', 'billing.webhook_secret' => 'whsec_example', 'billing.price' => 'price_test']);
    Http::preventStrayRequests();
});

function billingOwner(): User
{
    $user = User::factory()->create();
    Workshop::create(['user_id' => $user->id, 'name' => 'Oficina teste', 'city' => 'São Paulo', 'state' => 'SP', 'specialties' => 'Motores', 'description' => 'Oficina de homologação', 'is_public' => true]);

    return $user;
}

function billingAccount(User $user): BillingAccount
{
    return BillingAccount::create(['user_id' => $user->id, 'reference' => (string) Str::uuid(), 'attempt' => (string) Str::uuid()]);
}

function billingEvent(array $object, ?int $timestamp = null, bool $live = false): array
{
    $payload = json_encode(['id' => 'evt_test', 'object' => 'event', 'type' => 'customer.subscription.updated', 'livemode' => $live, 'data' => ['object' => $object]]);
    $time = $timestamp ?? time();

    return [$payload, 't='.$time.',v1='.hash_hmac('sha256', $time.'.'.$payload, 'whsec_example')];
}

function billingSubscription(BillingAccount $account, string $status = 'active'): array
{
    return ['id' => 'sub_test', 'object' => 'subscription', 'livemode' => false, 'customer' => 'cus_test', 'status' => $status, 'cancel_at_period_end' => false, 'metadata' => ['motorclub_account' => $account->reference, 'motorclub_attempt' => $account->attempt], 'items' => ['data' => [['price' => ['id' => 'price_test'], 'current_period_end' => time() + 86400]]]];
}

it('mostra o plano e não ativa pelo retorno do checkout', function () {
    $user = billingOwner();
    $account = billingAccount($user);
    $this->actingAs($user)->get('/plano-oficina?retorno=sucesso')->assertOk()->assertSee('notificação da Stripe');
    expect($account->fresh()->status)->toBe('none');
    Http::assertNothingSent();
});

it('exige login oficina e consentimento para checkout', function () {
    $this->post('/assinatura/checkout')->assertRedirect('/login');
    $this->actingAs(User::factory()->create())->post('/assinatura/checkout', ['consent' => 1])->assertForbidden();
    $this->actingAs(billingOwner())->post('/assinatura/checkout')->assertSessionHasErrors('consent');
    Http::assertNothingSent();
});

it('cria checkout de teste com preço controlado e reaproveita sessão aberta', function () {
    Http::fake([
        'api.stripe.com/v1/prices/*' => Http::response(['livemode' => false, 'active' => true, 'unit_amount' => 9900, 'currency' => 'brl', 'recurring' => ['interval' => 'month', 'interval_count' => 1]]),
        'api.stripe.com/v1/checkout/sessions*' => Http::response(['id' => 'cs_test', 'livemode' => false, 'status' => 'open', 'url' => 'https://checkout.stripe.com/c/pay/test']),
    ]);
    $this->actingAs(billingOwner())->post('/assinatura/checkout', ['consent' => 1, 'price' => 'price_forged'])->assertRedirect('https://checkout.stripe.com/c/pay/test');
    $this->post('/assinatura/checkout', ['consent' => 1])->assertRedirect('https://checkout.stripe.com/c/pay/test');
    Http::assertSentCount(3);
    Http::assertSent(fn ($request) => $request->method() === 'POST' && $request['line_items'][0]['price'] === 'price_test' && $request->hasHeader('Idempotency-Key'));
    expect(BillingAccount::sole()->status)->toBe('pending');
});

it('bloqueia credenciais reais sem chamar a API', function () {
    config(['billing.driver' => 'stripe', 'billing.secret' => 'sk_live_not_allowed']);
    $this->actingAs(billingOwner())->post('/assinatura/checkout', ['consent' => 1])->assertSessionHasErrors('billing');
    Http::assertNothingSent();
});

it('recusa preço divergente e esconde erros da API', function () {
    Http::fake(['*' => Http::sequence()->push(['livemode' => false, 'active' => true, 'unit_amount' => 100])->push(['error' => 'segredo-interno'], 500)]);
    $this->actingAs(billingOwner())->post('/assinatura/checkout', ['consent' => 1])->assertSessionHasErrors('billing');
    $this->post('/assinatura/checkout', ['consent' => 1])->assertRedirect('/plano-oficina')->assertSessionHasErrors('billing');
    $this->get('/plano-oficina')->assertDontSee('segredo-interno');
});

it('rejeita assinatura inválida expirada e evento real', function () {
    $this->call('POST', '/stripe/webhook', [], [], [], ['HTTP_STRIPE_SIGNATURE' => 'inválida'], '{}')->assertStatus(400);
    foreach ([[time() - 600, false], [time(), true]] as [$time, $live]) {
        [$body, $signature] = billingEvent(['id' => 'sub_test'], $time, $live);
        $this->call('POST', '/stripe/webhook', [], [], [], ['HTTP_STRIPE_SIGNATURE' => $signature], $body)->assertStatus(400);
    }
    Http::assertNothingSent();
});

it('sincroniza notificações repetidas usando estado atual inclusive cancelamento e inadimplência', function () {
    $account = billingAccount(billingOwner());
    $object = billingSubscription($account);
    [$body, $signature] = billingEvent($object);
    $sequence = Http::sequence();
    foreach (['active', 'past_due', 'canceled', 'canceled'] as $status) {
        $sequence->push(billingSubscription($account, $status));
    }
    Http::fake(['*' => $sequence]);
    foreach (['active', 'past_due', 'canceled', 'canceled'] as $status) {
        $this->call('POST', '/stripe/webhook', [], [], [], ['HTTP_STRIPE_SIGNATURE' => $signature], $body)->assertOk();
        expect($account->fresh()->status)->toBe($status);
    }
});

it('ignora tentativa antiga e solicita reentrega quando a API falha', function () {
    $account = billingAccount(billingOwner());
    $object = billingSubscription($account);
    [$body, $signature] = billingEvent($object);
    $old = $object;
    $old['metadata']['motorclub_attempt'] = 'outra-tentativa';
    Http::fake(['*' => Http::sequence()->push($old)->push([], 500)]);
    $this->call('POST', '/stripe/webhook', [], [], [], ['HTTP_STRIPE_SIGNATURE' => $signature], $body)->assertOk();
    expect($account->fresh()->status)->toBe('none');
    $this->call('POST', '/stripe/webhook', [], [], [], ['HTTP_STRIPE_SIGNATURE' => $signature], $body)->assertStatus(503);
});

it('portal usa apenas o cliente da conta autenticada', function () {
    $owner = billingOwner();
    $account = billingAccount($owner);
    $account->update(['customer_id' => 'cus_owner', 'status' => 'active']);
    Http::fake(['*' => Http::response(['url' => 'https://billing.stripe.com/p/session/test'])]);
    $this->actingAs($owner)->post('/assinatura/portal', ['customer_id' => 'cus_victim'])->assertRedirect('https://billing.stripe.com/p/session/test');
    Http::assertSent(fn ($request) => $request['customer'] === 'cus_owner');
    $this->actingAs(User::factory()->create())->post('/assinatura/portal', ['customer_id' => 'cus_owner'])->assertNotFound();
});

it('permite somente webhook assinado atravessar proteção de homologação', function () {
    app()->instance('env', 'staging');
    $this->get('/plano-oficina')->assertStatus(503);
    $this->call('POST', '/stripe/webhook', [], [], [], [], '{}')->assertStatus(400);
});

it('impede nova contratação quando a assinatura está ativa', function () {
    $owner = billingOwner();
    billingAccount($owner)->update(['status' => 'active']);
    $this->actingAs($owner)->post('/assinatura/checkout', ['consent' => 1])->assertSessionHasErrors('billing');
    Http::assertNothingSent();
});

it('não redireciona para endereço externo inesperado', function () {
    $owner = billingOwner();
    billingAccount($owner)->update(['customer_id' => 'cus_owner']);
    Http::fake(['*' => Http::response(['url' => 'https://site-invalido.example/checkout'])]);
    $this->actingAs($owner)->post('/assinatura/portal')->assertRedirect('/plano-oficina')->assertSessionHasErrors('billing');
});
