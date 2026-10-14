<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    |
    | Keep the policy in report-only mode until reports from Livewire, Flux,
    | Filament and the configured external font provider have been reviewed.
    | The header is still useful during production hardening because browsers
    | report violations without breaking the application.
    */
    'csp_report_only' => (bool) env('CSP_REPORT_ONLY', true),

    'csp_report_uri' => env('CSP_REPORT_URI'),

    'csp_policy' => implode('; ', array_filter([
        "default-src 'self'",
        "base-uri 'self'",
        "object-src 'none'",
        "frame-ancestors 'self'",
        "form-action 'self' https://accounts.google.com",
        "img-src 'self' data: blob: https:",
        "font-src 'self' data: https://fonts.bunny.net",
        "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
        "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
        "connect-src 'self' https://accounts.google.com",
        env('CSP_REPORT_URI') ? 'report-uri '.env('CSP_REPORT_URI') : null,
    ])),
];
