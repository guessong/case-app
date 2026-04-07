<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name',
            'power' => 'required|integer|min:1|max:100',
        ]);

        Team::create([
            'name' => $validated['name'],
            'power' => $validated['power'],
            'home_advantage' => 1.0 + ($validated['power'] / 500),
            'goalkeeper_factor' => 0.5 + ($validated['power'] / 250),
        ]);

        return redirect('/');
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name,' . $team->id,
            'power' => 'required|integer|min:1|max:100',
        ]);

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
        $team->delete();

        return redirect('/');
    }
}
