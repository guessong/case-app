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
