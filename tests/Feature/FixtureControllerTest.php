<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\Fixture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixtureControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTeams();
    }

    public function test_generate_fixtures_creates_fixtures_and_redirects(): void
    {
        $response = $this->post('/fixtures/generate');
        $response->assertRedirect('/fixtures');
        $this->assertEquals(12, Fixture::count());
    }

    public function test_fixtures_index_shows_all_weeks(): void
    {
        $this->post('/fixtures/generate');
        $response = $this->get('/fixtures');
        $response->assertStatus(200);
    }

    private function seedTeams(): void
    {
        Team::create(['name' => 'Chelsea',         'power' => 90, 'home_advantage' => 1.20, 'goalkeeper_factor' => 0.88]);
        Team::create(['name' => 'Arsenal',         'power' => 85, 'home_advantage' => 1.18, 'goalkeeper_factor' => 0.82]);
        Team::create(['name' => 'Manchester City', 'power' => 92, 'home_advantage' => 1.15, 'goalkeeper_factor' => 0.90]);
        Team::create(['name' => 'Liverpool',       'power' => 87, 'home_advantage' => 1.22, 'goalkeeper_factor' => 0.85]);
    }
}
