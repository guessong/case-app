<?php

namespace Tests\Unit\Services;

use App\Models\Fixture;
use App\Models\MatchResult;
use App\Models\Team;
use App\Services\LeagueTableService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueTableServiceTest extends TestCase
{
    use RefreshDatabase;

    private LeagueTableService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LeagueTableService();
    }

    public function test_initial_standings_are_all_zeros(): void
    {
        $this->seedTeams();
        $standings = $this->service->getStandings();

        $this->assertCount(4, $standings);
        foreach ($standings as $row) {
            $this->assertEquals(0, $row['played']);
            $this->assertEquals(0, $row['won']);
            $this->assertEquals(0, $row['drawn']);
            $this->assertEquals(0, $row['lost']);
            $this->assertEquals(0, $row['goals_for']);
            $this->assertEquals(0, $row['goals_against']);
            $this->assertEquals(0, $row['goal_difference']);
            $this->assertEquals(0, $row['points']);
        }
    }

    public function test_win_gives_3_points(): void
    {
        $teams = $this->seedTeams();
        $fixture = Fixture::create(['week' => 1, 'home_team_id' => $teams[0]->id, 'away_team_id' => $teams[1]->id]);
        MatchResult::create(['fixture_id' => $fixture->id, 'home_score' => 2, 'away_score' => 0, 'is_played' => true]);

        $standings = $this->service->getStandings();
        $homeStanding = collect($standings)->firstWhere('team_id', $teams[0]->id);
        $awayStanding = collect($standings)->firstWhere('team_id', $teams[1]->id);

        $this->assertEquals(3, $homeStanding['points']);
        $this->assertEquals(1, $homeStanding['won']);
        $this->assertEquals(0, $awayStanding['points']);
        $this->assertEquals(1, $awayStanding['lost']);
    }

    public function test_draw_gives_1_point_each(): void
    {
        $teams = $this->seedTeams();
        $fixture = Fixture::create(['week' => 1, 'home_team_id' => $teams[0]->id, 'away_team_id' => $teams[1]->id]);
        MatchResult::create(['fixture_id' => $fixture->id, 'home_score' => 1, 'away_score' => 1, 'is_played' => true]);

        $standings = $this->service->getStandings();
        $homeStanding = collect($standings)->firstWhere('team_id', $teams[0]->id);
        $awayStanding = collect($standings)->firstWhere('team_id', $teams[1]->id);

        $this->assertEquals(1, $homeStanding['points']);
        $this->assertEquals(1, $homeStanding['drawn']);
        $this->assertEquals(1, $awayStanding['points']);
        $this->assertEquals(1, $awayStanding['drawn']);
    }

    public function test_goal_difference_calculated_correctly(): void
    {
        $teams = $this->seedTeams();
        $fixture = Fixture::create(['week' => 1, 'home_team_id' => $teams[0]->id, 'away_team_id' => $teams[1]->id]);
        MatchResult::create(['fixture_id' => $fixture->id, 'home_score' => 3, 'away_score' => 1, 'is_played' => true]);

        $standings = $this->service->getStandings();
        $homeStanding = collect($standings)->firstWhere('team_id', $teams[0]->id);
        $awayStanding = collect($standings)->firstWhere('team_id', $teams[1]->id);

        $this->assertEquals(2, $homeStanding['goal_difference']);
        $this->assertEquals(-2, $awayStanding['goal_difference']);
    }

    public function test_standings_sorted_by_points_then_goal_difference(): void
    {
        $teams = $this->seedTeams();

        $f1 = Fixture::create(['week' => 1, 'home_team_id' => $teams[0]->id, 'away_team_id' => $teams[1]->id]);
        MatchResult::create(['fixture_id' => $f1->id, 'home_score' => 3, 'away_score' => 0, 'is_played' => true]);

        $f2 = Fixture::create(['week' => 1, 'home_team_id' => $teams[2]->id, 'away_team_id' => $teams[3]->id]);
        MatchResult::create(['fixture_id' => $f2->id, 'home_score' => 1, 'away_score' => 0, 'is_played' => true]);

        $standings = $this->service->getStandings();

        $this->assertEquals($teams[0]->id, $standings[0]['team_id']);
        $this->assertEquals($teams[2]->id, $standings[1]['team_id']);
    }

    public function test_unplayed_matches_are_not_counted(): void
    {
        $teams = $this->seedTeams();
        $fixture = Fixture::create(['week' => 1, 'home_team_id' => $teams[0]->id, 'away_team_id' => $teams[1]->id]);
        MatchResult::create(['fixture_id' => $fixture->id, 'home_score' => 0, 'away_score' => 0, 'is_played' => false]);

        $standings = $this->service->getStandings();
        $homeStanding = collect($standings)->firstWhere('team_id', $teams[0]->id);

        $this->assertEquals(0, $homeStanding['played']);
        $this->assertEquals(0, $homeStanding['points']);
    }

    private function seedTeams(): array
    {
        return [
            Team::create(['name' => 'Chelsea',         'power' => 90, 'home_advantage' => 1.20, 'goalkeeper_factor' => 0.88]),
            Team::create(['name' => 'Arsenal',         'power' => 85, 'home_advantage' => 1.18, 'goalkeeper_factor' => 0.82]),
            Team::create(['name' => 'Manchester City', 'power' => 92, 'home_advantage' => 1.15, 'goalkeeper_factor' => 0.90]),
            Team::create(['name' => 'Liverpool',       'power' => 87, 'home_advantage' => 1.22, 'goalkeeper_factor' => 0.85]),
        ];
    }
}
