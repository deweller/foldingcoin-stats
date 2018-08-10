<?php

namespace App\Repositories;

use App\Models\FoldingStat;
use App\Repositories\FoldingTeamRepository;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tokenly\LaravelApiProvider\Filter\RequestFilter;

/*
* TeamAggregateStatRepository
*/
class TeamAggregateStatRepository
{

    public function aggregateStats()
    {
        $all_stats = $this->findAllTeamsWithStats();

        DB::transaction(function() use ($all_stats) {
            // delete all
            DB::table('team_aggregate_stats')->delete();

            // insert 250 at a time
            $now_string = Carbon::now()->toDateTimeString();
            foreach (collect($all_stats->values())->chunk(250) as $chunk_rows) {
                $insert_rows_chunk = [];
                foreach($chunk_rows as $chunk_row) {
                    $chunk_row = json_decode(json_encode($chunk_row), true);
                    $insert_rows_chunk[] = array_merge($chunk_row, [
                        'all_points' => $chunk_row['all_points'] ?? 0,
                        'all_work_units' => $chunk_row['all_work_units'] ?? 0,
                        'week_points' => $chunk_row['week_points'] ?? 0,
                        'week_work_units' => $chunk_row['week_work_units'] ?? 0,
                        'day_points' => $chunk_row['day_points'] ?? 0,
                        'day_work_units' => $chunk_row['day_work_units'] ?? 0,

                        'updated_at' => $now_string,
                    ]);
                }
                DB::table('team_aggregate_stats')->insert($insert_rows_chunk);
            }
        });
    }

    public function findAllStatObjects()
    {
        return DB::table('team_aggregate_stats')->select('*')->get();
    }

    public function findAllWithFilter(RequestFilter $filter)
    {
        $query = DB::table('team_aggregate_stats')->select();

        $filter->filter($query);
        $filter->limit($query);
        $filter->sort($query);

        // Log::debug("\$query->toSql()\n" . $query->toSql());

        return $query->get();
    }

    public function findByTeamNumber($number)
    {
        return DB::table('team_aggregate_stats')
            ->where('number', '=', $number)
            ->first();
    }

    // ------------------------------------------------------------------------

    protected function findAllTeamsWithStats()
    {
        $query = DB::table('folding_teams');

        // all stats
        $start_date = Carbon::parse(env('STATS_BEGIN_DATE'));
        $all_stats_query = DB::table('folding_stats')
            ->select([
                'team_id',
                DB::raw('SUM(points) AS points'),
                DB::raw('SUM(work_units) AS work_units'),
            ])
            ->where('start_date', '>=', $start_date)
            ->where('period_type', '=', FoldingStat::PERIOD_DAILY)
            ->groupBy('team_id');
        $query->leftJoinSub($all_stats_query, 'all_stats', function ($join) {
            $join->on('folding_teams.id', '=', 'all_stats.team_id');
        });

        // week stats
        $start_date = Carbon::now()->startOfDay()->subDays(6);
        $week_stats_query = DB::table('folding_stats')
            ->select([
                'team_id',
                DB::raw('SUM(points) AS points'),
                DB::raw('SUM(work_units) AS work_units'),
            ])
            ->where('start_date', '>=', $start_date)
            ->where('period_type', '=', FoldingStat::PERIOD_DAILY)
            ->groupBy('team_id');
        $query->leftJoinSub($week_stats_query, 'week_stats', function ($join) {
            $join->on('folding_teams.id', '=', 'week_stats.team_id');
        });

        // day stats
        $start_date = Carbon::now()->startOfHour()->subHours(23);
        $day_stats_query = DB::table('folding_stats')
            ->select([
                'team_id',
                DB::raw('SUM(points) AS points'),
                DB::raw('SUM(work_units) AS work_units'),
            ])
            ->where('start_date', '>=', $start_date)
            ->where('period_type', '=', FoldingStat::PERIOD_HOURLY)
            ->groupBy('team_id');
        $query->leftJoinSub($day_stats_query, 'day_stats', function ($join) {
            $join->on('folding_teams.id', '=', 'day_stats.team_id');
        });


        $query->select([
            'folding_teams.number',
            'folding_teams.name',

            'all_stats.points AS all_points',
            'all_stats.work_units AS all_work_units',

            'week_stats.points AS week_points',
            'week_stats.work_units AS week_work_units',

            'day_stats.points AS day_points',
            'day_stats.work_units AS day_work_units',
        ]);

        // Log::debug("\$query->->toSql()\n" . $query->toSql());

        return $query->get();

    }

}
