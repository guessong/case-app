<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\FixtureRepositoryInterface;
use App\Contracts\Repositories\MatchResultRepositoryInterface;
use App\Services\LeagueTableService;
use App\Services\MatchSimulationService;
use App\Services\PredictionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SimulationController extends Controller
{
    public function __construct(
        private MatchSimulationService $matchSimulator,
        private LeagueTableService $leagueTable,
        private PredictionService $predictionService,
        private FixtureRepositoryInterface $fixtureRepo,
        private MatchResultRepositoryInterface $matchResultRepo,
    ) {}

    public function index(): Response
    {
        $currentWeek = $this->matchSimulator->getCurrentWeek();
        $totalWeeks = $this->fixtureRepo->getMaxWeek() ?? 0;

        $displayWeek = $currentWeek ?? $totalWeeks;

        $weekFixtures = $this->fixtureRepo->getByWeekWithRelations($displayWeek);

        $allWeeksResults = $this->fixtureRepo->getPlayedWithTeams();

        return Inertia::render('Simulation/Index', [
            'standings' => $this->leagueTable->getStandings(),
            'currentWeek' => $displayWeek,
            'totalWeeks' => $totalWeeks,
            'weekFixtures' => $weekFixtures,
            'allWeeksResults' => $allWeeksResults,
            'predictions' => $this->predictionService->predict(),
            'isFinished' => $currentWeek === null,
        ]);
    }

    public function playNext(): RedirectResponse
    {
        $currentWeek = $this->matchSimulator->getCurrentWeek();
        if ($currentWeek !== null) {
            $this->matchSimulator->simulateWeek($currentWeek);
        }
        return redirect('/simulation');
    }

    public function playAll(): RedirectResponse
    {
        $this->matchSimulator->simulateAllRemaining();
        return redirect('/simulation');
    }

    public function reset(): RedirectResponse
    {
        $this->matchResultRepo->deleteAll();
        $this->fixtureRepo->deleteAll();
        return redirect('/');
    }
}
