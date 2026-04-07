<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\FixtureRepositoryInterface;
use App\Contracts\Repositories\TeamRepositoryInterface;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function __construct(
        private TeamRepositoryInterface $teamRepo,
        private FixtureRepositoryInterface $fixtureRepo,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Teams/Index', [
            'teams' => $this->teamRepo->all(),
        ]);
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->teamRepo->create([
            'name' => $validated['name'],
            'power' => $validated['power'],
            'home_advantage' => 1.0 + ($validated['power'] / 500),
            'goalkeeper_factor' => 0.5 + ($validated['power'] / 250),
        ]);

        return redirect('/');
    }

    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $validated = $request->validated();

        $this->teamRepo->update($team, [
            'name' => $validated['name'],
            'power' => $validated['power'],
            'home_advantage' => 1.0 + ($validated['power'] / 500),
            'goalkeeper_factor' => 0.5 + ($validated['power'] / 250),
        ]);

        return redirect('/');
    }

    public function destroy(Team $team): RedirectResponse
    {
        if ($this->fixtureRepo->hasTeam($team->id)) {
            return redirect('/')->withErrors(['team' => 'Cannot delete team with active fixtures. Reset data first.']);
        }

        $this->teamRepo->delete($team);

        return redirect('/');
    }
}
