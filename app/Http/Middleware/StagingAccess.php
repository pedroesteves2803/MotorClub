<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StagingAccess
{
    public function handle(Request $request, Closure $next)
    {
        // O webhook usa autenticação criptográfica própria, validada pelo SDK Stripe.
        if ($request->isMethod('post') && $request->is('stripe/webhook')) {
            return $next($request);
        }
        if (! app()->environment('staging')) {
            return $next($request);
        }
        $password = (string) config('production.staging_password');
        if ($password === '') {
            return response('Ambiente de testes indisponível: configure o acesso restrito.', 503);
        }
        if (! hash_equals((string) config('production.staging_user'), (string) $request->getUser()) || ! hash_equals($password, (string) $request->getPassword())) {
            return response('Autenticação necessária para acessar os testes.', 401, ['WWW-Authenticate' => 'Basic realm="MotorClub - Testes", charset="UTF-8"', 'Cache-Control' => 'no-store']);
        }

        return $next($request);
    }
}
