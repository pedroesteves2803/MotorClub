<?php

namespace App\Filament\Pages;

use App\Services\SystemHealthService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SystemHealth extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Plataforma';

    protected static ?string $navigationLabel = 'Diagnóstico técnico';

    protected static ?string $title = 'Diagnóstico técnico';

    protected static ?string $slug = 'diagnostico-tecnico';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.system-health';

    /** @var array<int, array<string, mixed>> */
    public array $checks = [];

    /** @var array<string, int> */
    public array $summary = [];

    public string $checkedAt = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function mount(SystemHealthService $health): void
    {
        $this->loadSnapshot($health);
    }

    public function refreshChecks(SystemHealthService $health): void
    {
        $this->loadSnapshot($health);
    }

    private function loadSnapshot(SystemHealthService $health): void
    {
        $snapshot = $health->snapshot();
        $this->checks = $snapshot['checks'];
        $this->summary = $snapshot['summary'];
        $this->checkedAt = $snapshot['checked_at'];
    }
}
