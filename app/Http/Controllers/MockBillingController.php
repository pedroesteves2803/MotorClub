<?php

namespace App\Http\Controllers;

use App\Models\MockSubscription;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MockBillingController extends Controller
{
    public const LABELS = [
        'checkout' => 'Checkout iniciado', 'approve' => 'Pagamento aprovado',
        'decline' => 'Pagamento recusado', 'renew' => 'Renovação aprovada',
        'fail' => 'Renovação recusada', 'schedule_cancel' => 'Cancelamento agendado',
        'resume' => 'Cancelamento desfeito', 'end_period' => 'Período encerrado',
        'cancel' => 'Cancelamento imediato', 'refund' => 'Último pagamento estornado',
    ];

    public function show(Request $request)
    {
        abort_unless(MockSubscription::enabled(), 404);
        $subscription = MockSubscription::where('user_id', $request->user()?->id)->first();

        return view('billing.mock', [
            'subscription' => $subscription,
            'workshop' => $request->user() ? Workshop::where('user_id', $request->user()->id)->first() : null,
            'events' => $subscription ? DB::table('mock_billing_events')->where('mock_subscription_id', $subscription->id)->latest('id')->paginate(15) : collect(),
            'labels' => self::LABELS,
        ]);
    }

    public function action(Request $request)
    {
        abort_unless(MockSubscription::enabled(), 404);
        abort_unless(Workshop::where('user_id', $request->user()->id)->exists(), 403);
        $data = $request->validate(['action' => ['required', Rule::in(array_keys(self::LABELS))], 'request_id' => 'required|uuid']);
        $subscription = MockSubscription::firstOrCreate(['user_id' => $request->user()->id]);
        DB::transaction(function () use ($subscription, $data) {
            $subscription = MockSubscription::lockForUpdate()->findOrFail($subscription->id);
            if (DB::table('mock_billing_events')->where('request_id', $data['request_id'])->exists()) {
                return;
            }
            $action = $data['action'];
            $status = $subscription->status;
            $allowed = match ($action) {
                'checkout' => in_array($status, ['none', 'canceled', 'refunded']),
                'approve', 'decline' => in_array($status, ['pending', 'past_due']),
                'renew', 'fail', 'schedule_cancel' => $status === 'active' && ! $subscription->cancel_at_period_end,
                'resume', 'end_period' => $status === 'active' && $subscription->cancel_at_period_end,
                'cancel' => in_array($status, ['active', 'pending', 'past_due']),
                'refund' => $status === 'active',
                default => false,
            };
            if (! $allowed) {
                throw ValidationException::withMessages(['billing' => 'Esta ação não está disponível no estado atual. Atualize a página.']);
            }
            $amount = 0;
            switch ($action) {
                case 'checkout': $subscription->status = 'pending';
                    $subscription->period_end = null;
                    $subscription->cancel_at_period_end = false;
                    break;
                case 'approve': $subscription->status = 'active';
                    $subscription->period_end = ($subscription->period_end?->isFuture() ? $subscription->period_end->copy() : now())->addMonthNoOverflow();
                    $subscription->cancel_at_period_end = false;
                    $amount = 9900;
                    break;
                case 'decline': case 'fail': $subscription->status = 'past_due';
                    break;
                case 'renew': $subscription->period_end = ($subscription->period_end?->isFuture() ? $subscription->period_end->copy() : now())->addMonthNoOverflow();
                    $amount = 9900;
                    break;
                case 'schedule_cancel': $subscription->cancel_at_period_end = true;
                    break;
                case 'resume': $subscription->cancel_at_period_end = false;
                    break;
                case 'end_period': case 'cancel': $subscription->status = 'canceled';
                    $subscription->cancel_at_period_end = false;
                    $subscription->period_end = now();
                    break;
                case 'refund': $subscription->status = 'refunded';
                    $subscription->cancel_at_period_end = false;
                    $subscription->period_end = now();
                    $amount = -9900;
                    break;
            }
            $subscription->save();
            DB::table('mock_billing_events')->insert(['mock_subscription_id' => $subscription->id, 'request_id' => $data['request_id'], 'action' => $action, 'amount' => $amount, 'created_at' => now()]);
        });

        return redirect()->route('billing.index')->with('status', self::LABELS[$data['action']].' — simulação, sem movimentar dinheiro.');
    }
}
