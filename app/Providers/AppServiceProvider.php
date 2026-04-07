<?php

namespace App\Providers;

use App\Contracts\MatchSimulatorInterface;
use App\Services\MatchSimulationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MatchSimulatorInterface::class, MatchSimulationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
