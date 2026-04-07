<?php

namespace Tests\Unit\Services;

use App\Models\Fixture;
use App\Models\Team;
use App\Services\FixtureGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixtureGeneratorServiceTest extends TestCase
{
    use RefreshDatabase;

    private FixtureGeneratorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FixtureGeneratorService();
    }

    public function test_generates_correct_number_of_weeks_for_4_teams(): void
    {
        $this->seedTeams();
        $this->service->generate();

        // 4 teams round-robin home & away = (4-1) * 2 = 6 weeks
        $weeks = Fixture::distinct('week')->pluck('week');
        $this->assertCount(6, $weeks);
    }

    public function test_each_week_has_correct_number_of_matches(): void
    {
        $this->seedTeams();
        $this->service->generate();

        // 4 teams / 2 = 2 matches per week
        for ($week = 1; $week <= 6; $week++) {
            $count = Fixture::where('week', $week)->count();
            $this->assertEquals(2, $count, "Week $week should have 2 matches");
        }
    }

    public function test_each_team_plays_every_other_team_home_and_away(): void
    {
        $this->seedTeams();
        $this->service->generate();

        $teams = Team::all();

        foreach ($teams as $teamA) {
            foreach ($teams as $teamB) {
                if ($teamA->id === $teamB->id) continue;

                $homeGame = Fixture::where('home_team_id', $teamA->id)
                    ->where('away_team_id', $teamB->id)
                    ->count();

                $this->assertEquals(1, $homeGame,
                    "{$teamA->name} should play 1 home game against {$teamB->name}"
                );
            }
        }
    }

    public function test_no_team_plays_twice_in_same_week(): void
    {
        $this->seedTeams();
        $this->service->generate();

        for ($week = 1; $week <= 6; $week++) {
            $fixtures = Fixture::where('week', $week)->get();
            $teamIds = [];

            foreach ($fixtures as $fixture) {
                $this->assertNotContains($fixture->home_team_id, $teamIds, "Duplicate team in week $week");
                $this->assertNotContains($fixture->away_team_id, $teamIds, "Duplicate team in week $week");
                $teamIds[] = $fixture->home_team_id;
                $teamIds[] = $fixture->away_team_id;
            }
        }
    }

    public function test_generates_match_result_placeholders(): void
    {
        $this->seedTeams();
        $this->service->generate();

        $fixtureCount = Fixture::count();
        $resultCount = \App\Models\MatchResult::count();

        $this->assertEquals($fixtureCount, $resultCount);
        $this->assertTrue(\App\Models\MatchResult::where('is_played', false)->count() === $resultCount);
    }

    public function test_clears_existing_fixtures_before_generating(): void
    {
        $this->seedTeams();
        $this->service->generate();
        $this->service->generate(); // generate again

        // Should still be 12 fixtures, not 24
        $this->assertEquals(12, Fixture::count());
    }

    private function seedTeams(): void
    {
        Team::create(['name' => 'Chelsea',         'power' => 90, 'home_advantage' => 1.20, 'goalkeeper_factor' => 0.88]);
        Team::create(['name' => 'Arsenal',         'power' => 85, 'home_advantage' => 1.18, 'goalkeeper_factor' => 0.82]);
        Team::create(['name' => 'Manchester City', 'power' => 92, 'home_advantage' => 1.15, 'goalkeeper_factor' => 0.90]);
        Team::create(['name' => 'Liverpool',       'power' => 87, 'home_advantage' => 1.22, 'goalkeeper_factor' => 0.85]);
    }
}
