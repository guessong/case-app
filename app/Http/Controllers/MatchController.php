<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMatchResultRequest;
use App\Models\MatchResult;
use Illuminate\Http\RedirectResponse;

class MatchController extends Controller
{
    public function update(UpdateMatchResultRequest $request, MatchResult $matchResult): RedirectResponse
    {
        if (!$matchResult->is_played) {
            abort(422, 'Cannot edit an unplayed match.');
        }

        $matchResult->update($request->validated());
        return redirect('/simulation');
    }
}
