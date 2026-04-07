# Champions League Simulation - Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a football league simulation where 4 teams play a 6-week round-robin tournament with power-based match results and championship predictions.

**Architecture:** Laravel 11 API backend with Vue 3 SPA frontend via Inertia.js. Backend services handle fixture generation (round-robin algorithm), match simulation (weighted probability based on team power), and championship prediction (Monte Carlo simulation). Docker (Sail) for development and deployment. Three-screen flow: Teams → Fixtures → Simulation.

**Tech Stack:** PHP 8.1 / Laravel 11 / Inertia.js / Vue 3 (Composition API) / Tailwind CSS / SQLite / Docker (Sail) / PHPUnit

---

## File Structure

### Backend

```
app/
├── Http/
│   └── Controllers/
│       ├── TeamController.php          # List teams
│       ├── FixtureController.php       # Generate fixtures
│       ├── SimulationController.php    # Play week, play all, reset
│       └── MatchController.php         # Edit match result
├── Models/
│   ├── Team.php                        # id, name, power, home_advantage, goalkeeper_factor
│   ├── Fixture.php                     # id, week, home_team_id, away_team_id
│   └── MatchResult.php                # id, fixture_id, home_score, away_score, is_played
├── Services/
│   ├── FixtureGeneratorService.php    # Round-robin fixture algorithm
│   ├── MatchSimulationService.php     # Power-based match result engine
│   ├── LeagueTableService.php         # Standing calculation from results
│   └── PredictionService.php          # Monte Carlo championship prediction
└── Enums/
    └── MatchOutcome.php               # WIN, DRAW, LOSS

database/
├── migrations/
│   ├── create_teams_table.php
│   ├── create_fixtures_table.php
│   └── create_match_results_table.php
└── seeders/
    └── TeamSeeder.php                 # 4 teams with power ratings

routes/
└── web.php                            # Inertia routes

tests/
├── Unit/
│   ├── Services/
│   │   ├── FixtureGeneratorServiceTest.php
│   │   ├── MatchSimulationServiceTest.php
│   │   ├── LeagueTableServiceTest.php
│   │   └── PredictionServiceTest.php
│   └── Models/
│       └── TeamTest.php
└── Feature/
    ├── FixtureControllerTest.php
    ├── SimulationControllerTest.php
    └── MatchControllerTest.php
```

### Frontend

```
resources/js/
├── app.js                             # Inertia + Vue bootstrap
├── Pages/
│   ├── Teams/Index.vue                # Screen 1: Team list + Generate Fixtures
│   ├── Fixtures/Index.vue             # Screen 2: Weekly fixture grid + Start Simulation
│   └── Simulation/Index.vue           # Screen 3: Table + Results + Predictions
├── Components/
│   ├── LeagueTable.vue                # Standings table component
│   ├── WeekResults.vue                # Current week match results
│   ├── ChampionshipPredictions.vue    # Prediction percentages
│   └── MatchResultEditor.vue          # Inline edit match score (bonus)
└── Layouts/
    └── AppLayout.vue                  # Shared layout wrapper
```

---

## Task 1: Laravel Project Setup with Sail

**Files:**
- Create: entire Laravel project scaffold
- Create: `docker-compose.yml` (via Sail)
- Modify: `.env`

- [ ] **Step 1: Create Laravel project with Sail**

```bash
cd /Users/bektascimen/projects/caseapp
composer create-project laravel/laravel . --prefer-dist
```

- [ ] **Step 2: Install Laravel Sail**

```bash
cd /Users/bektascimen/projects/caseapp
composer require laravel/sail --dev
php artisan sail:install --with=none
```

Choose no additional services - we'll use SQLite to keep it simple.

- [ ] **Step 3: Configure .env for SQLite**

In `.env`, set:
```
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite
```

Create the SQLite file:
```bash
touch database/database.sqlite
```

- [ ] **Step 4: Install Inertia.js server-side**

```bash
composer require inertiajs/inertia-laravel
```

- [ ] **Step 5: Install frontend dependencies**

```bash
npm install @inertiajs/vue3 vue@3 @vitejs/plugin-vue tailwindcss @tailwindcss/vite
```

- [ ] **Step 6: Configure Vite for Vue + Tailwind**

Replace `vite.config.js`:
```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
});
```

- [ ] **Step 7: Setup Inertia root template**

Replace `resources/views/app.blade.php`:
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
```

- [ ] **Step 8: Setup Vue + Inertia app bootstrap**

Replace `resources/js/app.js`:
```js
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
```

- [ ] **Step 9: Setup Tailwind CSS**

Replace `resources/css/app.css`:
```css
@import "tailwindcss";
```

- [ ] **Step 10: Setup Inertia middleware**

```bash
php artisan inertia:middleware
```

Then add to `bootstrap/app.php` in the `withMiddleware` closure:
```php
$middleware->web(append: [
    \App\Http\Middleware\HandleInertiaRequests::class,
]);
```

- [ ] **Step 11: Create a test page to verify setup**

Create `resources/js/Pages/Teams/Index.vue`:
```vue
<template>
    <div class="p-8">
        <h1 class="text-2xl font-bold">Setup works!</h1>
    </div>
