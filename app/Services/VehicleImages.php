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
        $image = $this->correctOrientation($image, $file);
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

    private function correctOrientation(\GdImage $image, UploadedFile $file): \GdImage
    {
        if (! function_exists('exif_read_data') || ! in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg'], true)) {
            return $image;
        }
        $orientation = (int) (@exif_read_data($file->getRealPath())['Orientation'] ?? 1);
        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };
        if ($angle === 0) {
            return $image;
        }
        $rotated = imagerotate($image, $angle, 0);
        if (! $rotated) {
            return $image;
        }
        imagedestroy($image);

        return $rotated;
    }
}
