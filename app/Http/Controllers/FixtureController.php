<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\FixtureRepositoryInterface;
use App\Services\FixtureGeneratorService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FixtureController extends Controller
{
    public function __construct(
        private FixtureGeneratorService $fixtureGenerator,
        private FixtureRepositoryInterface $fixtureRepo,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Fixtures/Index', [
            'fixturesByWeek' => $this->fixtureRepo->getByWeekWithTeams(),
        ]);
    }

    public function generate(): RedirectResponse
    {
        $this->fixtureGenerator->generate();
        return redirect('/fixtures');
    }
}
