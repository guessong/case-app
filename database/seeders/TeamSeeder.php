<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            ['name' => 'Chelsea',         'power' => 90, 'home_advantage' => 1.20, 'goalkeeper_factor' => 0.88],
            ['name' => 'Arsenal',         'power' => 85, 'home_advantage' => 1.18, 'goalkeeper_factor' => 0.82],
            ['name' => 'Manchester City', 'power' => 92, 'home_advantage' => 1.15, 'goalkeeper_factor' => 0.90],
            ['name' => 'Liverpool',       'power' => 87, 'home_advantage' => 1.22, 'goalkeeper_factor' => 0.85],
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }
    }
}
