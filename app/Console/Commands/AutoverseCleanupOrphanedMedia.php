<?php

namespace App\Console\Commands;

use App\Models\MediaPhoto;
use App\Models\ProjectPhoto;
use App\Models\VehiclePhoto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AutoverseCleanupOrphanedMedia extends Command
{
    protected $signature = 'autoverse:cleanup-orphaned-media {--delete : Excluir registros e arquivos órfãos}';

    protected $description = 'Lista ou remove referências de mídia cujo arquivo não existe.';

    public function handle(): int
    {
        $disk = Storage::disk(config('filesystems.private_disk'));
        $orphans = collect();
        foreach ([VehiclePhoto::class, ProjectPhoto::class, MediaPhoto::class] as $model) {
            $model::query()->each(function ($photo) use ($disk, $orphans): void {
                if (! $disk->exists($photo->path)) {
                    $orphans->push($photo);
                }
            });
        }
        if ($this->option('delete')) {
            $orphans->each->delete();
        }
        $this->info(($this->option('delete') ? 'Removidos: ' : 'Encontrados: ').$orphans->count());

        return self::SUCCESS;
    }
}
