<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\MatchResult;
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
    ) {}

    public function index(): Response
    {
        $currentWeek = $this->matchSimulator->getCurrentWeek();
        $totalWeeks = Fixture::max('week') ?? 0;

        $displayWeek = $currentWeek ?? $totalWeeks;

        $weekFixtures = Fixture::with(['homeTeam:id,name', 'awayTeam:id,name', 'result'])
            ->where('week', $displayWeek)
            ->get();

        $allWeeksResults = Fixture::with(['homeTeam:id,name', 'awayTeam:id,name', 'result'])
            ->whereHas('result', fn ($q) => $q->where('is_played', true))
            ->orderBy('week')
            ->get()
            ->groupBy('week');

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
        MatchResult::query()->delete();
        Fixture::query()->delete();
        return redirect('/');
    }
}
