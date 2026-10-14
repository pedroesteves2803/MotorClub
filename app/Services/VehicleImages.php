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
        $image = $this->resize($image, (int) config('motorclub.photo_max_dimension', 2400));
        // Re-encoding strips EXIF/GPS metadata and embedded payloads.
        ob_start();
        imagewebp($image, null, (int) config('motorclub.photo_webp_quality', 82));
        $bytes = ob_get_clean();
        imagedestroy($image);
        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        if (! Storage::disk(config('filesystems.private_disk'))->put($path, $bytes)) {
            throw ValidationException::withMessages(['photo' => 'Não foi possível salvar a foto. Tente novamente.']);
        }

        return $path;
    }

    /**
     * Duplicate an already processed private image for a second album.
     * A copy keeps each album independently deletable while reusing the library asset.
     */
    public function copy(string $sourcePath, string $directory = 'media'): string
    {
        $disk = Storage::disk(config('filesystems.private_disk'));
        if (! $disk->exists($sourcePath)) {
            throw ValidationException::withMessages(['photos' => 'Esta foto não está mais disponível.']);
        }

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        if (! $disk->put($path, $disk->get($sourcePath))) {
            throw ValidationException::withMessages(['photos' => 'Não foi possível reutilizar esta foto. Tente novamente.']);
        }

        return $path;
    }

    private function resize(\GdImage $image, int $maxDimension): \GdImage
    {
        $maxDimension = max(320, $maxDimension);
        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1, $maxDimension / max($width, $height));
        if ($scale >= 1) {
            return $image;
        }

        $resized = imagecreatetruecolor((int) round($width * $scale), (int) round($height * $scale));
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, imagesx($resized), imagesy($resized), $width, $height);
        imagedestroy($image);

        return $resized;
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
