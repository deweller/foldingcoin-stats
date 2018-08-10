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
                'max' => 50, // optional
                'pagingField' => 'pg', // optional
            ],
            'defaults' => ['sort' => 'allPoints'],
        ];
    }
}