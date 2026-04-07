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
