<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\MatchResult;
use App\Models\Team;
use App\Services\FixtureGeneratorService;
use App\Services\MatchSimulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTeams();
        (new FixtureGeneratorService())->generate();
    }

    public function test_update_match_result(): void
    {
        (new MatchSimulationService())->simulateWeek(1);

        $result = MatchResult::first();

        $response = $this->put("/matches/{$result->id}", [
            'home_score' => 5,
            'away_score' => 2,
        ]);

        $response->assertRedirect('/simulation');

        $result->refresh();
        $this->assertEquals(5, $result->home_score);
        $this->assertEquals(2, $result->away_score);
    }

    public function test_update_validates_scores(): void
    {
        (new MatchSimulationService())->simulateWeek(1);
        $result = MatchResult::first();

        $response = $this->put("/matches/{$result->id}", [
            'home_score' => -1,
            'away_score' => 2,
        ]);

        $response->assertSessionHasErrors('home_score');
    }

    private function seedTeams(): void
    {
        Team::create(['name' => 'Chelsea',         'power' => 90, 'home_advantage' => 1.20, 'goalkeeper_factor' => 0.88]);
        Team::create(['name' => 'Arsenal',         'power' => 85, 'home_advantage' => 1.18, 'goalkeeper_factor' => 0.82]);
        Team::create(['name' => 'Manchester City', 'power' => 92, 'home_advantage' => 1.15, 'goalkeeper_factor' => 0.90]);
        Team::create(['name' => 'Liverpool',       'power' => 87, 'home_advantage' => 1.22, 'goalkeeper_factor' => 0.85]);
    }
}
