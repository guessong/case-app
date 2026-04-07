<?php

namespace App\Services;

use App\Contracts\Repositories\FixtureRepositoryInterface;
use App\Contracts\Repositories\MatchResultRepositoryInterface;
use App\Contracts\Repositories\TeamRepositoryInterface;
use Illuminate\Support\Facades\DB;

class FixtureGeneratorService
{
    public function __construct(
        private TeamRepositoryInterface $teamRepo,
        private FixtureRepositoryInterface $fixtureRepo,
        private MatchResultRepositoryInterface $matchResultRepo,
    ) {}

    public function generate(): void
    {
        DB::transaction(function () {
            $this->matchResultRepo->deleteAll();
            $this->fixtureRepo->deleteAll();

            $teams = $this->teamRepo->all();
            $teamIds = $teams->pluck('id')->toArray();
            $n = count($teamIds);

            if ($n % 2 !== 0) {
                $teamIds[] = null;
                $n++;
            }

            $rounds = $n - 1;
            $matchesPerRound = $n / 2;
            $week = 1;

            // First half: home games
            for ($round = 0; $round < $rounds; $round++) {
                $this->generateRound($teamIds, $round, $n, $matchesPerRound, $week, false);
                $week++;
            }

            // Second half: reverse home/away
            for ($round = 0; $round < $rounds; $round++) {
                $this->generateRound($teamIds, $round, $n, $matchesPerRound, $week, true);
                $week++;
            }
        });
    }

    private function generateRound(array $teamIds, int $round, int $n, int $matchesPerRound, int $week, bool $reverse): void
    {
        $rotated = $this->rotateTeams($teamIds, $round);

        for ($match = 0; $match < $matchesPerRound; $match++) {
            $home = $rotated[$match];
            $away = $rotated[$n - 1 - $match];

            if ($home === null || $away === null) {
                continue;
            }

            if ($reverse) {
                [$home, $away] = [$away, $home];
            }

            $fixture = $this->fixtureRepo->create([
                'week' => $week,
                'home_team_id' => $home,
                'away_team_id' => $away,
            ]);

            $this->matchResultRepo->create([
                'fixture_id' => $fixture->id,
                'home_score' => 0,
                'away_score' => 0,
                'is_played' => false,
            ]);
        }
    }

    private function rotateTeams(array $teamIds, int $round): array
    {
        if ($round === 0) {
            return $teamIds;
        }

        $fixed = $teamIds[0];
        $rest = array_slice($teamIds, 1);

        for ($i = 0; $i < $round; $i++) {
            $last = array_pop($rest);
            array_unshift($rest, $last);
        }

        return array_merge([$fixed], $rest);
    }
}
