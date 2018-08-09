<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Repositories\MemberAggregateStatRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tokenly\LaravelApiProvider\Filter\IndexRequestFilter;

class MembersController extends ApiController
{
    public function index(Request $request, MemberAggregateStatRepository $member_aggregate_stat_repository)
    {
        $filter = IndexRequestFilter::createFromRequest($request, $this->buildFilterDefinition());
        $member_stats = $member_aggregate_stat_repository->findAllWithFilter($filter);

        $serialized_members = [];
        foreach ($member_stats as $member_stat) {
            $serialized_members[] = [
                'userName' => $member_stat->user_name,
                'friendlyName' => $member_stat->friendly_name,
                'bitcoinAddress' => $member_stat->bitcoin_address,
                'allPoints' => $member_stat->all_points ?? 0,
                'weekPoints' => $member_stat->week_points ?? 0,
                'dayPoints' => $member_stat->day_points ?? 0,
            ];
        }

        return $this->buildJSONResponse(
            $this->buidPagedItemList($serialized_members, $filter->used_page_offset, $filter->used_limit, $filter->getCountForPagination())
        );
    }

    protected function buildFilterDefinition()
    {
        return [
            'fields' => [
                'username' => ['field' => 'username'],
                'all_points' => ['sortField' => 'all_points', 'defaultSortDirection' => 'desc'],
                'week_points' => ['sortField' => 'week_points', 'defaultSortDirection' => 'desc'],
                'day_points' => ['sortField' => 'day_points', 'defaultSortDirection' => 'desc'],
            ],
            'limit' => [
                'field' => 'limit', // optional
                'max' => 50, // optional
                'pagingField' => 'pg', // optional
            ],
            'defaults' => ['sort' => 'all_points'],
        ];
    }
}
