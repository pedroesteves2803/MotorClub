<?php

return [
    'terms_version' => env('AUTOVERSE_TERMS_VERSION', '2026.10'),
    'privacy_version' => env('AUTOVERSE_PRIVACY_VERSION', '2026.10'),
    'cookies_version' => env('AUTOVERSE_COOKIES_VERSION', '2026.10'),
    'community_version' => env('AUTOVERSE_COMMUNITY_VERSION', '2026.10'),
    'updated_at' => env('AUTOVERSE_LEGAL_UPDATED_AT', '2026-10-14'),
    // No analytics, advertising or other optional provider is loaded by default.
    'optional_cookies_available' => (bool) env('AUTOVERSE_OPTIONAL_COOKIES', false),
    'responsible_party' => env('AUTOVERSE_RESPONSIBLE_PARTY', 'Autoverse'),
    'contact_email' => env('AUTOVERSE_CONTACT_EMAIL', 'suporte@motorclub.example'),
    'privacy_email' => env('AUTOVERSE_PRIVACY_EMAIL', 'privacidade@motorclub.example'),
];
