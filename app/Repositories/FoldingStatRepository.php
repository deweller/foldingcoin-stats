<?php

namespace App\Repositories;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tokenly\LaravelApiProvider\Repositories\BaseRepository;

/*
 * FoldingStatRepository
 */
class FoldingStatRepository extends BaseRepository
{

    protected $model_type = 'App\Models\FoldingStat';

    public function allEntryObjectsByDateAndPeriod(Carbon $start_date, $period_type)
    {
        $collection = DB::table($this->prototype_model->getTable())
            ->select()
            ->where('start_date', '=', $start_date)
            ->where('period_type', '=', $period_type)
            ->get();

        return $collection;
    }


    public function aggregatePointsByDateAndPeriod(Carbon $start_date, Carbon $end_date, $period_type)
    {
        $collection = DB::table($this->prototype_model->getTable())
            ->select([
                'start_date',
                DB::raw('SUM(points) AS points'),
                DB::raw('SUM(work_units) AS work_units'),
            ])
            ->where('start_date', '>=', $start_date->copy()->startOfHour())
            ->where('start_date', '<=', $end_date)
            ->where('period_type', '=', $period_type)

            ->groupBy('start_date')

            ->get();

        return $collection;
    }

    public function deleteStatsBefore(Carbon $oldest_date_to_keep, $period_type)
    {
        DB::table($this->prototype_model->getTable())
            ->where('start_date', '<', $oldest_date_to_keep)
            ->where('period_type', '=', $period_type)
            ->delete();
    }
}
