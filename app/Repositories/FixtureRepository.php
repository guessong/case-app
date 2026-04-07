<?php

namespace App\Repositories;

use App\Contracts\Repositories\FixtureRepositoryInterface;
use App\Models\Fixture;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class FixtureRepository implements FixtureRepositoryInterface
{
    public function getByWeekWithTeams(): SupportCollection
    {
        return Fixture::with(['homeTeam:id,name', 'awayTeam:id,name'])
            ->orderBy('week')
            ->get()
            ->groupBy('week');
    }

    public function getByWeek(int $week): Collection
    {
        return Fixture::where('week', $week)->get();
    }

    public function getByWeekWithRelations(int $week): Collection
    {
        return Fixture::with(['homeTeam', 'awayTeam', 'result'])
            ->where('week', $week)
            ->get();
    }

    public function getPlayedWithTeams(): SupportCollection
    {
        return Fixture::with(['homeTeam:id,name', 'awayTeam:id,name', 'result'])
            ->whereHas('result', fn ($q) => $q->where('is_played', true))
            ->orderBy('week')
            ->get()
            ->groupBy('week');
    }

    public function getMaxWeek(): ?int
    {
        return Fixture::max('week');
    }

    public function getNextUnplayedWeek(): ?int
    {
        return Fixture::whereHas('result', fn ($q) => $q->where('is_played', false))
            ->min('week');
    }

    public function getRemainingFixtures(): Collection
    {
        return Fixture::with(['homeTeam', 'awayTeam', 'result'])
            ->whereHas('result', fn ($q) => $q->where('is_played', false))
            ->get();
    }

    public function deleteAll(): void
    {
        Fixture::query()->delete();
    }

    public function create(array $data): Fixture
    {
        return Fixture::create($data);
    }

    public function hasTeam(int $teamId): bool
    {
        return Fixture::where('home_team_id', $teamId)
            ->orWhere('away_team_id', $teamId)
            ->exists();
    }
}
