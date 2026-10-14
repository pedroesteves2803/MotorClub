<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AutoverseRebuildPublicCache extends Command
{
    protected $signature = 'autoverse:rebuild-public-cache {--dry-run}';

    protected $description = 'Avança a versão do cache público derivado sem apagar o cache da aplicação.';

    public function handle(): int
    {
        if ($this->option('dry-run')) {
            $this->info('Cache público seria invalidado por versão.');

            return self::SUCCESS;
        }

        $version = now()->timestamp;
        Cache::forever('autoverse.public.cache.version', $version);
        $this->info("Cache público invalidado. Versão: {$version}");

        return self::SUCCESS;
    }
}
