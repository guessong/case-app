<?php

namespace App\Services;

use App\Contracts\MatchSimulatorInterface;
use App\Contracts\Repositories\FixtureRepositoryInterface;
use App\Models\Team;

class MatchSimulationService implements MatchSimulatorInterface
{
    public function __construct(
        private FixtureRepositoryInterface $fixtureRepo,
    ) {}

    public function simulateWeek(int $week): array
    {
        $fixtures = $this->fixtureRepo->getByWeekWithRelations($week);

        $results = [];

        foreach ($fixtures as $fixture) {
            if ($fixture->result && $fixture->result->is_played) {
                $results[] = $fixture->result;
                continue;
            }

            $scores = $this->simulateMatch($fixture->homeTeam, $fixture->awayTeam, true);

            $matchResult = $fixture->result;
            $matchResult->update([
                'home_score' => $scores['home_score'],
                'away_score' => $scores['away_score'],
                'is_played' => true,
            ]);

            $results[] = $matchResult->fresh();
        }

        return $results;
    }

    public function simulateAllRemaining(): array
    {
        $allResults = [];
        $totalWeeks = $this->fixtureRepo->getMaxWeek();

        for ($week = 1; $week <= $totalWeeks; $week++) {
            $allResults[$week] = $this->simulateWeek($week);
        }

        return $allResults;
    }

    public function getCurrentWeek(): ?int
    {
        $totalWeeks = $this->fixtureRepo->getMaxWeek();
        if ($totalWeeks === null) return null;

        return $this->fixtureRepo->getNextUnplayedWeek();
    }

    /**
     * Simulate a single match. Public so PredictionService can use it.
     *
     * Algorithm:
     * 1. Effective power = base power * home_advantage (if home)
     * 2. Expected goals = (teamPower / totalPower) * 3.0
     * 3. Goalkeeper factor reduces opponent's expected goals
     * 4. Poisson random for realistic score generation
     */
    public function simulateMatch(Team $home, Team $away, bool $applyHomeAdvantage = true): array
    {
        $homePower = $home->power;
        $awayPower = $away->power;

        if ($applyHomeAdvantage) {
            $homePower *= $home->home_advantage;
        }

        $homeExpectedGoals = ($homePower / ($homePower + $awayPower)) * 3.0;
        $awayExpectedGoals = ($awayPower / ($homePower + $awayPower)) * 3.0;

        // Goalkeeper factor reduces opponent goals
        $homeExpectedGoals *= (1 - $away->goalkeeper_factor * 0.3);
        $awayExpectedGoals *= (1 - $home->goalkeeper_factor * 0.3);

        $homeExpectedGoals = max(0.3, $homeExpectedGoals);
        $awayExpectedGoals = max(0.3, $awayExpectedGoals);

        $homeScore = $this->poissonRandom($homeExpectedGoals);
        $awayScore = $this->poissonRandom($awayExpectedGoals);

        return [
            'home_score' => min($homeScore, 8),
            'away_score' => min($awayScore, 8),
        ];
    }

    private function poissonRandom(float $lambda): int
    {
        $L = exp(-$lambda);
        $k = 0;
        $p = 1.0;

        do {
            $k++;
            $p *= mt_rand() / mt_getrandmax();
        } while ($p > $L);

        return $k - 1;
    }
}
