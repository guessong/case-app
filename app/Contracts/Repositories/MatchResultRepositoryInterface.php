<?php

namespace App\Contracts\Repositories;

use App\Models\MatchResult;
use Illuminate\Database\Eloquent\Collection;

interface MatchResultRepositoryInterface
{
    public function getPlayedResults(): Collection;
    public function getPlayedWeekCount(): int;
    public function create(array $data): MatchResult;
    public function deleteAll(): void;
}
