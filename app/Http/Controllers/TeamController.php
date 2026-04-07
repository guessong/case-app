<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Fixture;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Teams/Index', [
            'teams' => Team::all(),
        ]);
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Team::create([
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

        $team->update([
            'name' => $validated['name'],
            'power' => $validated['power'],
            'home_advantage' => 1.0 + ($validated['power'] / 500),
            'goalkeeper_factor' => 0.5 + ($validated['power'] / 250),
        ]);

        return redirect('/');
    }

    public function destroy(Team $team): RedirectResponse
    {
        if (Fixture::where('home_team_id', $team->id)->orWhere('away_team_id', $team->id)->exists()) {
            return redirect('/')->withErrors(['team' => 'Cannot delete team with active fixtures. Reset data first.']);
        }

        $team->delete();

        return redirect('/');
    }
}
