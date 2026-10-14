<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use Illuminate\Console\Command;

class AutoverseBackfillVehicleTypes extends Command
{
    protected $signature = 'autoverse:backfill-vehicle-types {--dry-run}';

    protected $description = 'Normaliza tipos de veículos existentes sem apagar dados.';

    public function handle(): int
    {
        $allowed = array_keys(config('motorclub.vehicle_types'));
        $query = Vehicle::query()->whereNull('vehicle_type')->orWhereNotIn('vehicle_type', $allowed);
        $count = $query->count();
        if (! $this->option('dry-run')) {
            $query->update(['vehicle_type' => 'car']);
        }
        $this->info(($this->option('dry-run') ? 'Seriam normalizados: ' : 'Normalizados: ').$count);

        return self::SUCCESS;
    }
}
