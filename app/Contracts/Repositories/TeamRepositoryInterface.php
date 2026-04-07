<?php

namespace App\Contracts\Repositories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;

interface TeamRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Team;
    public function create(array $data): Team;
    public function update(Team $team, array $data): Team;
    public function delete(Team $team): void;
}
