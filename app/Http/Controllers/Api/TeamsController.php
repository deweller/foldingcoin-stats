<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Repositories\TeamAggregateStatRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tokenly\LaravelApiProvider\Filter\IndexRequestFilter;

class TeamsController extends ApiController
{
    public function index(Request $request, TeamAggregateStatRepository $team_aggregate_stat_repository)
    {
        $filter = IndexRequestFilter::createFromRequest($request, $this->buildFilterDefinition());
        $team_stats = $team_aggregate_stat_repository->findAllWithFilter($filter);

        $serialized_teams = [];
        foreach ($team_stats as $team_stat) {
            $serialized_teams[] = [
                'name' => $team_stat->name,
                'number' => $team_stat->number,
                'allPoints' => $team_stat->all_points ?? 0,
                'weekPoints' => $team_stat->week_points ?? 0,
                'dayPoints' => $team_stat->day_points ?? 0,
            ];
        }

        return $this->buildJSONResponse(
            $this->buidPagedItemList($serialized_teams, $filter->used_page_offset, $filter->used_limit, $filter->getCountForPagination())
        );
    }

    public function byRank(Request $request, TeamAggregateStatRepository $team_aggregate_stat_repository)
    {
        $rank_type = 'all';
        $rank_start = 1;
        $rank_end = 5;
        $team_stats = $team_aggregate_stat_repository->findByRankRange($rank_type, $rank_start, $rank_end);

        $serialized_teams = [];
        foreach ($team_stats as $team_stat) {
            $serialized_teams[] = [
                'name' => $team_stat->name,
                'number' => $team_stat->number,
                
                'allPoints' => $team_stat->all_points ?? 0,
                'allRank' => $team_stat->all_rank ?? 0,
                
                'weekPoints' => $team_stat->week_points ?? 0,
                'weekRank' => $team_stat->week_rank ?? 0,
                
                'dayPoints' => $team_stat->day_points ?? 0,
                'dayRank' => $team_stat->day_rank ?? 0,
            ];
        }

        return $this->buildJSONResponse(
            $this->buidPagedItemList($serialized_teams, $_page_offset=0, $_limit=100, count($serialized_teams))
        );

    }

    protected function buildFilterDefinition()
    {
        return [
            'fields' => [
                'name' => ['field' => 'name', 'sortField' => 'name', 'defaultSortDirection' => 'asc', 'assumeLike' => true,],
                'number' => ['field' => 'number', 'sortField' => 'number', 'defaultSortDirection' => 'asc', ],

                'allPoints' => ['sortField' => 'all_points', 'defaultSortDirection' => 'desc'],
                'weekPoints' => ['sortField' => 'week_points', 'defaultSortDirection' => 'desc'],
                'dayPoints' => ['sortField' => 'day_points', 'defaultSortDirection' => 'desc'],
            ],
            'limit' => [
                'field' => 'limit', // optional
                'max' => 100, // optional
                'pagingField' => 'pg', // optional
            ],
            'defaults' => ['sort' => 'allPoints'],
        ];
    }
}