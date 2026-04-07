<?php

namespace App\Services;

use App\Models\MatchResult;
use App\Models\Team;

class LeagueTableService
{
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

            $standings[$homeId]['played']++;
            $standings[$awayId]['played']++;

            $standings[$homeId]['goals_for'] += $homeScore;
            $standings[$homeId]['goals_against'] += $awayScore;
            $standings[$awayId]['goals_for'] += $awayScore;
            $standings[$awayId]['goals_against'] += $homeScore;

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

        foreach ($standings as &$row) {
            $row['goal_difference'] = $row['goals_for'] - $row['goals_against'];
        }

        usort($standings, function ($a, $b) {
            if ($a['points'] !== $b['points']) return $b['points'] - $a['points'];
            if ($a['goal_difference'] !== $b['goal_difference']) return $b['goal_difference'] - $a['goal_difference'];
            return $b['goals_for'] - $a['goals_for'];
        });

        return array_values($standings);
    }
}