</template>
```

Add route in `routes/web.php`:
```php
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Teams/Index'));
```

- [ ] **Step 12: Verify it runs**

```bash
php artisan serve &
npm run dev &
# Open http://localhost:8000 - should see "Setup works!"
```

- [ ] **Step 13: Init git and commit**

```bash
git init
git add -A
git commit -m "chore: initial Laravel + Inertia + Vue 3 + Tailwind setup"
```

---

## Task 2: Database Schema + Models + Seeder

**Files:**
- Create: `database/migrations/xxxx_create_teams_table.php`
- Create: `database/migrations/xxxx_create_fixtures_table.php`
- Create: `database/migrations/xxxx_create_match_results_table.php`
- Create: `app/Models/Team.php`
- Create: `app/Models/Fixture.php`
- Create: `app/Models/MatchResult.php`
- Create: `database/seeders/TeamSeeder.php`
- Test: `tests/Unit/Models/TeamTest.php`

- [ ] **Step 1: Write Team model test**

Create `tests/Unit/Models/TeamTest.php`:
```php
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
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --filter=TeamTest
```
Expected: FAIL - migration/model not found.

- [ ] **Step 3: Create teams migration**

```bash
php artisan make:migration create_teams_table
```

Edit the migration:
```php
public function up(): void
{
    Schema::create('teams', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->integer('power');           // 1-100 team overall strength
        $table->float('home_advantage');    // multiplier, e.g. 1.15
        $table->float('goalkeeper_factor'); // 0.0-1.0, higher = better keeper
        $table->timestamps();
    });
}
```

- [ ] **Step 4: Create fixtures migration**

```bash
php artisan make:migration create_fixtures_table
```

```php
public function up(): void
{
    Schema::create('fixtures', function (Blueprint $table) {
        $table->id();
        $table->integer('week');
        $table->foreignId('home_team_id')->constrained('teams')->cascadeOnDelete();
        $table->foreignId('away_team_id')->constrained('teams')->cascadeOnDelete();
        $table->timestamps();

        $table->unique(['week', 'home_team_id', 'away_team_id']);
    });
}
```

- [ ] **Step 5: Create match_results migration**

```bash
php artisan make:migration create_match_results_table
```

```php
public function up(): void
{
    Schema::create('match_results', function (Blueprint $table) {
        $table->id();
        $table->foreignId('fixture_id')->constrained()->cascadeOnDelete();
        $table->unsignedTinyInteger('home_score')->default(0);
        $table->unsignedTinyInteger('away_score')->default(0);
        $table->boolean('is_played')->default(false);
        $table->timestamps();
    });
}
```

- [ ] **Step 6: Create Team model**

Create `app/Models/Team.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'power', 'home_advantage', 'goalkeeper_factor'];

    public function homeFixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'home_team_id');
    }

    public function awayFixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'away_team_id');
    }
}
```

- [ ] **Step 7: Create Team factory**

Create `database/factories/TeamFactory.php`:
```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Chelsea', 'Arsenal', 'Liverpool', 'Manchester City']),
            'power' => fake()->numberBetween(70, 95),
            'home_advantage' => fake()->randomFloat(2, 1.05, 1.25),
            'goalkeeper_factor' => fake()->randomFloat(2, 0.6, 0.95),
        ];
    }
}
```

- [ ] **Step 8: Create Fixture model**

Create `app/Models/Fixture.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Fixture extends Model
{
    use HasFactory;

    protected $fillable = ['week', 'home_team_id', 'away_team_id'];

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(MatchResult::class);
    }
}
```

- [ ] **Step 9: Create MatchResult model**

Create `app/Models/MatchResult.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchResult extends Model
{
    use HasFactory;

    protected $fillable = ['fixture_id', 'home_score', 'away_score', 'is_played'];

    protected function casts(): array
    {
        return [
            'is_played' => 'boolean',
        ];
    }

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class);
    }
}
```

- [ ] **Step 10: Create TeamSeeder**

Create `database/seeders/TeamSeeder.php`:
```php
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
```

Register in `database/seeders/DatabaseSeeder.php`:
```php
public function run(): void
{
    $this->call([TeamSeeder::class]);
}
```

- [ ] **Step 11: Run migrations + seed and test**

```bash
php artisan migrate:fresh --seed
php artisan test --filter=TeamTest
```
Expected: All tests PASS.

- [ ] **Step 12: Commit**

```bash
git add -A
git commit -m "feat: add database schema, models, and team seeder"
```

---

## Task 3: Fixture Generator Service

**Files:**
- Create: `app/Services/FixtureGeneratorService.php`
- Test: `tests/Unit/Services/FixtureGeneratorServiceTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Unit/Services/FixtureGeneratorServiceTest.php`:
```php
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
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test --filter=FixtureGeneratorServiceTest
```
Expected: FAIL - class not found.

- [ ] **Step 3: Implement FixtureGeneratorService**

Create `app/Services/FixtureGeneratorService.php`:
```php
<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\MatchResult;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class FixtureGeneratorService
{
    /**
     * Generate round-robin fixtures for all teams (home & away).
     * Uses the "circle method" / round-robin scheduling algorithm.
     */
    public function generate(): void
    {
        DB::transaction(function () {
            MatchResult::query()->delete();
            Fixture::query()->delete();

            $teams = Team::all();
            $teamIds = $teams->pluck('id')->toArray();
            $n = count($teamIds);

            // If odd number of teams, add a "bye" placeholder
            if ($n % 2 !== 0) {
                $teamIds[] = null;
                $n++;
            }

            $rounds = $n - 1;         // number of rounds for single round-robin
            $matchesPerRound = $n / 2;

            $week = 1;

            // First half: home games
            for ($round = 0; $round < $rounds; $round++) {
                $fixtures = $this->generateRound($teamIds, $round, $n, $matchesPerRound, $week, false);
                $week++;
            }

            // Second half: reverse home/away
            for ($round = 0; $round < $rounds; $round++) {
                $fixtures = $this->generateRound($teamIds, $round, $n, $matchesPerRound, $week, true);
                $week++;
            }
        });
    }

    private function generateRound(array $teamIds, int $round, int $n, int $matchesPerRound, int $week, bool $reverse): void
    {
        // Circle method: fix first team, rotate others
        $rotated = $this->rotateTeams($teamIds, $round);

        for ($match = 0; $match < $matchesPerRound; $match++) {
            $home = $rotated[$match];
            $away = $rotated[$n - 1 - $match];

            // Skip bye matches
            if ($home === null || $away === null) {
                continue;
            }

            if ($reverse) {
                [$home, $away] = [$away, $home];
            }

            $fixture = Fixture::create([
                'week' => $week,
                'home_team_id' => $home,
                'away_team_id' => $away,
            ]);

            MatchResult::create([
                'fixture_id' => $fixture->id,
                'home_score' => 0,
                'away_score' => 0,
                'is_played' => false,
            ]);
        }
    }

    /**
     * Circle method rotation: fix first element, rotate the rest.
     */
    private function rotateTeams(array $teamIds, int $round): array
    {
        if ($round === 0) {
            return $teamIds;
        }

        $fixed = $teamIds[0];
        $rest = array_slice($teamIds, 1);

        // Rotate rest array by $round positions
        for ($i = 0; $i < $round; $i++) {
            $last = array_pop($rest);
            array_unshift($rest, $last);
        }

        return array_merge([$fixed], $rest);
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
php artisan test --filter=FixtureGeneratorServiceTest
```
Expected: All 6 tests PASS.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "feat: add fixture generator with round-robin algorithm"
```

---

## Task 4: Match Simulation Service

**Files:**
- Create: `app/Services/MatchSimulationService.php`
- Test: `tests/Unit/Services/MatchSimulationServiceTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Unit/Services/MatchSimulationServiceTest.php`:
```php
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
        // Statistical test: run 200 simulations between power=95 and power=50 team
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
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test --filter=MatchSimulationServiceTest
```
Expected: FAIL - class not found.

- [ ] **Step 3: Implement MatchSimulationService**

Create `app/Services/MatchSimulationService.php`:
```php
<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\MatchResult;
use App\Models\Team;

class MatchSimulationService
{
    /**
     * Simulate all matches for a given week.
     * Skips already played weeks.
     */
    public function simulateWeek(int $week): array
    {
        $fixtures = Fixture::with(['homeTeam', 'awayTeam', 'result'])
            ->where('week', $week)
            ->get();

        $results = [];

        foreach ($fixtures as $fixture) {
            if ($fixture->result && $fixture->result->is_played) {
                $results[] = $fixture->result;
                continue;
            }

            $scores = $this->simulateMatch($fixture->homeTeam, $fixture->awayTeam, true);

            $matchResult = $fixture->result;
            $matchResult->update([
                'home_score' => $scores['home_score'],
                'away_score' => $scores['away_score'],
                'is_played' => true,
            ]);

            $results[] = $matchResult->fresh();
        }

        return $results;
    }

    /**
     * Simulate all remaining unplayed weeks.
     */
    public function simulateAllRemaining(): array
    {
        $allResults = [];
        $totalWeeks = Fixture::max('week');

        for ($week = 1; $week <= $totalWeeks; $week++) {
            $allResults[$week] = $this->simulateWeek($week);
        }

        return $allResults;
    }

    /**
     * Get the next unplayed week number.
     * Returns null if all weeks are played.
     */
    public function getCurrentWeek(): ?int
    {
        $playedWeeks = MatchResult::where('is_played', true)
            ->join('fixtures', 'match_results.fixture_id', '=', 'fixtures.id')
            ->select('fixtures.week')
            ->distinct()
            ->pluck('week');

        $totalWeeks = Fixture::max('week');

        if ($totalWeeks === null) {
            return null;
        }

        for ($week = 1; $week <= $totalWeeks; $week++) {
            if (!$playedWeeks->contains($week)) {
                return $week;
            }
        }

        return null; // all played
    }

    /**
     * Simulate a single match between two teams.
     * Returns ['home_score' => int, 'away_score' => int].
     *
     * Algorithm:
     * 1. Calculate effective power: base power * home_advantage (if home)
     * 2. Win probability from power ratio
     * 3. Generate goals using Poisson-like distribution weighted by power
     * 4. Goalkeeper factor reduces opponent's goals
     */
    public function simulateMatch(Team $home, Team $away, bool $applyHomeAdvantage = true): array
    {
        $homePower = $home->power;
        $awayPower = $away->power;

        // Apply home advantage
        if ($applyHomeAdvantage) {
            $homePower *= $home->home_advantage;
        }

        // Calculate expected goals based on power
        // Higher power = more expected goals scored, scaled to realistic range
        $homeExpectedGoals = ($homePower / ($homePower + $awayPower)) * 3.0;
        $awayExpectedGoals = ($awayPower / ($homePower + $awayPower)) * 3.0;

        // Apply goalkeeper factor (reduces opponent's expected goals)
        $homeExpectedGoals *= (1 - $away->goalkeeper_factor * 0.3);
        $awayExpectedGoals *= (1 - $home->goalkeeper_factor * 0.3);

        // Ensure minimum expected goals
        $homeExpectedGoals = max(0.3, $homeExpectedGoals);
        $awayExpectedGoals = max(0.3, $awayExpectedGoals);

        // Generate goals using Poisson distribution
        $homeScore = $this->poissonRandom($homeExpectedGoals);
        $awayScore = $this->poissonRandom($awayExpectedGoals);

        return [
            'home_score' => min($homeScore, 8),
            'away_score' => min($awayScore, 8),
        ];
    }

    /**
     * Generate a Poisson-distributed random number.
     * This naturally produces realistic football scores.
     */
    private function poissonRandom(float $lambda): int
    {
        $L = exp(-$lambda);
        $k = 0;
        $p = 1.0;

        do {
            $k++;
            $p *= mt_rand() / mt_getrandmax();
        } while ($p > $L);

        return $k - 1;
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
php artisan test --filter=MatchSimulationServiceTest
```
Expected: All 6 tests PASS.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "feat: add match simulation with Poisson distribution and team power"
```

---

## Task 5: League Table Service

**Files:**
- Create: `app/Services/LeagueTableService.php`
- Test: `tests/Unit/Services/LeagueTableServiceTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Unit/Services/LeagueTableServiceTest.php`:
```php
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

        $this->assertEquals(2, $homeStanding['goal_difference']);   // 3-1
        $this->assertEquals(-2, $awayStanding['goal_difference']);  // 1-3
    }

    public function test_standings_sorted_by_points_then_goal_difference(): void
    {
        $teams = $this->seedTeams();

        // Team 0 beats Team 1: 3-0  (3 pts, +3 GD)
        $f1 = Fixture::create(['week' => 1, 'home_team_id' => $teams[0]->id, 'away_team_id' => $teams[1]->id]);
        MatchResult::create(['fixture_id' => $f1->id, 'home_score' => 3, 'away_score' => 0, 'is_played' => true]);

        // Team 2 beats Team 3: 1-0  (3 pts, +1 GD)
        $f2 = Fixture::create(['week' => 1, 'home_team_id' => $teams[2]->id, 'away_team_id' => $teams[3]->id]);
        MatchResult::create(['fixture_id' => $f2->id, 'home_score' => 1, 'away_score' => 0, 'is_played' => true]);

        $standings = $this->service->getStandings();

        // Both have 3 points, but team 0 has better GD
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
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test --filter=LeagueTableServiceTest
```
Expected: FAIL.

- [ ] **Step 3: Implement LeagueTableService**

Create `app/Services/LeagueTableService.php`:
```php
<?php

namespace App\Services;

use App\Models\MatchResult;
use App\Models\Team;

class LeagueTableService
{
    /**
     * Calculate league standings from all played match results.
     * Returns array sorted by: points DESC, goal_difference DESC, goals_for DESC.
     */
    public function getStandings(): array
    {
        $teams = Team::all();
        $standings = [];

        foreach ($teams as $team) {
            $standings[$team->id] = [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'points' => 0,
            ];
        }

        $results = MatchResult::with('fixture')
            ->where('is_played', true)
            ->get();

        foreach ($results as $result) {
            $homeId = $result->fixture->home_team_id;
            $awayId = $result->fixture->away_team_id;
            $homeScore = $result->home_score;
            $awayScore = $result->away_score;

            // Played
            $standings[$homeId]['played']++;
            $standings[$awayId]['played']++;

            // Goals
            $standings[$homeId]['goals_for'] += $homeScore;
            $standings[$homeId]['goals_against'] += $awayScore;
            $standings[$awayId]['goals_for'] += $awayScore;
            $standings[$awayId]['goals_against'] += $homeScore;

            // Points
            if ($homeScore > $awayScore) {
                $standings[$homeId]['won']++;
                $standings[$homeId]['points'] += 3;
                $standings[$awayId]['lost']++;
            } elseif ($homeScore < $awayScore) {
                $standings[$awayId]['won']++;
                $standings[$awayId]['points'] += 3;
                $standings[$homeId]['lost']++;
            } else {
                $standings[$homeId]['drawn']++;
                $standings[$awayId]['drawn']++;
                $standings[$homeId]['points'] += 1;
                $standings[$awayId]['points'] += 1;
            }
        }

        // Calculate GD
        foreach ($standings as &$row) {
            $row['goal_difference'] = $row['goals_for'] - $row['goals_against'];
        }

        // Sort: points DESC, GD DESC, GF DESC
        usort($standings, function ($a, $b) {
            if ($a['points'] !== $b['points']) return $b['points'] - $a['points'];
            if ($a['goal_difference'] !== $b['goal_difference']) return $b['goal_difference'] - $a['goal_difference'];
            return $b['goals_for'] - $a['goals_for'];
        });

        return array_values($standings);
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
php artisan test --filter=LeagueTableServiceTest
```
Expected: All 6 tests PASS.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "feat: add league table service with Premier League scoring rules"
```

---

## Task 6: Prediction Service (Monte Carlo)

**Files:**
- Create: `app/Services/PredictionService.php`
- Test: `tests/Unit/Services/PredictionServiceTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Unit/Services/PredictionServiceTest.php`:
```php
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
        $this->service = new PredictionService(new MatchSimulationService());
        $this->seedTeams();
        (new FixtureGeneratorService())->generate();
    }

    public function test_predictions_return_all_teams(): void
    {
        // Play first 4 weeks
        $sim = new MatchSimulationService();
        for ($w = 1; $w <= 4; $w++) {
            $sim->simulateWeek($w);
        }

        $predictions = $this->service->predict();
        $this->assertCount(4, $predictions);
    }

    public function test_predictions_sum_to_100(): void
    {
        $sim = new MatchSimulationService();
        for ($w = 1; $w <= 4; $w++) {
            $sim->simulateWeek($w);
        }

        $predictions = $this->service->predict();
        $total = array_sum(array_column($predictions, 'percentage'));

        // Allow small rounding tolerance
        $this->assertGreaterThanOrEqual(99, $total);
        $this->assertLessThanOrEqual(101, $total);
    }

    public function test_team_with_insurmountable_lead_has_100_percent(): void
    {
        // Manually create a scenario: team 1 has 15 pts, others have 0, 1 week left
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

    public function test_returns_zero_for_all_before_week_4(): void
    {
        // No weeks played
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
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test --filter=PredictionServiceTest
```
Expected: FAIL.

- [ ] **Step 3: Implement PredictionService**

Create `app/Services/PredictionService.php`:
```php
<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\MatchResult;
use App\Models\Team;

class PredictionService
{
    private const SIMULATIONS = 1000;

    public function __construct(
        private MatchSimulationService $matchSimulator
    ) {}

    /**
     * Monte Carlo championship prediction.
     * Simulates remaining matches N times and counts how often each team finishes first.
     * Only active from week 4 onwards (last 3 weeks of 6-week league).
     */
    public function predict(): array
    {
        $teams = Team::all();
        $totalWeeks = Fixture::max('week') ?? 0;

        // Determine how many weeks are played
        $playedWeeks = MatchResult::where('is_played', true)
            ->join('fixtures', 'match_results.fixture_id', '=', 'fixtures.id')
            ->distinct('fixtures.week')
            ->count('fixtures.week');

        // Only predict from week 4 onwards
        if ($playedWeeks < 4) {
            return $teams->map(fn (Team $t) => [
                'team_id' => $t->id,
                'team_name' => $t->name,
                'percentage' => 0,
            ])->toArray();
        }

        // Get current real standings (points)
        $currentPoints = $this->getCurrentPoints($teams);

        // Get remaining (unplayed) fixtures
        $remainingFixtures = Fixture::with(['homeTeam', 'awayTeam', 'result'])
            ->whereHas('result', fn ($q) => $q->where('is_played', false))
            ->get();

        // If no remaining fixtures, whoever is top gets 100%
        if ($remainingFixtures->isEmpty()) {
            return $this->deterministic($teams, $currentPoints);
        }

        // Monte Carlo: simulate remaining matches N times
        $winCounts = array_fill_keys($teams->pluck('id')->toArray(), 0);

        for ($sim = 0; $sim < self::SIMULATIONS; $sim++) {
            $simPoints = $currentPoints;

            foreach ($remainingFixtures as $fixture) {
                $result = $this->matchSimulator->simulateMatch(
                    $fixture->homeTeam,
                    $fixture->awayTeam,
                    true
                );

                if ($result['home_score'] > $result['away_score']) {
                    $simPoints[$fixture->home_team_id] += 3;
                } elseif ($result['home_score'] < $result['away_score']) {
                    $simPoints[$fixture->away_team_id] += 3;
                } else {
                    $simPoints[$fixture->home_team_id] += 1;
                    $simPoints[$fixture->away_team_id] += 1;
                }
            }

            // Find winner(s) - highest points
            $maxPoints = max($simPoints);
            $winners = array_keys(array_filter($simPoints, fn ($p) => $p === $maxPoints));

            // Distribute among tied winners
            foreach ($winners as $winnerId) {
                $winCounts[$winnerId] += 1 / count($winners);
            }
        }

        return $teams->map(fn (Team $t) => [
            'team_id' => $t->id,
            'team_name' => $t->name,
            'percentage' => (int) round(($winCounts[$t->id] / self::SIMULATIONS) * 100),
        ])->toArray();
    }

    private function getCurrentPoints($teams): array
    {
        $points = [];
        foreach ($teams as $team) {
            $points[$team->id] = 0;
        }

        $playedResults = MatchResult::with('fixture')
            ->where('is_played', true)
            ->get();

        foreach ($playedResults as $result) {
            $homeId = $result->fixture->home_team_id;
            $awayId = $result->fixture->away_team_id;

            if ($result->home_score > $result->away_score) {
                $points[$homeId] += 3;
            } elseif ($result->home_score < $result->away_score) {
                $points[$awayId] += 3;
            } else {
                $points[$homeId] += 1;
                $points[$awayId] += 1;
            }
        }

        return $points;
    }

    /**
     * When all matches are played, give 100% to leader, 0% to rest.
     */
    private function deterministic($teams, array $points): array
    {
        $maxPoints = max($points);
        $leaders = array_keys(array_filter($points, fn ($p) => $p === $maxPoints));
        $share = (int) round(100 / count($leaders));

        return $teams->map(fn (Team $t) => [
            'team_id' => $t->id,
            'team_name' => $t->name,
            'percentage' => in_array($t->id, $leaders) ? $share : 0,
        ])->toArray();
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
php artisan test --filter=PredictionServiceTest
```
Expected: All 4 tests PASS.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "feat: add Monte Carlo championship prediction service"
```

---

## Task 7: Controllers + Routes

**Files:**
- Create: `app/Http/Controllers/TeamController.php`
- Create: `app/Http/Controllers/FixtureController.php`
- Create: `app/Http/Controllers/SimulationController.php`
- Create: `app/Http/Controllers/MatchController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/FixtureControllerTest.php`
- Test: `tests/Feature/SimulationControllerTest.php`
- Test: `tests/Feature/MatchControllerTest.php`

- [ ] **Step 1: Write feature tests**

Create `tests/Feature/FixtureControllerTest.php`:
```php
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
```

Create `tests/Feature/SimulationControllerTest.php`:
```php
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
        (new FixtureGeneratorService())->generate();
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
        $this->assertEquals(2, $played); // 2 matches in week 1
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
```

Create `tests/Feature/MatchControllerTest.php`:
```php
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
        // Play week 1 first
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
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test --filter=ControllerTest
```
Expected: FAIL.

- [ ] **Step 3: Implement TeamController**

Create `app/Http/Controllers/TeamController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Teams/Index', [
            'teams' => Team::all(['id', 'name', 'power']),
        ]);
    }
}
```

- [ ] **Step 4: Implement FixtureController**

Create `app/Http/Controllers/FixtureController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Services\FixtureGeneratorService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FixtureController extends Controller
{
    public function __construct(
        private FixtureGeneratorService $fixtureGenerator
    ) {}

    public function index(): Response
    {
        $fixtures = Fixture::with(['homeTeam:id,name', 'awayTeam:id,name'])
            ->orderBy('week')
            ->get()
            ->groupBy('week');

        return Inertia::render('Fixtures/Index', [
            'fixturesByWeek' => $fixtures,
        ]);
    }

    public function generate(): RedirectResponse
    {
        $this->fixtureGenerator->generate();

        return redirect('/fixtures');
    }
}
```

- [ ] **Step 5: Implement SimulationController**

Create `app/Http/Controllers/SimulationController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\MatchResult;
use App\Services\LeagueTableService;
use App\Services\MatchSimulationService;
use App\Services\PredictionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SimulationController extends Controller
{
    public function __construct(
        private MatchSimulationService $matchSimulator,
        private LeagueTableService $leagueTable,
        private PredictionService $predictionService,
    ) {}

    public function index(): Response
    {
        $currentWeek = $this->matchSimulator->getCurrentWeek();
        $totalWeeks = Fixture::max('week') ?? 0;

        // If all played, show last week
        $displayWeek = $currentWeek ?? $totalWeeks;

        $weekFixtures = Fixture::with(['homeTeam:id,name', 'awayTeam:id,name', 'result'])
            ->where('week', $displayWeek)
            ->get();

        // For "play all" - get all weeks with results
        $allWeeksResults = Fixture::with(['homeTeam:id,name', 'awayTeam:id,name', 'result'])
            ->whereHas('result', fn ($q) => $q->where('is_played', true))
            ->orderBy('week')
            ->get()
            ->groupBy('week');

        return Inertia::render('Simulation/Index', [
            'standings' => $this->leagueTable->getStandings(),
            'currentWeek' => $displayWeek,
            'totalWeeks' => $totalWeeks,
            'weekFixtures' => $weekFixtures,
            'allWeeksResults' => $allWeeksResults,
            'predictions' => $this->predictionService->predict(),
            'isFinished' => $currentWeek === null,
        ]);
    }

    public function playNext(): RedirectResponse
    {
        $currentWeek = $this->matchSimulator->getCurrentWeek();
        if ($currentWeek !== null) {
            $this->matchSimulator->simulateWeek($currentWeek);
        }

        return redirect('/simulation');
    }

    public function playAll(): RedirectResponse
    {
        $this->matchSimulator->simulateAllRemaining();

        return redirect('/simulation');
    }

    public function reset(): RedirectResponse
    {
        MatchResult::query()->delete();
        Fixture::query()->delete();

        return redirect('/');
    }
}
```

- [ ] **Step 6: Implement MatchController**

Create `app/Http/Controllers/MatchController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Models\MatchResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function update(Request $request, MatchResult $matchResult): RedirectResponse
    {
        $validated = $request->validate([
            'home_score' => 'required|integer|min:0|max:99',
            'away_score' => 'required|integer|min:0|max:99',
        ]);

        $matchResult->update($validated);

        return redirect('/simulation');
    }
}
```

- [ ] **Step 7: Define routes**

Replace `routes/web.php`:
```php
<?php

