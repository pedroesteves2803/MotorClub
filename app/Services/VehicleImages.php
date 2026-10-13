<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VehicleImages
{
    public function store(UploadedFile $file, string $directory = 'vehicles'): string
    {
        $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (! $image) {
            throw ValidationException::withMessages(['photo' => 'Não foi possível processar esta imagem.']);
        }
        // Re-encoding strips EXIF/GPS metadata and embedded payloads.
        ob_start();
        imagewebp($image, null, 85);
        $bytes = ob_get_clean();
        imagedestroy($image);
        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        if (! Storage::disk(config('filesystems.private_disk'))->put($path, $bytes)) {
            throw ValidationException::withMessages(['photo' => 'Não foi possível salvar a foto. Tente novamente.']);
        }

        return $path;
    }
}
