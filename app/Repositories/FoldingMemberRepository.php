<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Tokenly\LaravelApiProvider\Repositories\BaseRepository;

/*
 * FoldingMemberRepository
 */
class FoldingMemberRepository extends BaseRepository
{

    protected $model_type = 'App\Models\FoldingMember';

    // returns a collection with only the number and name
    public function allMemberAttributesByUniqueKey()
    {
        $collection = DB::table($this->prototype_model->getTable())
            ->select(['user_name', 'friendly_name', 'bitcoin_address', 'team_id', 'team_number'])
            ->get();

        return $collection->keyBy(function ($m) {
            return $m->user_name . '|' . $m->team_number;
        });
    }

    public function allMemberAndTeamIdsByUniqueKey()
    {
        $collection = DB::table($this->prototype_model->getTable())
            ->select(['user_name', 'team_number', 'id', 'team_id'])
            ->get();

        return $collection->mapWithKeys(function ($m) {
            return [$m->user_name . '|' . $m->team_number => [
                'id' => $m->id,
                'team_id' => $m->team_id,
            ]];
        });
    }

    public function findByUsernameAndTeamNumber($user_name, $team_number)
    {
        return $this->prototype_model
            ->where('user_name', '=', $user_name)
            ->where('team_number', '=', $team_number)
            ->first();
    }

    public function lookupAllIdsForUsername($user_name)
    {
        $collection = DB::table($this->prototype_model->getTable())
            ->select(['id'])
            ->where('user_name', '=', $user_name)
            ->get();

        return $collection->pluck('id')->toArray();
    }

    public function lookupAllIdsForTeamId($team_id)
    {
        $collection = DB::table($this->prototype_model->getTable())
            ->select(['id'])
            ->where('team_id', '=', $team_id)
            ->get();

        return $collection->pluck('id')->toArray();
    }

}

// $table->string('user_name', 80)->index();
// $table->string('friendly_name');
// $table->string('bitcoin_address')->index();
// $table->integer('team_id')->unsigned()->index();
// $table->string('team_number', 16); // denormalized for efficiency
