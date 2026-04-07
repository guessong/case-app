# Insider One Champions League Simulator

A football league simulation case study built for **Insider One**. Teams compete in a full round-robin tournament (home & away). The app simulates matches using a Poisson-distribution engine, tracks standings by Premier League rules, and forecasts championship probability via Monte Carlo simulation.

---

## Features

- **Team Management** — Add, edit, and delete teams with configurable power ratings (1–100)
- **Fixture Generation** — Automatic round-robin schedule (circle method), home & away legs
- **Week-by-Week Simulation** — Play one matchweek at a time or simulate the entire season at once
- **League Standings** — Points, goal difference, goals scored; sorted by Premier League tiebreak rules (3 pts win, 1 pt draw)
- **Championship Predictions** — Monte Carlo simulation (1,000 runs) showing each team's win probability; activates in the final 3 matchweeks
- **Manual Result Editing** — Override any match result; standings and predictions recalculate instantly
- **Simulation Reset** — Clear all results and restart from scratch

---

## Tech Stack

| Layer      | Technology                        |
|------------|-----------------------------------|
| Backend    | PHP 8.1, Laravel 10               |
| Frontend   | Vue 3 (Composition API), Inertia.js |
| Styling    | Tailwind CSS v4                   |
| Database   | MySQL 8.0                         |
| Dev Environment | Docker                       |

---

## Quick Start (Docker)

**Prerequisites:** Docker Desktop running.

```bash
git clone https://github.com/guessong/simulator-case-app.git caseapp
cd caseapp
make setup
```

That's it. App is running at **http://localhost:8000**.

Other commands: `make stop`, `make clean`.

---

## Installation (Local, without Docker)

**Prerequisites:** PHP 8.1+, Composer, Node.js 18+, MySQL 8.0.

```bash
# 1. Clone and enter the project
git clone https://github.com/guessong/simulator-case-app.git caseapp
cd caseapp

# 2. Install dependencies
composer install
npm install

# 3. Configure environment
cp .env.example .env
# Update DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD for your MySQL instance

# 4. Generate app key and run migrations
php artisan key:generate
php artisan migrate --seed

# 5. Build frontend assets
npm run build

# 6. Serve the application
php artisan serve
```

App is available at **http://localhost:8000**.

---

## Running Tests

```bash
# Docker / Sail
./vendor/bin/sail artisan test

# Local
php artisan test
```

The test suite covers the core service layer: fixture generation correctness, match simulation output validity, standings calculation, and Monte Carlo prediction bounds.

---

## Architecture

The application follows a thin-controller / rich-service pattern. Controllers handle HTTP concerns only; all business logic lives in four dedicated services:

```
app/Services/
├── FixtureGeneratorService.php   — Round-robin schedule builder
├── MatchSimulationService.php    — Poisson-based match engine
├── LeagueTableService.php        — Standings aggregation & sorting
└── PredictionService.php         — Monte Carlo championship forecast
```

Inertia.js bridges Laravel and Vue 3 without a separate API layer — server responses are JSON page objects rendered client-side by Vue components. No REST endpoints are exposed publicly.

---

## Algorithm Highlights

### Match Simulation — Poisson Distribution

Each team carries a **power rating** (1–100). For a given fixture:

1. **Expected goals** for each team are derived from the power ratio against the combined total:
   ```
   expectedGoals = (teamPower / (homeTeamPower + awayTeamPower)) * 3.0
   ```
2. The **home advantage multiplier** boosts the home team's effective power before this calculation.
3. The opposing team's **goalkeeper factor** scales down the attacker's expected goals.
4. Actual goals are sampled independently for each team from a **Poisson distribution** with the computed lambda — producing realistic, low-scoring football results with natural variance.

### Championship Prediction — Monte Carlo Simulation

Active during the final 3 matchweeks (when meaningful differentiation is possible):

1. **1,000 independent simulations** are run over all remaining fixtures.
2. Each simulation uses the same Poisson match engine, so results reflect actual team strengths.
3. The team finishing first in each run earns a win count; ties are split fractionally.
4. Final probability = `(wins in 1000 runs) / 1000`, expressed as a percentage.
5. Once all matches are played, predictions switch to a **deterministic result** derived from actual standings (points → goal difference → goals scored).

### Fixture Generation — Circle Method

A standard round-robin scheduling algorithm:

1. One team is fixed; the remaining `n-1` teams rotate around it each round.
2. First half of the season: home/away assigned by position in the rotation.
3. Second half: home/away reversed to guarantee every team plays each opponent once at home and once away.
4. Odd numbers of teams are handled with a bye week (virtual placeholder team).
