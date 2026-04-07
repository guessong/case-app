<?php

namespace Tests\Unit\Models;

use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_has_required_attributes(): void
    {
        $team = Team::factory()->create([
            'name' => 'Chelsea',
            'power' => 88,
            'home_advantage' => 1.15,
            'goalkeeper_factor' => 0.85,
        ]);

        $this->assertEquals('Chelsea', $team->name);
        $this->assertEquals(88, $team->power);
        $this->assertEquals(1.15, $team->home_advantage);
        $this->assertEquals(0.85, $team->goalkeeper_factor);
    }

    public function test_team_has_fixtures_relationship(): void
    {
        $team = Team::factory()->create();

        $this->assertCount(0, $team->homeFixtures);
        $this->assertCount(0, $team->awayFixtures);
    }
}
