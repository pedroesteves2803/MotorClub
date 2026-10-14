<?php

it('limita uploads temporários do Livewire ao disco privado e a imagens pequenas', function () {
    expect(config('livewire.temporary_file_upload.disk') ?: config('filesystems.default'))->toBe('local')
        ->and(config('livewire.temporary_file_upload.middleware'))->toBe('throttle:10,1')
        ->and(config('livewire.temporary_file_upload.rules'))->toContain('file')
        ->and(config('livewire.temporary_file_upload.rules'))->toContain('max:5120');
});

it('limita a gravação de eventos nos QR Codes públicos', function () {
    expect(app('router')->getRoutes()->getByName('vehicles.slug.qr')->middleware())->toContain('throttle:60,1')
        ->and(app('router')->getRoutes()->getByName('public.garage.qr')->middleware())->toContain('throttle:60,1');
});