use App\Http\Controllers\FixtureController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

// Screen 1: Teams
Route::get('/', [TeamController::class, 'index']);

// Screen 2: Fixtures
Route::get('/fixtures', [FixtureController::class, 'index']);
Route::post('/fixtures/generate', [FixtureController::class, 'generate']);

// Screen 3: Simulation
Route::get('/simulation', [SimulationController::class, 'index']);
Route::post('/simulation/play-next', [SimulationController::class, 'playNext']);
Route::post('/simulation/play-all', [SimulationController::class, 'playAll']);
Route::post('/simulation/reset', [SimulationController::class, 'reset']);

// Match editing (bonus)
Route::put('/matches/{matchResult}', [MatchController::class, 'update']);
```

- [ ] **Step 8: Run all tests**

```bash
php artisan test
```
Expected: All tests PASS.

- [ ] **Step 9: Commit**

```bash
git add -A
git commit -m "feat: add controllers, routes, and feature tests"
```

---

## Task 8: Frontend - Layout + Teams Page (Screen 1)

**Files:**
- Create: `resources/js/Layouts/AppLayout.vue`
- Modify: `resources/js/Pages/Teams/Index.vue`

- [ ] **Step 1: Create AppLayout**

Create `resources/js/Layouts/AppLayout.vue`:
```vue
<template>
    <div class="min-h-screen bg-gray-100">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <header class="mb-8 text-center">
                <h1 class="text-3xl font-light text-gray-500 tracking-wide">
                    <slot name="header">Champions League Simulator</slot>
                </h1>
            </header>
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
```

- [ ] **Step 2: Build Teams/Index page**

Replace `resources/js/Pages/Teams/Index.vue`:
```vue
<template>
    <AppLayout>
        <template #header>Tournament Teams</template>

        <div class="max-w-2xl mx-auto">
            <table class="w-full bg-white shadow rounded overflow-hidden">
                <thead>
                    <tr class="bg-gray-700 text-white">
                        <th class="py-3 px-6 text-left font-medium">Team Name</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="team in teams" :key="team.id"
                        class="border-b border-gray-200 last:border-0">
                        <td class="py-3 px-6 text-gray-800">{{ team.name }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-6">
                <button @click="generateFixtures"
                    :disabled="loading"
                    class="bg-teal-500 hover:bg-teal-600 text-white font-medium py-2 px-6 rounded
                           transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ loading ? 'Generating...' : 'Generate Fixtures' }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    teams: Array,
});

