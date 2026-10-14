<?php

namespace App\Services;

use App\Models\MediaAsset;
use DateTimeInterface;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToWriteFile;

class DatabaseFilesystemAdapter implements FilesystemAdapter
{
    public function fileExists(string $path): bool
    {
        return MediaAsset::query()->where('path', $this->normalize($path))->exists();
    }

    public function directoryExists(string $path): bool
    {
        $prefix = trim($this->normalize($path), '/');

        if ($prefix === '') {
            return MediaAsset::query()->exists();
        }

        return MediaAsset::query()->where('path', 'like', $prefix.'/%')->exists();
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $this->persist($path, $contents);
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        if (! is_resource($contents)) {
            throw UnableToWriteFile::atLocation($path, 'O conteúdo enviado não é um stream válido.');
        }

        $bytes = stream_get_contents($contents);
        if ($bytes === false) {
            throw UnableToWriteFile::atLocation($path, 'Não foi possível ler o stream enviado.');
        }

        $this->persist($path, $bytes);
    }

    public function read(string $path): string
    {
        $asset = $this->asset($path);
        if (! $asset) {
            throw UnableToReadFile::fromLocation($path, 'O arquivo não existe.');
        }

        return (string) $asset->contents;
    }

    public function readStream(string $path)
    {
        $stream = fopen('php://temp', 'w+b');
        if (! $stream) {
            throw UnableToReadFile::fromLocation($path, 'Não foi possível abrir o stream.');
        }

        fwrite($stream, $this->read($path));
        rewind($stream);

        return $stream;
    }

    public function delete(string $path): void
    {
        MediaAsset::query()->where('path', $this->normalize($path))->delete();
    }

    public function deleteDirectory(string $path): void
    {
        $prefix = trim($this->normalize($path), '/');
        if ($prefix === '') {
            MediaAsset::query()->delete();

            return;
        }

        MediaAsset::query()->where('path', 'like', $prefix.'/%')->delete();
    }

    public function createDirectory(string $path, Config $config): void {}

    public function setVisibility(string $path, string $visibility): void {}

    public function visibility(string $path): FileAttributes
    {
        $this->ensureAsset($path);

        return new FileAttributes($this->normalize($path), null, 'private');
    }

    public function mimeType(string $path): FileAttributes
    {
        $asset = $this->ensureAsset($path, false);

        return new FileAttributes($this->normalize($path), null, null, null, $asset->mime_type ?: 'application/octet-stream');
    }

    public function lastModified(string $path): FileAttributes
    {
        $asset = $this->ensureAsset($path, false);
        $timestamp = $asset->updated_at instanceof DateTimeInterface ? $asset->updated_at->getTimestamp() : time();

        return new FileAttributes($this->normalize($path), null, null, $timestamp);
    }

    public function fileSize(string $path): FileAttributes
    {
        $asset = $this->ensureAsset($path, false);

        return new FileAttributes($this->normalize($path), (int) $asset->size_bytes);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $prefix = trim($this->normalize($path), '/');
        $query = MediaAsset::query()->select(['path', 'mime_type', 'size_bytes', 'updated_at']);
        if ($prefix !== '') {
            $query->where('path', 'like', $prefix.'/%');
        }

        foreach ($query->cursor() as $asset) {
            $relative = $prefix === '' ? $asset->path : ltrim(substr($asset->path, strlen($prefix)), '/');
            if (! $deep && str_contains($relative, '/')) {
                continue;
            }

            yield new FileAttributes(
                $asset->path,
                (int) $asset->size_bytes,
                'private',
                $asset->updated_at?->getTimestamp(),
                $asset->mime_type ?: 'application/octet-stream',
            );
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->persist($destination, $this->read($source));
        $this->delete($source);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $this->persist($destination, $this->read($source));
    }

    private function persist(string $path, string $contents): void
    {
        $path = $this->normalize($path);
        $mime = null;
        if (function_exists('finfo_buffer')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = @finfo_buffer($finfo, $contents) ?: null;
                finfo_close($finfo);
            }
        }

        MediaAsset::query()->updateOrCreate(
            ['path' => $path],
            ['mime_type' => $mime, 'size_bytes' => strlen($contents), 'contents' => $contents],
        );
    }

    private function asset(string $path, bool $withContents = true): ?MediaAsset
    {
        $query = MediaAsset::query()->where('path', $this->normalize($path));
        if (! $withContents) {
            $query->select(['id', 'path', 'mime_type', 'size_bytes', 'updated_at']);
        }

        return $query->first();
    }

    private function ensureAsset(string $path, bool $withContents = true): MediaAsset
    {
        $asset = $this->asset($path, $withContents);
        if (! $asset) {
            throw UnableToRetrieveMetadata::create($path, StorageAttributes::ATTRIBUTE_PATH, 'O arquivo não existe.');
        }

        return $asset;
    }

    private function normalize(string $path): string
    {
        return ltrim(str_replace('\\', '/', $path), '/');
    }
}
