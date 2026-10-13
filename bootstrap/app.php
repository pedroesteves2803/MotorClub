<?php

use App\Http\Middleware\FeatureEnabled;
use App\Http\Middleware\PrivateResponses;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\StagingAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias(['feature' => FeatureEnabled::class]);
        $middleware->append(StagingAccess::class);
        $middleware->validateCsrfTokens(except: ['stripe/webhook']);
        $middleware->web(append: [PrivateResponses::class, SecurityHeaders::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