const loading = ref(false);

function generateFixtures() {
    loading.value = true;
    router.post('/fixtures/generate', {}, {
        onFinish: () => loading.value = false,
    });
}
</script>
```

- [ ] **Step 3: Add Vite alias for @ imports**

In `vite.config.js`, add resolve alias:
```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
});
```

- [ ] **Step 4: Verify in browser**

```bash
php artisan migrate:fresh --seed
php artisan serve &
npm run dev &
# Open http://localhost:8000 - should see 4 teams + Generate Fixtures button
```

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "feat: add teams page with layout (screen 1)"
```

---

## Task 9: Frontend - Fixtures Page (Screen 2)

**Files:**
- Create: `resources/js/Pages/Fixtures/Index.vue`

- [ ] **Step 1: Build Fixtures/Index page**

Create `resources/js/Pages/Fixtures/Index.vue`:
```vue
<template>
    <AppLayout>
        <template #header>Generated Fixtures</template>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div v-for="(fixtures, week) in fixturesByWeek" :key="week"
                 class="bg-white shadow rounded overflow-hidden">
                <div class="bg-gray-700 text-white py-2 px-4 font-bold">
                    Week {{ week }}
                </div>
                <div class="p-4 space-y-3">
                    <div v-for="fixture in fixtures" :key="fixture.id"
                         class="flex items-center justify-between text-gray-800">
                        <span class="flex-1 text-right pr-3">{{ fixture.home_team.name }}</span>
                        <span class="text-gray-400 px-2">-</span>
                        <span class="flex-1 pl-3">{{ fixture.away_team.name }}</span>
                    </div>
                </div>
            </div>
        </div>

        <button @click="startSimulation"
            class="bg-teal-500 hover:bg-teal-600 text-white font-medium py-2 px-6 rounded
                   transition-colors">
            Start Simulation
        </button>
    </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    fixturesByWeek: Object,
});

function startSimulation() {
    router.visit('/simulation');
}
</script>
```

