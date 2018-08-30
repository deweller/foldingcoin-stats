<?php

namespace App\Libraries\StatsDownload;

use App\Libraries\StatsDownload\StatsDownloadAPIClient;
use App\Repositories\FoldingTeamRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tokenly\LaravelEventLog\Facade\EventLog;

/**
 * TeamSynchronizer
 */
class TeamSynchronizer
{

    public function __construct(StatsDownloadAPIClient $stats_api_client, FoldingTeamRepository $folding_team_repository)
    {
        $this->stats_api_client = $stats_api_client;
        $this->folding_team_repository = $folding_team_repository;
    }

    public function synchronizeTeamsData()
    {
        
        $existing_teams_by_number = $this->folding_team_repository->allTeamAttributesByTeamNumber();
        $new_teams_by_number = collect($this->stats_api_client->getTeams()['teams'])->keyBy('teamNumber');

        // determine create, update and delete team numbers
        $team_numbers_to_create = $new_teams_by_number->diffKeys($existing_teams_by_number)->keys();
        $team_numbers_to_update = $existing_teams_by_number->intersectByKeys($new_teams_by_number)->keys();
        $existing_team_numbers_to_delete = $existing_teams_by_number->diffKeys($new_teams_by_number)->keys();


        // ---------------
        // create

        foreach ($team_numbers_to_create as $team_number) {
            $new_team_vars = $this->normalizeTeamVarsFromApi($new_teams_by_number[$team_number]);

            $team = $this->folding_team_repository->create([
                'number' => $new_team_vars['teamNumber'],
                'name' => $new_team_vars['teamName'],
                'name_lc' => strtolower($new_team_vars['teamName']),
            ]);
            EventLog::debug('team.created', [
                'number' => $team['number'],
                'name' => $team['name'],
            ]);
        }

        // ---------------
        // update

        foreach ($team_numbers_to_update as $team_number) {
            $new_team_vars = $this->normalizeTeamVarsFromApi($new_teams_by_number[$team_number]);
            $team = $existing_teams_by_number[$team_number];

            if (
                (string) $new_team_vars['teamNumber'] != (string) $team->number
                or (string) $new_team_vars['teamName'] != (string) $team->name
            ) {
                // is different
                $update_vars = [
                    'number' => $new_team_vars['teamNumber'],
                    'name' => $new_team_vars['teamName'],
                    'name_lc' => strtolower($new_team_vars['teamName']),
                ];

                $team_model = $this->folding_team_repository->findByTeamNumber($team->number);
                $this->folding_team_repository->update($team_model, $update_vars);
                EventLog::debug('team.updated', [
                    'number' => $team_model['number'],
                    'name' => $team_model['name'],
                ]);
            }
        }

        // ---------------
        // delete

        foreach ($existing_team_numbers_to_delete as $team_number) {
            $team = $existing_teams_by_number[$team_number];
            $team_model = $this->folding_team_repository->findByTeamNumber($team->number);
            $this->folding_team_repository->delete($team_model);
            EventLog::debug('team.deleted', [
                'number' => $team->number,
                'name' => $team->name,
            ]);
        }


    }

    protected function normalizeTeamVarsFromApi($team_vars)
    {
        if (!isset($team_vars['teamNumber'])) {
            throw new Exception("team number not found for team vars: ".json_encode($team_vars), 1);
        }

        $team_vars['teamName'] = $team_vars['teamName'] ?? "Team {$team_vars['teamNumber']}";

        return $team_vars;
    }

}
