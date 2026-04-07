<?php

namespace App\Contracts;

use App\Models\Team;

interface MatchSimulatorInterface
{
    public function simulateMatch(Team $home, Team $away, bool $applyHomeAdvantage = true): array;
}
