<?php

return [
    'driver' => env('BILLING_DRIVER', env('APP_ENV') === 'local' ? 'mock' : 'stripe'),
    // Esta versão aceita exclusivamente credenciais e eventos de teste.
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'price' => env('STRIPE_PRICE_ID'),
    'amount' => 9900,
    'currency' => 'brl',
];
