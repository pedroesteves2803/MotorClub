<?php

use Illuminate\Support\Facades\Storage;

it('persiste arquivos no disco de mídia do banco', function () {
    $contents = "RIFF\x00\x00\x00\x00WEBP";

    expect(Storage::disk('database')->put('vehicles/test.webp', $contents))->toBeTrue()
        ->and(Storage::disk('database')->exists('vehicles/test.webp'))->toBeTrue()
        ->and(Storage::disk('database')->get('vehicles/test.webp'))->toBe($contents);

    $response = Storage::disk('database')->response('vehicles/test.webp', null, ['Content-Type' => 'image/webp']);

    expect($response->headers->get('Content-Type'))->toBe('image/webp')
        ->and($response->headers->get('Content-Length'))->toBe((string) strlen($contents));

    expect(Storage::disk('database')->delete('vehicles/test.webp'))->toBeTrue()
        ->and(Storage::disk('database')->exists('vehicles/test.webp'))->toBeFalse();
});