- [ ] **Step 2: Verify in browser**

Click "Generate Fixtures" on the teams page. Should see 6 weeks of fixtures in a grid layout.

- [ ] **Step 3: Commit**

```bash
git add -A
git commit -m "feat: add fixtures page with weekly grid (screen 2)"
```

---

## Task 10: Frontend - Simulation Page (Screen 3)

**Files:**
- Create: `resources/js/Components/LeagueTable.vue`
- Create: `resources/js/Components/WeekResults.vue`
- Create: `resources/js/Components/ChampionshipPredictions.vue`
- Create: `resources/js/Components/MatchResultEditor.vue`
- Create: `resources/js/Pages/Simulation/Index.vue`

- [ ] **Step 1: Create LeagueTable component**

Create `resources/js/Components/LeagueTable.vue`:
```vue
<template>
    <div class="bg-white shadow rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-700 text-white">
                    <th class="py-2 px-3 text-left font-medium">Team Name</th>
                    <th class="py-2 px-3 text-center font-medium w-10">P</th>
                    <th class="py-2 px-3 text-center font-medium w-10">W</th>
                    <th class="py-2 px-3 text-center font-medium w-10">D</th>
                    <th class="py-2 px-3 text-center font-medium w-10">L</th>
                    <th class="py-2 px-3 text-center font-medium w-12">GD</th>
                    <th class="py-2 px-3 text-center font-medium w-12">PTS</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, index) in standings" :key="row.team_id"
                    class="border-b border-gray-100 last:border-0"
                    :class="{ 'bg-green-50': index === 0 && row.points > 0 }">
                    <td class="py-2 px-3 font-medium text-gray-800">{{ row.team_name }}</td>
                    <td class="py-2 px-3 text-center text-gray-600">{{ row.played }}</td>
                    <td class="py-2 px-3 text-center text-gray-600">{{ row.won }}</td>
                    <td class="py-2 px-3 text-center text-gray-600">{{ row.drawn }}</td>
                    <td class="py-2 px-3 text-center text-gray-600">{{ row.lost }}</td>
                    <td class="py-2 px-3 text-center text-gray-600">{{ row.goal_difference }}</td>
                    <td class="py-2 px-3 text-center font-bold text-gray-800">{{ row.points }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
defineProps({
    standings: Array,
});
</script>
```

