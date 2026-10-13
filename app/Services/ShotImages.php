<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShotImages
{
    /** @return array{original_path: string, preview_path: string, original_sha256: string} */
    public function store(UploadedFile $file, string $photoUuid, ?string $photographerName = null): array
    {
        $source = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (! $source) {
            throw ValidationException::withMessages(['photo' => 'Não foi possível processar esta imagem.']);
        }
        $source = $this->correctOrientation($source, $file);
        $id = Str::uuid()->toString();
        $extension = $file->extension() ?: 'jpg';
        $originalPath = "shots/originals/{$id}.{$extension}";
        $previewPath = "shots/previews/{$id}.webp";
        $hash = hash_file('sha256', $file->getRealPath());
        $disk = Storage::disk(config('filesystems.private_disk'));
        if (! $disk->putFileAs('shots/originals', $file, "{$id}.{$extension}")) {
            imagedestroy($source);
            throw ValidationException::withMessages(['photo' => 'Não foi possível guardar o original.']);
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, config('motorclub.shots_preview_max_dimension') / max($width, $height));
        $preview = imagecreatetruecolor((int) round($width * $scale), (int) round($height * $scale));
        imagecopyresampled($preview, $source, 0, 0, 0, 0, imagesx($preview), imagesy($preview), $width, $height);
        try {
            $this->watermark($preview, $photoUuid, $photographerName);
            ob_start();
            imagewebp($preview, null, config('motorclub.shots_preview_webp_quality'));
            $bytes = ob_get_clean();
        } finally {
            imagedestroy($source);
            imagedestroy($preview);
        }
        if (! isset($bytes) || ! $disk->put($previewPath, $bytes)) {
            $disk->delete($originalPath);
            throw ValidationException::withMessages(['photo' => 'Não foi possível criar a prévia.']);
        }

        return ['original_path' => $originalPath, 'preview_path' => $previewPath, 'original_sha256' => $hash];
    }

    private function watermark(\GdImage $image, string $uuid, ?string $photographerName): void
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $opacity = max(0, min(100, config('motorclub.shots_watermark_opacity')));
        $alpha = (int) round(127 * (1 - ($opacity / 100)));
        $text = trim(config('motorclub.shots_watermark_text').' · '.($photographerName && config('motorclub.shots_watermark_photographer') ? $photographerName.' · ' : '').str($uuid)->substr(0, 8));
        $color = imagecolorallocatealpha($image, 255, 255, 255, $alpha);
        $font = min(5, max(2, (int) floor(min($width, $height) / 220)));
        $count = max(3, min(12, config('motorclub.shots_watermark_count')));
        for ($index = 0; $index < $count; $index++) {
            $x = (int) (($width - 130) * (($index * 37 + 13) % 100) / 100);
            $y = (int) (($height - 20) * (($index * 53 + 29) % 100) / 100);
            imagestring($image, $font, max(8, $x), max(8, $y), $text, $color);
        }
        for ($offset = -$height; $offset < $width; $offset += max(120, (int) (min($width, $height) / 3))) {
            imageline($image, $offset, $height, $offset + $height, 0, $color);
        }
        $main = imagecolorallocatealpha($image, 255, 255, 255, max(0, $alpha - 20));
        $stamp = imagecreatetruecolor(220, 25);
        imagealphablending($stamp, false);
        imagesavealpha($stamp, true);
        $transparent = imagecolorallocatealpha($stamp, 0, 0, 0, 127);
        imagefill($stamp, 0, 0, $transparent);
        imagestring($stamp, 5, 5, 5, $text, $main);
        $rotated = imagerotate($stamp, -18, $transparent);
        imagecopy($image, $rotated, max(12, (int) (($width - imagesx($rotated)) / 2)), max(12, (int) (($height - imagesy($rotated)) / 2)), 0, 0, imagesx($rotated), imagesy($rotated));
        imagedestroy($stamp);
        imagedestroy($rotated);
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
