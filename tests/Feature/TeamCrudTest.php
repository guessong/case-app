<?php

namespace Tests\Feature;

use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_team(): void
    {
        $response = $this->post('/teams', [
            'name' => 'Barcelona',
            'power' => 88,
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('teams', ['name' => 'Barcelona', 'power' => 88]);
    }

    public function test_create_team_validates_required_fields(): void
    {
        $response = $this->post('/teams', []);
        $response->assertSessionHasErrors(['name', 'power']);
    }

    public function test_create_team_validates_unique_name(): void
    {
        Team::create(['name' => 'Chelsea', 'power' => 90, 'home_advantage' => 1.18, 'goalkeeper_factor' => 0.86]);

        $response = $this->post('/teams', ['name' => 'Chelsea', 'power' => 85]);
        $response->assertSessionHasErrors('name');
    }

    public function test_create_team_validates_power_range(): void
    {
        $response = $this->post('/teams', ['name' => 'Test', 'power' => 150]);
        $response->assertSessionHasErrors('power');

        $response = $this->post('/teams', ['name' => 'Test', 'power' => 0]);
        $response->assertSessionHasErrors('power');
    }

    public function test_can_update_team(): void
    {
        $team = Team::create(['name' => 'Chelsea', 'power' => 90, 'home_advantage' => 1.18, 'goalkeeper_factor' => 0.86]);

        $response = $this->put("/teams/{$team->id}", [
            'name' => 'Chelsea FC',
            'power' => 92,
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('teams', ['id' => $team->id, 'name' => 'Chelsea FC', 'power' => 92]);
    }

    public function test_can_delete_team(): void
    {
        $team = Team::create(['name' => 'Chelsea', 'power' => 90, 'home_advantage' => 1.18, 'goalkeeper_factor' => 0.86]);

        $response = $this->delete("/teams/{$team->id}");
        $response->assertRedirect('/');
        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    public function test_home_page_shows_teams(): void
    {
        Team::create(['name' => 'Chelsea', 'power' => 90, 'home_advantage' => 1.18, 'goalkeeper_factor' => 0.86]);

        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
