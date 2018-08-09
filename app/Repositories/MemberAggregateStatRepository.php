<?php

namespace App\Repositories;

use App\Models\FoldingStat;
use App\Repositories\FoldingMemberRepository;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tokenly\LaravelApiProvider\Filter\RequestFilter;

/*
* MemberAggregateStatRepository
*/
class MemberAggregateStatRepository
{

    public function aggregateStats()
    {
        $all_stats = $this->findAllMembersWithStats();
        // Log::debug("\$all_stats=".json_encode($all_stats, 192));

        // collect by username
        $by_username = [];
        $now_string = Carbon::now()->toDateTimeString();
        foreach($all_stats as $stat_object) {
            $user_name = $stat_object->user_name;

            if (!isset($by_username[$user_name])) {
                $by_username[$user_name] = [
                    'user_name' => $stat_object->user_name,
                    'friendly_name' => $stat_object->friendly_name,
                    'bitcoin_address' => $stat_object->bitcoin_address,
                    'updated_at' => $stat_object->updated_at,

                    'all_points' => 0,
                    'all_work_units' => 0,
                    'week_points' => 0,
                    'week_work_units' => 0,
                    'day_points' => 0,
                    'day_work_units' => 0,
                ];
            }

            $by_username[$user_name]['all_points'] += ($stat_object->all_points ?? 0);
            $by_username[$user_name]['all_work_units'] += ($stat_object->all_work_units ?? 0);

            $by_username[$user_name]['week_points'] += ($stat_object->week_points ?? 0);
            $by_username[$user_name]['week_work_units'] += ($stat_object->week_work_units ?? 0);

            $by_username[$user_name]['day_points'] += ($stat_object->day_points ?? 0);
            $by_username[$user_name]['day_work_units'] += ($stat_object->day_work_units ?? 0);
        }

        DB::transaction(function() use ($by_username) {
            // delete all
            DB::table('member_aggregate_stats')->delete();

            foreach (collect(array_values($by_username))->chunk(250) as $insert_rows_chunk) {
                DB::table('member_aggregate_stats')->insert($insert_rows_chunk->toArray());
            }
        });
    }

    public function findAllStatObjects()
    {
        return DB::table('member_aggregate_stats')->select('*')->get();
    }

    public function findAllWithFilter(RequestFilter $filter)
    {
        $query = DB::table('member_aggregate_stats')->select();

        $filter->filter($query);
        $filter->limit($query);
        $filter->sort($query);

        Log::debug("\$query->toSql()\n" . $query->toSql());

        return $query->get();

    }
    // ------------------------------------------------------------------------

    protected function findAllMembersWithStats()
    {
        $query = DB::table('folding_members');

        // all stats
        $start_date = Carbon::parse(env('STATS_BEGIN_DATE'));
        $all_stats_query = DB::table('folding_stats')
            ->select([
                'member_id',
                DB::raw('SUM(points) AS points'),
                DB::raw('SUM(work_units) AS work_units'),
            ])
            ->where('start_date', '>=', $start_date)
            ->where('period_type', '=', FoldingStat::PERIOD_DAILY)
            ->groupBy('member_id');
        $query->leftJoinSub($all_stats_query, 'all_stats', function ($join) {
            $join->on('folding_members.id', '=', 'all_stats.member_id');
        });

        // week stats
        $start_date = Carbon::now()->startOfDay()->subDays(6);
        $week_stats_query = DB::table('folding_stats')
            ->select([
                'member_id',
                DB::raw('SUM(points) AS points'),
                DB::raw('SUM(work_units) AS work_units'),
            ])
            ->where('start_date', '>=', $start_date)
            ->where('period_type', '=', FoldingStat::PERIOD_DAILY)
            ->groupBy('member_id');
        $query->leftJoinSub($week_stats_query, 'week_stats', function ($join) {
            $join->on('folding_members.id', '=', 'week_stats.member_id');
        });

        // day stats
        $start_date = Carbon::now()->startOfHour()->subHours(23);
        $day_stats_query = DB::table('folding_stats')
            ->select([
                'member_id',
                DB::raw('SUM(points) AS points'),
                DB::raw('SUM(work_units) AS work_units'),
            ])
            ->where('start_date', '>=', $start_date)
            ->where('period_type', '=', FoldingStat::PERIOD_HOURLY)
            ->groupBy('member_id');
        $query->leftJoinSub($day_stats_query, 'day_stats', function ($join) {
            $join->on('folding_members.id', '=', 'day_stats.member_id');
        });


        $query->select([
            'folding_members.*',

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