- [ ] **Step 2: Create WeekResults component**

Create `resources/js/Components/WeekResults.vue`:
```vue
<template>
    <div class="bg-white shadow rounded overflow-hidden">
        <div class="bg-gray-700 text-white py-2 px-4 font-bold">
            Week {{ week }}
        </div>
        <div class="p-4 space-y-3">
            <div v-for="fixture in fixtures" :key="fixture.id"
                 class="flex items-center justify-between text-gray-800">
                <span class="flex-1 text-right pr-2 font-medium">{{ fixture.home_team.name }}</span>
                <div class="flex items-center gap-1 px-2 min-w-[60px] justify-center">
                    <template v-if="fixture.result?.is_played">
                        <span class="font-bold">{{ fixture.result.home_score }}</span>
                        <span class="text-gray-400">-</span>
                        <span class="font-bold">{{ fixture.result.away_score }}</span>
                    </template>
                    <span v-else class="text-gray-400">-</span>
                </div>
                <span class="flex-1 pl-2 font-medium">{{ fixture.away_team.name }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    week: Number,
    fixtures: Array,
});
</script>
```

- [ ] **Step 3: Create ChampionshipPredictions component**

Create `resources/js/Components/ChampionshipPredictions.vue`:
```vue
<template>
    <div class="bg-white shadow rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-red-600 text-white">
                    <th class="py-2 px-3 text-left font-medium">Championship Predictions</th>
                    <th class="py-2 px-3 text-right font-medium w-16">%</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="prediction in predictions" :key="prediction.team_id"
                    class="border-b border-gray-100 last:border-0">
                    <td class="py-2 px-3 text-gray-800">{{ prediction.team_name }}</td>
                    <td class="py-2 px-3 text-right font-bold"
                        :class="prediction.percentage > 0 ? 'text-red-600' : 'text-gray-400'">
                        {{ prediction.percentage }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
defineProps({
    predictions: Array,
});
</script>
```

