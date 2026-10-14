<?php

namespace App\Services;

use App\Models\ShareEvent;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class ShareEventLogger
{
    public function log(Request $request, ?Vehicle $vehicle, string $eventType, ?string $source = null): void
    {
        ShareEvent::create([
            'vehicle_id' => $vehicle?->id,
            'event_type' => $eventType,
            'source' => $source,
            'user_agent_hash' => $request->userAgent() ? hash('sha256', $request->userAgent()) : null,
            'ip_hash' => $request->ip() ? hash_hmac('sha256', $request->ip(), (string) config('app.key')) : null,
        ]);
    }
}
