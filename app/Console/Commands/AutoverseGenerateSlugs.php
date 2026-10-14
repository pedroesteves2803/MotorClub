<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Services\VehicleSlugService;
use Illuminate\Console\Command;

class AutoverseGenerateSlugs extends Command
{
    protected $signature = 'autoverse:generate-slugs {--dry-run}';

    protected $description = 'Gera slugs públicos idempotentes para veículos existentes.';

    public function handle(VehicleSlugService $slugs): int
    {
        $count = 0;
        Vehicle::query()->whereNull('slug')->orderBy('id')->each(function (Vehicle $vehicle) use ($slugs, &$count): void {
            $count++;
            if (! $this->option('dry-run')) {
                $slugs->ensure($vehicle);
            }
        });
        $this->info(($this->option('dry-run') ? 'Seriam gerados: ' : 'Gerados: ').$count);

        return self::SUCCESS;
    }
}
