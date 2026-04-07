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
