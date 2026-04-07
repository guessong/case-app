<?php

namespace App\Repositories;

use App\Contracts\Repositories\MatchResultRepositoryInterface;
use App\Models\MatchResult;
use Illuminate\Database\Eloquent\Collection;

class MatchResultRepository implements MatchResultRepositoryInterface
{
    public function getPlayedResults(): Collection
    {
        return MatchResult::with('fixture')
            ->where('is_played', true)
            ->get();
    }

    public function getPlayedWeekCount(): int
    {
        return MatchResult::where('is_played', true)
            ->join('fixtures', 'match_results.fixture_id', '=', 'fixtures.id')
            ->distinct('fixtures.week')
            ->count('fixtures.week');
    }

    public function create(array $data): MatchResult
    {
        return MatchResult::create($data);
    }

    public function deleteAll(): void
    {
        MatchResult::query()->delete();
    }
}
