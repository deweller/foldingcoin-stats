<?php

namespace App\Repositories;

use Exception;
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
                'number' => $team_number,
            ]);
        });

    }


}
