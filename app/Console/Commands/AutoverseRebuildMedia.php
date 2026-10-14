<?php

namespace App\Console\Commands;

use App\Models\MediaPhoto;
use App\Models\ProjectPhoto;
use App\Models\VehiclePhoto;
use Illuminate\Console\Command;

class AutoverseRebuildMedia extends Command
{
    protected $signature = 'autoverse:rebuild-media {--dry-run}';

    protected $description = 'Confere a biblioteca de mídia existente e reporta os álbuns ativos.';

    public function handle(): int
    {
        $total = VehiclePhoto::count() + ProjectPhoto::count() + MediaPhoto::count();
        $this->info(($this->option('dry-run') ? 'Mídias a conferir: ' : 'Mídias conferidas: ').$total);

        return self::SUCCESS;
    }
}
