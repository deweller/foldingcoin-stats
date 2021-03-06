<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tokenly\LaravelApiProvider\Repositories\BaseRepository;

/*
 * FoldingTeamRepository
 */
class FoldingTeamRepository extends BaseRepository
{
    protected $model_type = 'App\Models\FoldingTeam';

    // returns a collection with only the number and name
    public function allTeamAttributesByTeamNumber()
    {
        $collection = DB::table($this->prototype_model->getTable())
            ->select(['name', 'number'])
            ->get();

        return $collection->keyBy('number');
    }

    public function findByTeamNumber($team_number)
    {
        return $this->prototype_model
            ->where('number', '=', $team_number)
            ->first();
    }

    public function findOrCreateTeamByTeamNumber($team_number)
    {
        return DB::transaction(function () use ($team_number) {
            $existing_model = $this->findByTeamNumber($team_number);
            if ($existing_model) {
                return $existing_model;
            }

            Log::debug("team number $team_number not found");

            return $this->create([
                'name' => '',
                'name_lc' => '',
                'number' => $team_number,
            ]);
        });

    }

    public function findAllTeamNumberAndNamesForUsername($user_name)
    {
        return $this->findAllTeamsForUsername($user_name, ['folding_teams.name', 'folding_teams.number']);
    }

    public function findAllTeamsForUsername($user_name, $fields = 'folding_teams.*')
    {
        $collection = DB::table($this->prototype_model->getTable())
            ->select($fields)
            ->join('folding_members', 'folding_teams.id', '=', 'folding_members.team_id')
            ->where('folding_members.user_name', '=', $user_name)
            ->get();

        return $collection;
    }

    public function deleteAll() {
        DB::table($this->prototype_model->getTable())
            ->delete();
    }


}
