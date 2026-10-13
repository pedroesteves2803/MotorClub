<?php

namespace App\Http\Middleware;

use App\Models\FeatureSetting;
use Closure;
use Illuminate\Http\Request;

class FeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature)
    {
        abort_unless(FeatureSetting::enabled($feature), 404);

        return $next($request);
    }
}
