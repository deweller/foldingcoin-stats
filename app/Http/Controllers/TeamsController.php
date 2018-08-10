<?php

namespace App\Http\Controllers;

use App\Repositories\FoldingTeamRepository;
use App\Repositories\TeamAggregateStatRepository;
use Illuminate\Support\Facades\Log;

class TeamsController extends Controller
{
    public function teams()
    {
        return view('teams.index');
    }

    public function showTeam($team_number, TeamAggregateStatRepository $team_aggregate_stat_repository, FoldingTeamRepository $folding_team_repository)
    {
        $team = $team_aggregate_stat_repository->findByTeamNumber($team_number);
        if (!$team) {
            return redirect()->route('teams.index')->withErrors("This username was not found.");
        }

        $team_for_api = [];
        foreach ($team as $team_key => $team_val) {
            $team_for_api[camel_case($team_key)] = $team_val;
        }
        unset($team_for_api['updatedAt']);

        Log::debug("\$team_for_api=".json_encode($team_for_api, 192));
        return view('teams.show', [
            'team' => $team_for_api,
        ]);
    }

}
