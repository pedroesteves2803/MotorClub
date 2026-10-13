<?php

use App\Models\BillingAccount;
use App\Models\MockSubscription;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    config(['billing.driver' => 'mock', 'production.test_mode' => true]);
    Http::preventStrayRequests();
});

function mockOwner(): User
{
    $user = User::factory()->create();
    Workshop::create(['user_id' => $user->id, 'name' => 'Oficina fictícia', 'city' => 'São Paulo', 'state' => 'SP', 'specialties' => 'Motores', 'description' => 'Somente testes', 'is_public' => true]);

    return $user;
}

function mockAction(string $action, ?string $id = null): array
{
    return ['action' => $action, 'request_id' => $id ?? (string) Str::uuid()];
}

it('permite contratação recusa recuperação renovação e cancelamento sem API externa', function () {
    $this->actingAs(mockOwner())->get('/plano-oficina')->assertOk()->assertSee('Não precisamos de cartão');
    foreach (['checkout' => 'pending', 'decline' => 'past_due', 'approve' => 'active', 'fail' => 'past_due'] as $action => $status) {
        $this->post('/assinatura/simular', mockAction($action))->assertSessionHasNoErrors();
        expect(MockSubscription::sole()->status)->toBe($status);
    }
    $this->post('/assinatura/simular', mockAction('approve'))->assertSessionHasNoErrors();
    $end = MockSubscription::sole()->period_end;
    $this->post('/assinatura/simular', mockAction('renew'))->assertSessionHasNoErrors();
    expect(MockSubscription::sole()->period_end->greaterThan($end))->toBeTrue();
    $this->post('/assinatura/simular', mockAction('schedule_cancel'))->assertSessionHasNoErrors();
    expect(MockSubscription::sole()->cancel_at_period_end)->toBeTrue();
    $this->post('/assinatura/simular', mockAction('resume'))->assertSessionHasNoErrors();
    expect(MockSubscription::sole()->cancel_at_period_end)->toBeFalse();
    $this->post('/assinatura/simular', mockAction('schedule_cancel'));
    $this->post('/assinatura/simular', mockAction('end_period'))->assertSessionHasNoErrors();
    expect(MockSubscription::sole()->status)->toBe('canceled');
    expect(BillingAccount::count())->toBe(0);
    Http::assertNothingSent();
});

it('estorna uma vez e permite nova contratação sem apagar histórico', function () {
    $this->actingAs(mockOwner());
    foreach (['checkout', 'approve', 'refund'] as $action) {
        $this->post('/assinatura/simular', mockAction($action))->assertSessionHasNoErrors();
    }
    expect(DB::table('mock_billing_events')->sum('amount'))->toBe(0);
    $this->post('/assinatura/simular', mockAction('refund'))->assertSessionHasErrors('billing');
    $this->post('/assinatura/simular', mockAction('checkout'))->assertSessionHasNoErrors();
    expect(DB::table('mock_billing_events')->count())->toBe(4);
});

it('impede duplicação no reenvio do formulário e ignora valores adulterados', function () {
    $this->actingAs(mockOwner());
    $this->post('/assinatura/simular', mockAction('checkout'));
    $data = mockAction('approve') + ['amount' => 1, 'user_id' => 999];
    $this->post('/assinatura/simular', $data)->assertSessionHasNoErrors();
    $this->post('/assinatura/simular', $data)->assertSessionHasNoErrors();
    expect(DB::table('mock_billing_events')->count())->toBe(2);
    expect((int) DB::table('mock_billing_events')->sum('amount'))->toBe(9900);
});

it('exige autenticação e oficina própria e não altera outra assinatura', function () {
    $this->post('/assinatura/simular', mockAction('checkout'))->assertRedirect('/login');
    $this->actingAs(User::factory()->create())->post('/assinatura/simular', mockAction('checkout'))->assertForbidden();
    $first = mockOwner();
    $this->actingAs($first)->post('/assinatura/simular', mockAction('checkout'));
    $this->actingAs(mockOwner())->post('/assinatura/simular', mockAction('approve') + ['user_id' => $first->id])->assertSessionHasErrors('billing');
    expect(MockSubscription::where('user_id', $first->id)->sole()->status)->toBe('pending');
    $this->get('/plano-oficina')->assertDontSee('Checkout iniciado');
});

it('bloqueia mock em produção e Stripe quando mock está selecionado', function () {
    $this->actingAs(mockOwner())->post('/assinatura/checkout', ['consent' => 1])->assertNotFound();
    $this->post('/assinatura/portal')->assertNotFound();
    app()->instance('env', 'production');
    $this->withSession(['_token' => 'csrf-test'])->post('/assinatura/simular', mockAction('checkout') + ['_token' => 'csrf-test'])->assertNotFound();
    Http::assertNothingSent();
});
