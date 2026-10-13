<?php

namespace App\Providers;

use App\Services\AI\AIReportGeneratorInterface;
use App\Services\AI\FakeAIReportGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AIReportGeneratorInterface::class, function () {
            return match (config('motorclub.ai_provider')) {
                'fake' => new FakeAIReportGenerator,
                default => throw new \InvalidArgumentException('Provedor MotorClub AI não configurado.'),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
