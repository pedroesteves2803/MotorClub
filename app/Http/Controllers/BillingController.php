<?php

namespace App\Http\Controllers;

use App\Models\BillingAccount;
use App\Models\MockSubscription;
use App\Models\Workshop;
use App\Services\StripeBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BillingController extends Controller
{
    public function index(Request $request, StripeBilling $stripe)
    {
        if (MockSubscription::enabled()) {
            return app(MockBillingController::class)->show($request);
        }

        return view('billing.index', [
            'account' => $request->user() ? BillingAccount::where('user_id', $request->user()->id)->first() : null,
            'workshop' => $request->user() ? Workshop::where('user_id', $request->user()->id)->first() : null,
            'configured' => $stripe->configured(),
        ]);
    }

    public function checkout(Request $request, StripeBilling $stripe)
    {
        abort_if(config('billing.driver') === 'mock', 404);
        abort_unless(Workshop::where('user_id', $request->user()->id)->exists(), 403, 'Cadastre sua oficina antes de contratar.');
        $request->validate(['consent' => 'accepted']);
        $account = BillingAccount::firstOrCreate(['user_id' => $request->user()->id], ['reference' => (string) Str::uuid(), 'attempt' => (string) Str::uuid()]);

        return $this->redirectToStripe(fn () => $stripe->checkout($account));
    }

    public function portal(Request $request, StripeBilling $stripe)
    {
        abort_if(config('billing.driver') === 'mock', 404);
        $account = BillingAccount::where('user_id', $request->user()->id)->firstOrFail();

        return $this->redirectToStripe(fn () => $stripe->portal($account));
    }

    private function redirectToStripe(callable $operation)
    {
        try {
            return redirect()->away($operation());
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable) {
            // Não registra corpos da API, credenciais nem dados financeiros.
            return redirect()->route('billing.index')->withErrors(['billing' => 'Não foi possível acessar a Stripe. Tente novamente em instantes.']);
        }
    }
}
