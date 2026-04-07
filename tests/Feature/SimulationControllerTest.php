<?php

namespace Tests\Feature;

use App\Models\Fixture;
use App\Models\MatchResult;
use App\Models\Team;
use App\Services\FixtureGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTeams();
        app(FixtureGeneratorService::class)->generate();
    }

    public function test_simulation_index_returns_200(): void
    {
        $response = $this->get('/simulation');
        $response->assertStatus(200);
    }

    public function test_play_next_week_simulates_one_week(): void
    {
        $response = $this->post('/simulation/play-next');
        $response->assertRedirect('/simulation');

        $played = MatchResult::where('is_played', true)->count();
        $this->assertEquals(2, $played);
    }

    public function test_play_all_simulates_everything(): void
    {
        $response = $this->post('/simulation/play-all');
        $response->assertRedirect('/simulation');

        $unplayed = MatchResult::where('is_played', false)->count();
        $this->assertEquals(0, $unplayed);
    }

    public function test_reset_clears_all_data(): void
    {
        $this->post('/simulation/play-all');
        $response = $this->post('/simulation/reset');
        $response->assertRedirect('/');

        $this->assertEquals(0, Fixture::count());
        $this->assertEquals(0, MatchResult::count());
    }

    private function seedTeams(): void
    {
        Team::create(['name' => 'Chelsea',         'power' => 90, 'home_advantage' => 1.20, 'goalkeeper_factor' => 0.88]);
        Team::create(['name' => 'Arsenal',         'power' => 85, 'home_advantage' => 1.18, 'goalkeeper_factor' => 0.82]);
        Team::create(['name' => 'Manchester City', 'power' => 92, 'home_advantage' => 1.15, 'goalkeeper_factor' => 0.90]);
        Team::create(['name' => 'Liverpool',       'power' => 87, 'home_advantage' => 1.22, 'goalkeeper_factor' => 0.85]);
    }
}
