<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\MatchResult;
use App\Models\Team;

class PredictionService
{
    private const SIMULATIONS = 1000;

    public function __construct(
        private MatchSimulationService $matchSimulator
    ) {}

    public function predict(): array
    {
        $teams = Team::all();
        $totalWeeks = Fixture::max('week') ?? 0;

        $playedWeeks = MatchResult::where('is_played', true)
            ->join('fixtures', 'match_results.fixture_id', '=', 'fixtures.id')
            ->distinct('fixtures.week')
            ->count('fixtures.week');

        // Only predict from week 4 onwards
        if ($playedWeeks < 4) {
            return $teams->map(fn (Team $t) => [
                'team_id' => $t->id,
                'team_name' => $t->name,
                'percentage' => 0,
            ])->toArray();
        }

        $currentPoints = $this->getCurrentPoints($teams);

        $remainingFixtures = Fixture::with(['homeTeam', 'awayTeam', 'result'])
            ->whereHas('result', fn ($q) => $q->where('is_played', false))
            ->get();

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

        $playedResults = MatchResult::with('fixture')
            ->where('is_played', true)
            ->get();

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

    private function deterministic($teams, array $points): array
    {
        $maxPoints = max($points);
        $leaders = array_keys(array_filter($points, fn ($p) => $p === $maxPoints));
        $share = (int) round(100 / count($leaders));

        return $teams->map(fn (Team $t) => [
            'team_id' => $t->id,
            'team_name' => $t->name,
            'percentage' => in_array($t->id, $leaders) ? $share : 0,
        ])->toArray();
    }
}
