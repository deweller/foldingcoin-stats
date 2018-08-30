<?php

namespace App\Repositories;

use App\Models\FoldingStat;
use App\Repositories\FoldingMemberRepository;
use App\Repositories\FoldingTeamRepository;
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
        DB::transaction(function() {
            $week_and_day_stats = $this->findAllMemberWeekAndDayStats();
            // Log::debug("\$week_and_day_stats=".json_encode($week_and_day_stats, 192));
            $all_stats = $this->findAllMemberTotalStats();
            // Log::debug("\$all_stats=".json_encode($all_stats, 192));

            // collect by username
            $by_username = [];
            $now_string = Carbon::now()->toDateTimeString();

            // collect week and day stats by username
            foreach($week_and_day_stats as $stat_object) {
                $user_name = $stat_object->user_name;

                if (!isset($by_username[$user_name])) {
                    $by_username[$user_name] = [
                        'user_name' => $stat_object->user_name,
                        'friendly_name' => $stat_object->friendly_name,
                        'friendly_name_lc' => $stat_object->friendly_name_lc,
                        'bitcoin_address' => $stat_object->bitcoin_address,
                        'updated_at' => $now_string,

                        'all_points' => 0,
                        'all_work_units' => 0,
                        'week_points' => 0,
                        'week_work_units' => 0,
                        'day_points' => 0,
                        'day_work_units' => 0,
                    ];
                }

                $by_username[$user_name]['week_points'] += ($stat_object->week_points ?? 0);
                $by_username[$user_name]['week_work_units'] += ($stat_object->week_work_units ?? 0);

                $by_username[$user_name]['day_points'] += ($stat_object->day_points ?? 0);
                $by_username[$user_name]['day_work_units'] += ($stat_object->day_work_units ?? 0);
            }

            // collect all time stats by username
            foreach($all_stats as $stat_object) {
                $user_name = $stat_object->user_name;

                if (!isset($by_username[$user_name])) {
                    $by_username[$user_name] = [
                        'user_name' => $stat_object->user_name,
                        'friendly_name' => $stat_object->friendly_name,
                        'friendly_name_lc' => $stat_object->friendly_name_lc,
                        'bitcoin_address' => $stat_object->bitcoin_address,
                        'updated_at' => $now_string,

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
            }


            // delete all
            DB::table('member_aggregate_stats')->delete();

            // insert all rows
            foreach (collect(array_values($by_username))->chunk(250) as $insert_rows_chunk) {
                DB::table('member_aggregate_stats')->insert($insert_rows_chunk->toArray());
            }

            // add the rankings
            $rank_types = ['all','week','day'];
            foreach($rank_types as $rank_type) {
                $rank_counter = 1;
                $rows = DB::table('member_aggregate_stats')->select('user_name')->orderBy($rank_type.'_points', 'DESC')->get();
                foreach($rows as $row) {
                    DB::table('member_aggregate_stats')->where('user_name', $row->user_name)->update([
                        $rank_type.'_rank' => $rank_counter,
                    ]);
                    ++$rank_counter;
                }
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

        // Log::debug("\$query->toSql()\n" . $query->toSql());

        return $query->get();
    }

    public function findByTeam($team_number)
    {
        $query = DB::table('member_aggregate_stats')->select();

        $query->select('member_aggregate_stats.*');
        $query->join('folding_members', 'member_aggregate_stats.user_name', '=', 'folding_members.user_name');
        $query->where('folding_members.team_number', '=', $team_number);

        // sort by points
        $query->orderBy('all_points', 'desc');

        return $query->get();
    }

    public function findByUsername($username)
    {
        return DB::table('member_aggregate_stats')
            ->where('user_name', '=', $username)
            ->first();
    }

    public function findByRank($rank_type, $rank)
    {
        $field = $rank_type.'_rank';

        return DB::table('member_aggregate_stats')
            ->where($field, '=', $rank)
            ->first();
    }

    // start and end are inclusive
    //   1, 10 is all 10 entries
    public function findByRankRange($rank_type, $rank_start, $rank_end)
    {
        $field = $rank_type.'_rank';

        return DB::table('member_aggregate_stats')
            ->where($field, '>=', $rank_start)
            ->where($field, '<=', $rank_end)
            ->orderBy($field)
            ->get();
    }

    // ------------------------------------------------------------------------

    protected function findAllMemberTotalStats()
    {
        $query = DB::table('folding_stats');
        $query->select([
            'folding_members.*',
            DB::raw('`points` + `start_points` AS all_points'),
            DB::raw('`work_units` + `start_work_units` AS all_work_units')
        ]);

        // latest row
        $query->where('period_type', '=', FoldingStat::PERIOD_HOURLY);
        $subquery = '(select MAX(fs2.start_date) from folding_stats AS fs2 where fs2.member_id = folding_stats.member_id)';
        $query->where('start_date', '=', DB::raw($subquery));

        $query->join('folding_members', 'folding_stats.member_id', '=', 'folding_members.id');

        return $query->get();
    }

    protected function findAllMemberWeekAndDayStats()
    {
        $query = DB::table('folding_members');

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

            'week_stats.points AS week_points',
            'week_stats.work_units AS week_work_units',

            'day_stats.points AS day_points',
            'day_stats.work_units AS day_work_units',
        ]);

        // Log::debug("\$query->->toSql()\n" . $query->toSql());

        return $query->get();

    }

}
