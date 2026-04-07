<?php

namespace Tests\Unit\Services;

use App\Models\Fixture;
use App\Models\MatchResult;
use App\Models\Team;
use App\Services\FixtureGeneratorService;
use App\Services\MatchSimulationService;
use App\Services\PredictionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictionServiceTest extends TestCase
{
    use RefreshDatabase;

    private PredictionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PredictionService::class);
        $this->seedTeams();
        app(FixtureGeneratorService::class)->generate();
    }

    public function test_predictions_return_all_teams(): void
    {
        $sim = app(MatchSimulationService::class);
        for ($w = 1; $w <= 3; $w++) {
            $sim->simulateWeek($w);
        }

        $predictions = $this->service->predict();
        $this->assertCount(4, $predictions);
    }

    public function test_predictions_sum_to_100(): void
    {
        $sim = app(MatchSimulationService::class);
        for ($w = 1; $w <= 3; $w++) {
            $sim->simulateWeek($w);
        }

        $predictions = $this->service->predict();
        $total = array_sum(array_column($predictions, 'percentage'));

        $this->assertGreaterThanOrEqual(99, $total);
        $this->assertLessThanOrEqual(101, $total);
    }

    public function test_team_with_insurmountable_lead_has_100_percent(): void
    {
        $teams = Team::all();

        // Play weeks 1-5 manually giving all wins to team 1
        for ($w = 1; $w <= 5; $w++) {
            $fixtures = Fixture::where('week', $w)->get();
            foreach ($fixtures as $fixture) {
                $result = $fixture->result;
                if ($fixture->home_team_id === $teams[0]->id) {
                    $result->update(['home_score' => 3, 'away_score' => 0, 'is_played' => true]);
                } elseif ($fixture->away_team_id === $teams[0]->id) {
                    $result->update(['home_score' => 0, 'away_score' => 3, 'is_played' => true]);
                } else {
                    $result->update(['home_score' => 0, 'away_score' => 0, 'is_played' => true]);
                }
            }
        }

        $predictions = $this->service->predict();
        $topTeam = collect($predictions)->firstWhere('team_id', $teams[0]->id);

        $this->assertEquals(100, $topTeam['percentage']);
    }

    public function test_returns_zero_for_all_before_last_3_weeks(): void
    {
        // With 0 weeks played, should return all zeros
        $predictions = $this->service->predict();

        foreach ($predictions as $p) {
            $this->assertEquals(0, $p['percentage']);
        }

        // Play only 2 weeks (threshold is 3 for 6-week league) - still zeros
        $sim = app(MatchSimulationService::class);
        $sim->simulateWeek(1);
        $sim->simulateWeek(2);

        $predictions = $this->service->predict();
        foreach ($predictions as $p) {
            $this->assertEquals(0, $p['percentage']);
        }
    }

    private function seedTeams(): void
    {
        Team::create(['name' => 'Chelsea',         'power' => 90, 'home_advantage' => 1.20, 'goalkeeper_factor' => 0.88]);
        Team::create(['name' => 'Arsenal',         'power' => 85, 'home_advantage' => 1.18, 'goalkeeper_factor' => 0.82]);
        Team::create(['name' => 'Manchester City', 'power' => 92, 'home_advantage' => 1.15, 'goalkeeper_factor' => 0.90]);
        Team::create(['name' => 'Liverpool',       'power' => 87, 'home_advantage' => 1.22, 'goalkeeper_factor' => 0.85]);
    }
}
