<?php

namespace App\Services;

use App\Contracts\MatchSimulatorInterface;
use App\Contracts\Repositories\FixtureRepositoryInterface;
use App\Contracts\Repositories\MatchResultRepositoryInterface;
use App\Contracts\Repositories\TeamRepositoryInterface;
use App\Models\Team;

class PredictionService
{
    private const SIMULATIONS = 1000;

    public function __construct(
        private MatchSimulatorInterface $matchSimulator,
        private TeamRepositoryInterface $teamRepo,
        private FixtureRepositoryInterface $fixtureRepo,
        private MatchResultRepositoryInterface $matchResultRepo,
    ) {}

    public function predict(): array
    {
        $teams = $this->teamRepo->all();
        $totalWeeks = $this->fixtureRepo->getMaxWeek() ?? 0;

        $playedWeeks = $this->matchResultRepo->getPlayedWeekCount();

        // Predictions start when entering the last 3 weeks (FAQ requirement)
        $predictionThreshold = max($totalWeeks - 3, 1);
        if ($playedWeeks < $predictionThreshold) {
            return $teams->map(fn (Team $t) => [
                'team_id' => $t->id,
                'team_name' => $t->name,
                'percentage' => 0,
            ])->toArray();
        }

        $currentPoints = $this->getCurrentPoints($teams);

        $remainingFixtures = $this->fixtureRepo->getRemainingFixtures();

        if ($remainingFixtures->isEmpty()) {
            return $this->deterministic($teams, $currentPoints);
        }

        $winCounts = array_fill_keys($teams->pluck('id')->toArray(), 0);

        for ($sim = 0; $sim < self::SIMULATIONS; $sim++) {
            $simPoints = $currentPoints;

            foreach ($remainingFixtures as $fixture) {
                $result = $this->matchSimulator->simulateMatch(
                    $fixture->homeTeam,
                    $fixture->awayTeam,
                    true
                );

                if ($result['home_score'] > $result['away_score']) {
                    $simPoints[$fixture->home_team_id] += 3;
                } elseif ($result['home_score'] < $result['away_score']) {
                    $simPoints[$fixture->away_team_id] += 3;
                } else {
                    $simPoints[$fixture->home_team_id] += 1;
                    $simPoints[$fixture->away_team_id] += 1;
                }
            }

            $maxPoints = max($simPoints);
            $winners = array_keys(array_filter($simPoints, fn ($p) => $p === $maxPoints));

            foreach ($winners as $winnerId) {
                $winCounts[$winnerId] += 1 / count($winners);
            }
        }

        return $teams->map(fn (Team $t) => [
            'team_id' => $t->id,
            'team_name' => $t->name,
            'percentage' => (int) round(($winCounts[$t->id] / self::SIMULATIONS) * 100),
        ])->toArray();
    }

    private function getCurrentPoints($teams): array
    {
        $points = [];
        foreach ($teams as $team) {
            $points[$team->id] = 0;
        }

        $playedResults = $this->matchResultRepo->getPlayedResults();

        foreach ($playedResults as $result) {
            $homeId = $result->fixture->home_team_id;
            $awayId = $result->fixture->away_team_id;

            if ($result->home_score > $result->away_score) {
                $points[$homeId] += 3;
            } elseif ($result->home_score < $result->away_score) {
                $points[$awayId] += 3;
            } else {
                $points[$homeId] += 1;
                $points[$awayId] += 1;
            }
        }

        return $points;
    }

    /**
     * When all matches are played, use the actual league standings
     * (points, GD, GF) to determine the winner deterministically.
     */
    private function deterministic($teams, array $points): array
    {
        $standings = app(LeagueTableService::class)->getStandings();
        $winnerId = $standings[0]['team_id'] ?? null;

        return $teams->map(fn (Team $t) => [
            'team_id' => $t->id,
            'team_name' => $t->name,
            'percentage' => $t->id === $winnerId ? 100 : 0,
        ])->toArray();
    }
}
