<?php

namespace App\Filament\Widgets;

use App\Models\ShotGallery;
use App\Models\ShotReport;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MotorClubOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Membros', User::count())
                ->description(User::where('created_at', '>=', now()->subDays(30))->count().' novos nos últimos 30 dias'),
            Stat::make('Veículos', Vehicle::count())
                ->description(Vehicle::where('visibility', 'public')->count().' públicos'),
            Stat::make('Galerias Shots', ShotGallery::count())
                ->description(ShotGallery::where('is_public', true)->count().' publicadas'),
            Stat::make('Denúncias em aberto', ShotReport::where('status', 'open')->count())
                ->description('Conteúdos aguardando moderação'),
        ];
    }
}
