<?php

return [
    'garage' => env('FEATURE_GARAGE', true),
    'shots' => env('FEATURE_SHOTS', true),
    'explore' => env('FEATURE_EXPLORE', true),
    'ai_reports' => env('FEATURE_AI_REPORTS', false),
    'workshops' => env('FEATURE_WORKSHOPS', false),
    'events' => env('FEATURE_EVENTS', false),
    'subscriptions' => env('FEATURE_SUBSCRIPTIONS', false),
    'demo' => env('FEATURE_DEMO', false),
];
