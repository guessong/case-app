<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

interface FixtureRepositoryInterface
{
    public function getByWeekWithTeams(): SupportCollection;
    public function getByWeek(int $week): Collection;
    public function getByWeekWithRelations(int $week): Collection;
    public function getPlayedWithTeams(): SupportCollection;
    public function getMaxWeek(): ?int;
    public function getNextUnplayedWeek(): ?int;
    public function getRemainingFixtures(): Collection;
    public function deleteAll(): void;
    public function create(array $data): \App\Models\Fixture;
    public function hasTeam(int $teamId): bool;
}
