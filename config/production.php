<?php

return [
    'staging_user' => env('STAGING_ACCESS_USER', 'motorclub'),
    'staging_password' => env('STAGING_ACCESS_PASSWORD'),
    'test_mode' => env('TEST_MODE', env('APP_ENV', 'production') !== 'production'),
    'demo_enabled' => env('DEMO_ENABLED', env('APP_ENV', 'production') !== 'production'),
    'support_email' => env('SUPPORT_EMAIL'),
    'storage_persistent' => env('STORAGE_PERSISTENT', false),
    'backups_verified' => env('BACKUPS_VERIFIED', false),
];
