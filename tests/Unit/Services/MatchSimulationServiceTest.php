<?php

namespace Tests\Unit\Services;

use App\Models\Fixture;
use App\Models\MatchResult;
use App\Models\Team;
use App\Services\FixtureGeneratorService;
use App\Services\MatchSimulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchSimulationServiceTest extends TestCase
{
    use RefreshDatabase;

    private MatchSimulationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MatchSimulationService();
        $this->seedTeams();
        (new FixtureGeneratorService())->generate();
    }

    public function test_simulate_week_marks_matches_as_played(): void
    {
        $this->service->simulateWeek(1);

        $results = MatchResult::whereHas('fixture', fn ($q) => $q->where('week', 1))->get();

        foreach ($results as $result) {
            $this->assertTrue($result->is_played);
        }
    }

    public function test_simulate_week_produces_valid_scores(): void
    {
        $this->service->simulateWeek(1);

        $results = MatchResult::whereHas('fixture', fn ($q) => $q->where('week', 1))->get();

        foreach ($results as $result) {
            $this->assertGreaterThanOrEqual(0, $result->home_score);
            $this->assertGreaterThanOrEqual(0, $result->away_score);
            $this->assertLessThanOrEqual(8, $result->home_score);
            $this->assertLessThanOrEqual(8, $result->away_score);
        }
    }

    public function test_does_not_replay_already_played_week(): void
    {
        $this->service->simulateWeek(1);
        $firstResults = MatchResult::whereHas('fixture', fn ($q) => $q->where('week', 1))
            ->get()
            ->map(fn ($r) => $r->home_score . '-' . $r->away_score)
            ->toArray();

        $this->service->simulateWeek(1);
        $secondResults = MatchResult::whereHas('fixture', fn ($q) => $q->where('week', 1))
            ->get()
            ->map(fn ($r) => $r->home_score . '-' . $r->away_score)
            ->toArray();

        $this->assertEquals($firstResults, $secondResults);
    }

    public function test_stronger_team_wins_more_often_over_many_simulations(): void
    {
        $strong = Team::create(['name' => 'Strong FC', 'power' => 95, 'home_advantage' => 1.20, 'goalkeeper_factor' => 0.90]);
        $weak = Team::create(['name' => 'Weak FC', 'power' => 50, 'home_advantage' => 1.10, 'goalkeeper_factor' => 0.60]);

        $strongWins = 0;
        $weakWins = 0;

        for ($i = 0; $i < 200; $i++) {
            $result = $this->service->simulateMatch($strong, $weak, true);
            if ($result['home_score'] > $result['away_score']) $strongWins++;
            if ($result['home_score'] < $result['away_score']) $weakWins++;
        }

        $this->assertGreaterThan($weakWins * 2, $strongWins,
            "Strong team ($strongWins wins) should win significantly more than weak team ($weakWins wins)"
        );
    }

    public function test_simulate_all_remaining_weeks(): void
    {
        $this->service->simulateWeek(1);
        $this->service->simulateAllRemaining();

        $unplayed = MatchResult::where('is_played', false)->count();
        $this->assertEquals(0, $unplayed);
    }

    public function test_get_current_week_returns_next_unplayed(): void
    {
        $this->assertEquals(1, $this->service->getCurrentWeek());

        $this->service->simulateWeek(1);
        $this->assertEquals(2, $this->service->getCurrentWeek());

        $this->service->simulateWeek(2);
        $this->assertEquals(3, $this->service->getCurrentWeek());
    }

    private function seedTeams(): void
    {
        Team::create(['name' => 'Chelsea',         'power' => 90, 'home_advantage' => 1.20, 'goalkeeper_factor' => 0.88]);
        Team::create(['name' => 'Arsenal',         'power' => 85, 'home_advantage' => 1.18, 'goalkeeper_factor' => 0.82]);
        Team::create(['name' => 'Manchester City', 'power' => 92, 'home_advantage' => 1.15, 'goalkeeper_factor' => 0.90]);
        Team::create(['name' => 'Liverpool',       'power' => 87, 'home_advantage' => 1.22, 'goalkeeper_factor' => 0.85]);
    }
}
