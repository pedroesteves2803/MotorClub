<?php

namespace App\Providers;

use App\Services\AI\AIReportGeneratorInterface;
use App\Services\AI\FakeAIReportGenerator;
use App\Services\DatabaseFilesystemAdapter;
use Illuminate\Filesystem\FilesystemAdapter as LaravelFilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

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
                default => throw new \InvalidArgumentException('Provedor Autoverse AI não configurado.'),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Storage::extend('database', function ($app, array $config) {
            $adapter = new DatabaseFilesystemAdapter;

            return new LaravelFilesystemAdapter(new Filesystem($adapter), $adapter, $config);
        });
    }
}
