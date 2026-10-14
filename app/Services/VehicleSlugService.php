<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleSlugHistory;
use Illuminate\Support\Str;

class VehicleSlugService
{
    /** @var list<string> */
    private const RESERVED = [
        'admin', 'api', 'auth', 'central-mc-gestao', 'comunidade', 'configuracoes', 'explorar',
        'fotos', 'garagem', 'login', 'novo', 'projetos', 'shots', 'veiculos',
    ];

    public function ensure(Vehicle $vehicle): string
    {
        if ($vehicle->slug) {
            return $vehicle->slug;
        }

        return $this->change($vehicle, null, false);
    }

    public function ensurePublicKey(Vehicle $vehicle): string
    {
        if ($vehicle->public_key) {
            return $vehicle->public_key;
        }

        $base = Str::limit(Str::slug($vehicle->slug ?: trim($vehicle->brand.' '.$vehicle->model)), 160, '') ?: 'veiculo';
        $candidate = $base;
        $suffix = 2;

        while (Vehicle::query()->where('public_key', $candidate)->where('id', '<>', $vehicle->id)->exists()) {
            $candidate = Str::limit($base, 150, '').'-'.$suffix++;
        }

        $vehicle->forceFill(['public_key' => $candidate])->saveQuietly();

        return $candidate;
    }

    public function change(Vehicle $vehicle, ?string $requested = null, bool $rememberPrevious = true): string
    {
        $base = $this->baseFor($vehicle, $requested);
        $slug = $this->uniqueSlug($base, $vehicle->id);

        if ($vehicle->slug === $slug) {
            return $slug;
        }

        if ($rememberPrevious && $vehicle->slug) {
            $vehicle->slugHistories()->firstOrCreate(
                ['slug' => $vehicle->slug],
                ['redirect_status' => 301],
            );
        }

        $vehicle->forceFill(['slug' => $slug, 'slug_updated_at' => now()])->saveQuietly();

        return $slug;
    }

    private function baseFor(Vehicle $vehicle, ?string $requested): string
    {
        $source = trim((string) ($requested ?: $vehicle->public_name ?: implode('-', array_filter([$vehicle->brand, $vehicle->model, $vehicle->trim]))));
        $base = Str::slug($source);

        if ($base === '' || in_array($base, self::RESERVED, true) || ctype_digit($base[0] ?? '')) {
            $base = 'veiculo-'.$base;
        }

        return Str::limit($base, 170, '');
    }

    private function uniqueSlug(string $base, ?int $ignoreId): string
    {
        $slug = $base;
        $suffix = 2;
        while (
            Vehicle::query()->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->where('slug', $slug)->exists()
            || VehicleSlugHistory::query()->where('slug', $slug)->exists()
        ) {
            $slug = Str::limit($base, 170, '').'-'.$suffix++;
        }

        return $slug;
    }
}