- [ ] **Step 4: Create MatchResultEditor component (bonus feature)**

Create `resources/js/Components/MatchResultEditor.vue`:
```vue
<template>
    <div v-if="editing" class="flex items-center gap-1 px-2 min-w-[80px] justify-center">
        <input v-model.number="homeScore" type="number" min="0" max="99"
               class="w-10 text-center border rounded py-0.5 text-sm" />
        <span class="text-gray-400">-</span>
        <input v-model.number="awayScore" type="number" min="0" max="99"
               class="w-10 text-center border rounded py-0.5 text-sm" />
        <button @click="save" class="text-green-600 hover:text-green-800 ml-1" title="Save">
            &#10003;
        </button>
        <button @click="editing = false" class="text-red-500 hover:text-red-700" title="Cancel">
            &#10005;
        </button>
    </div>
    <div v-else class="flex items-center gap-1 px-2 min-w-[60px] justify-center cursor-pointer group"
         @click="startEdit">
        <template v-if="result?.is_played">
            <span class="font-bold">{{ result.home_score }}</span>
            <span class="text-gray-400">-</span>
            <span class="font-bold">{{ result.away_score }}</span>
            <span class="text-gray-300 opacity-0 group-hover:opacity-100 ml-1 text-xs">&#9998;</span>
        </template>
        <span v-else class="text-gray-400">-</span>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    result: Object,
});

const editing = ref(false);
const homeScore = ref(0);
const awayScore = ref(0);

function startEdit() {
    if (!props.result?.is_played) return;
    homeScore.value = props.result.home_score;
    awayScore.value = props.result.away_score;
    editing.value = true;
}

function save() {
    router.put(`/matches/${props.result.id}`, {
        home_score: homeScore.value,
        away_score: awayScore.value,
    }, {
        onSuccess: () => editing.value = false,
    });
}
</script>
```

