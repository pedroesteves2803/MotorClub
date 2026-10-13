<?php

namespace App\Filament\Widgets;

use App\Models\ShotGallery;
use App\Models\ShotReport;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class MotorClubOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $counts = Cache::remember('filament:motorclub-overview', now()->addSeconds(30), static fn () => [
            'members' => User::count(),
            'new_members' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'vehicles' => Vehicle::count(),
            'public_vehicles' => Vehicle::where('visibility', 'public')->count(),
            'galleries' => ShotGallery::count(),
            'public_galleries' => ShotGallery::where('is_public', true)->count(),
            'open_reports' => ShotReport::where('status', 'open')->count(),
        ]);

        return [
            Stat::make('Membros', $counts['members'])
                ->description($counts['new_members'].' novos nos últimos 30 dias'),
            Stat::make('Veículos', $counts['vehicles'])
                ->description($counts['public_vehicles'].' públicos'),
            Stat::make('Galerias Shots', $counts['galleries'])
                ->description($counts['public_galleries'].' publicadas'),
            Stat::make('Denúncias em aberto', $counts['open_reports'])
                ->description('Conteúdos aguardando moderação'),
        ];
    }
}
