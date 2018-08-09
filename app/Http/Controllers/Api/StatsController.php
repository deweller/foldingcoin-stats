<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Repositories\FoldingStatRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StatsController extends ApiController
{
    public function all(Request $request, FoldingStatRepository $stats_repository)
    {
        // get the start and end date
        $validation_rules = [
            'start' => 'required|date',
            'end' => 'sometimes|date',
            'period' => 'required|in:1,2',
        ];
        $valid_attributes = $this->validate($request, $validation_rules);

        $start_date = Carbon::parse($valid_attributes['start'])->copy()->startOfHour();
        if ($start_date->lt(Carbon::parse(env('STATS_BEGIN_DATE')))) {
            $this->throwJsonResponseException("Start date was too soon ", 422);
        }

        if (isset($valid_attributes['end'])) {
            $end_date = Carbon::parse($valid_attributes['end'])->copy()->startOfHour();
        } else {
            $end_date = Carbon::now()->startOfHour();
        }

        if ($end_date->gt(Carbon::now())) {
            $this->throwJsonResponseException("End date was too late", 422);

        }

        $aggregate_stats = $stats_repository->aggregatePointsByDateAndPeriod($start_date, $end_date, $valid_attributes['period']);
        $aggregate_stats = $aggregate_stats->map(function ($s) {
            $s->start_date = Carbon::parse($s->start_date)->toIso8601String();
            return $s;
        });

        return [
            'meta' => [
                'start' => $start_date->toIso8601String(),
                'end' => $end_date->toIso8601String(),
                'period' => $valid_attributes['period'],
            ],
            'stats' => $aggregate_stats,
        ];
    }

}
