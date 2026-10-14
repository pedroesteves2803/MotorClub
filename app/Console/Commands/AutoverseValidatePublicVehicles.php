<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use Illuminate\Console\Command;

class AutoverseValidatePublicVehicles extends Command
{
    protected $signature = 'autoverse:validate-public-vehicles';

    protected $description = 'Verifica invariantes dos veículos publicados.';

    public function handle(): int
    {
        $errors = 0;
        Vehicle::query()->where('visibility', 'public')->with('user')->each(function (Vehicle $vehicle) use (&$errors): void {
            foreach ([filled($vehicle->slug), $vehicle->user !== null] as $ok) {
                if (! $ok) {
                    $errors++;
                }
            }
            if (! preg_match('/^[a-z][a-z0-9-]*$/', (string) $vehicle->slug)) {
                $errors++;
                $this->error("Slug inválido no veículo #{$vehicle->id}");
            }
        });
        $this->line($errors === 0 ? 'Todos os veículos públicos estão consistentes.' : "Inconsistências encontradas: {$errors}");

        return $errors === 0 ? self::SUCCESS : self::FAILURE;
    }
}
