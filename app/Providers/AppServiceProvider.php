<?php

namespace App\Providers;

use App\Contracts\MatchSimulatorInterface;
use App\Contracts\Repositories\FixtureRepositoryInterface;
use App\Contracts\Repositories\MatchResultRepositoryInterface;
use App\Contracts\Repositories\TeamRepositoryInterface;
use App\Repositories\FixtureRepository;
use App\Repositories\MatchResultRepository;
use App\Repositories\TeamRepository;
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
        $this->app->bind(TeamRepositoryInterface::class, TeamRepository::class);
        $this->app->bind(FixtureRepositoryInterface::class, FixtureRepository::class);
        $this->app->bind(MatchResultRepositoryInterface::class, MatchResultRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