- [ ] **Step 5: Build Simulation/Index page**

Create `resources/js/Pages/Simulation/Index.vue`:
```vue
<template>
    <AppLayout>
        <template #header>Simulation</template>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- League Table -->
            <div class="lg:col-span-5">
                <LeagueTable :standings="standings" />
            </div>

            <!-- Week Results -->
            <div class="lg:col-span-4">
                <div class="bg-white shadow rounded overflow-hidden">
                    <div class="bg-gray-700 text-white py-2 px-4 font-bold">
                        Week {{ currentWeek }}
                    </div>
                    <div class="p-4 space-y-3">
                        <div v-for="fixture in weekFixtures" :key="fixture.id"
                             class="flex items-center justify-between text-gray-800">
                            <span class="flex-1 text-right pr-2 font-medium">
                                {{ fixture.home_team.name }}
                            </span>
                            <MatchResultEditor :result="fixture.result" />
                            <span class="flex-1 pl-2 font-medium">
                                {{ fixture.away_team.name }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- All weeks results (shown after Play All) -->
                <div v-if="Object.keys(allWeeksResults).length > 1" class="mt-4 space-y-3">
                    <div v-for="(fixtures, week) in allWeeksResults" :key="week">
                        <WeekResults v-if="Number(week) !== currentWeek"
                                     :week="Number(week)" :fixtures="fixtures" />
                    </div>
                </div>
            </div>

            <!-- Championship Predictions -->
            <div class="lg:col-span-3">
                <ChampionshipPredictions :predictions="predictions" />
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between mt-8">
            <button @click="playAll"
                :disabled="isFinished"
                class="bg-teal-500 hover:bg-teal-600 text-white font-medium py-2 px-6 rounded
                       transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Play All Weeks
            </button>

            <button @click="playNext"
                :disabled="isFinished"
                class="bg-teal-500 hover:bg-teal-600 text-white font-medium py-2 px-6 rounded
                       transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Play Next Week
            </button>

            <button @click="resetData"
                class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-6 rounded
                       transition-colors">
                Reset Data
            </button>
        </div>
    </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import LeagueTable from '@/Components/LeagueTable.vue';
import WeekResults from '@/Components/WeekResults.vue';
import ChampionshipPredictions from '@/Components/ChampionshipPredictions.vue';
import MatchResultEditor from '@/Components/MatchResultEditor.vue';

defineProps({
    standings: Array,
    currentWeek: Number,
    totalWeeks: Number,
    weekFixtures: Array,
    allWeeksResults: Object,
    predictions: Array,
    isFinished: Boolean,
});

function playNext() {
    router.post('/simulation/play-next');
}

function playAll() {
    router.post('/simulation/play-all');
}

function resetData() {
    if (confirm('Are you sure you want to reset all data?')) {
        router.post('/simulation/reset');
    }
}
</script>
```

- [ ] **Step 6: Verify full flow in browser**

```bash
php artisan migrate:fresh --seed
php artisan serve &
npm run dev &
```

Test the complete flow:
1. Visit `/` - see 4 teams, click Generate Fixtures
2. See 6 weeks of fixtures, click Start Simulation
3. Click Play Next Week repeatedly, verify table updates
4. After week 4, verify predictions appear
5. Click on a score to edit it (bonus feature)
6. Click Reset Data
7. Click Play All Weeks to test auto-play

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "feat: add simulation page with league table, results, and predictions (screen 3)"
```

---

## Task 11: Docker Setup (Sail)

**Files:**
- Modify: `docker-compose.yml`
- Create: `Dockerfile` (if needed for production)

- [ ] **Step 1: Verify Sail docker-compose.yml is correct**

The `sail:install` from Task 1 should have created a working `docker-compose.yml`. Verify it has:
- PHP 8.1 service (laravel.test)
- Correct port mapping (80:80)
- Volume mount for the project

If not already present, ensure `docker-compose.yml` contains:
```yaml
services:
    laravel.test:
        build:
            context: ./vendor/laravel/sail/runtimes/8.1
            dockerfile: Dockerfile
            args:
                WWWGROUP: '${WWWGROUP}'
        image: sail-8.1/app
        extra_hosts:
            - 'host.docker.internal:host-gateway'
        ports:
            - '${APP_PORT:-80}:80'
            - '${VITE_PORT:-5173}:${VITE_PORT:-5173}'
        environment:
            WWWUSER: '${WWWUSER}'
            LARAVEL_SAIL: 1
            XDEBUG_MODE: '${SAIL_XDEBUG_MODE:-off}'
        volumes:
            - '.:/var/www/html'
        networks:
            - sail
networks:
    sail:
        driver: bridge
```

- [ ] **Step 2: Test with Sail**

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail npm run build
```

Visit `http://localhost` to verify.

- [ ] **Step 3: Commit**

```bash
git add -A
git commit -m "chore: configure Docker via Laravel Sail"
```

---

## Task 12: Final Polish + Run All Tests

**Files:**
- All test files

- [ ] **Step 1: Run full test suite**

```bash
php artisan test
```

Expected: All unit + feature tests PASS.

- [ ] **Step 2: Build assets for production**

```bash
npm run build
```

Expected: No errors, assets compiled to `public/build/`.

- [ ] **Step 3: Final commit**

```bash
git add -A
git commit -m "chore: production build and final polish"
```

- [ ] **Step 4: Push to GitHub**

```bash
git remote add origin <github-repo-url>
git branch -M main
git push -u origin main
```
