<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Repositories\FoldingMemberRepository;
use App\Repositories\FoldingStatRepository;
use App\Repositories\FoldingTeamRepository;
use App\Repositories\MemberAggregateStatRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StatsController extends ApiController
{
    public function all(Request $request)
    {
        // validate
        [$start_date, $end_date, $period] = $this->validateStatAttributes($request);

        // build response
        return $this->buildStatsResponse($start_date, $end_date, $period);
    }



    public function memberStats($username, Request $request, FoldingMemberRepository $folding_member_repository)
    {
        // find all member ids
        $member_ids = $folding_member_repository->lookupAllIdsForUsername($username);
        if (count($member_ids) == 0) {
            $this->throwJsonResponseException("User not found", 404);
        }

        // validate
        [$start_date, $end_date, $period] = $this->validateStatAttributes($request);

        // build response
        return $this->buildStatsResponse($start_date, $end_date, $period, $member_ids);

    }

    public function teamStats($number, Request $request, FoldingTeamRepository $folding_team_repository, FoldingMemberRepository $folding_member_repository)
    {
        // find all member ids
        $team = $folding_team_repository->findByTeamNumber($number);
        if (!$team) {
            $this->throwJsonResponseException("Team not found", 404);
        }

        // find all member ids with this team
        $member_ids = $folding_member_repository->lookupAllIdsForTeamId($team['id']);

        // validate
        [$start_date, $end_date, $period] = $this->validateStatAttributes($request);

        // build response
        return $this->buildStatsResponse($start_date, $end_date, $period, $member_ids);

    }

    // ------------------------------------------------------------------------
    

    protected function validateStatAttributes(Request $request)
    {
        // get the start and end date
        $validation_rules = [
            'start' => 'sometimes|date',
            'end' => 'sometimes|date',
            'period' => 'required|in:1,2',
        ];
        $valid_attributes = $this->validate($request, $validation_rules);

        if (isset($valid_attributes['start'])) {
            $start_date = Carbon::parse($valid_attributes['start'])->copy()->startOfHour();
            if ($start_date->lt(Carbon::parse(env('STATS_BEGIN_DATE')))) {
                $this->throwJsonResponseException("Start date was too soon ", 422);
            }
        } else {
            $start_date = Carbon::parse(env('STATS_BEGIN_DATE'));
        }

        if (isset($valid_attributes['end'])) {
            $end_date = Carbon::parse($valid_attributes['end'])->copy()->startOfHour();
        } else {
            $end_date = Carbon::now()->startOfHour();
        }

        if ($end_date->gt(Carbon::now())) {
            $this->throwJsonResponseException("End date was too late", 422);

        }

        return [$start_date, $end_date, $valid_attributes['period']];
    }


    protected function buildStatsResponse($start_date, $end_date, $period, $member_ids = null)
    {
        $stats_repository = app(FoldingStatRepository::class);

        $aggregate_stats = $stats_repository->aggregatePointsByDateAndPeriod($start_date, $end_date, $period, $member_ids);
        $aggregate_stats = $aggregate_stats->map(function ($s) {
            $s->start_date = Carbon::parse($s->start_date)->toIso8601String();
            return $s;
        });

        return [
            'meta' => [
                'start' => $start_date->toIso8601String(),
                'end' => $end_date->toIso8601String(),
                'period' => $period,
            ],
            'stats' => $aggregate_stats,
        ];
    }

}
