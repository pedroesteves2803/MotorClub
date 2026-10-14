<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;

class AuthenticateFilamentWithApplication extends FilamentAuthenticate
{
    public function handle($request, \Closure $next, ...$guards)
    {
        if (! Filament::auth()->check() && ! $request->expectsJson() && $request->hasSession()) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return parent::handle($request, $next, ...$guards);
    }

    protected function redirectTo($request): ?string
    {
        return route('login');
    }
}
