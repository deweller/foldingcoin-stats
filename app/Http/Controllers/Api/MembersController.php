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

        $serialized_members = $this->serializeMemberStats($member_stats);

        return $this->buildJSONResponse(
            $this->buidPagedItemList($serialized_members, $filter->used_page_offset, $filter->used_limit, $filter->getCountForPagination())
        );
    }

    public function membersForTeam($team_number, Request $request, MemberAggregateStatRepository $member_aggregate_stat_repository)
    {
        $filter = IndexRequestFilter::createFromRequest($request, $this->buildMembersForTeamFilterDefinition());
        $member_stats = $member_aggregate_stat_repository->findByTeam($team_number, $filter);

        $serialized_members = $this->serializeMemberStats($member_stats);

        return $this->buildJSONResponse(
            $this->buidPagedItemList($serialized_members, $filter->used_page_offset, $filter->used_limit, $filter->getCountForPagination())
        );
    }

    public function byRank(Request $request, MemberAggregateStatRepository $member_aggregate_stat_repository)
    {
        $rank_type = 'all';
        $rank_start = 1;
        $rank_end = max(1, min(100, intval($request->input('end', 5))));
        $member_stats = $member_aggregate_stat_repository->findByRankRange($rank_type, $rank_start, $rank_end);

        $serialized_members = $this->serializeMemberStats($member_stats);

        return $this->buildJSONResponse(
            $this->buidPagedItemList($serialized_members, $_page_offset=0, $_limit=100, count($serialized_members))
        );

    }

    protected function serializeMemberStats($member_stats)
    {
        $serialized_members = [];
        foreach ($member_stats as $member_stat) {
            $serialized_members[] = [
                'userName' => $member_stat->user_name,
                'friendlyName' => $member_stat->friendly_name,
                'bitcoinAddress' => $member_stat->bitcoin_address,
                
                'allPoints' => $member_stat->all_points ?? 0,
                'allRank' => $member_stat->all_rank ?? 0,
                
                'weekPoints' => $member_stat->week_points ?? 0,
                'weekRank' => $member_stat->week_rank ?? 0,
                
                'dayPoints' => $member_stat->day_points ?? 0,
                'dayRank' => $member_stat->day_rank ?? 0,
            ];
        }

        return $serialized_members;
    }

    protected function buildFilterDefinition()
    {
        return [
            'fields' => [
                'userName' => ['field' => 'friendly_name_lc', 'sortField' => 'friendly_name', 'defaultSortDirection' => 'asc', 'assumeLike' => true, 'transformFn' => ['Tokenly\LaravelApiProvider\Filter\Transformers','LCTrimmed'],],
                'bitcoinAddress' => ['field' => 'bitcoin_address',],
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

    protected function buildMembersForTeamFilterDefinition()
    {
        return [
            'fields' => [
                'userName' => ['field' => 'friendly_name_lc', 'sortField' => 'friendly_name', 'defaultSortDirection' => 'asc', 'assumeLike' => true, 'transformFn' => ['Tokenly\LaravelApiProvider\Filter\Transformers','LCTrimmed'],],
                'bitcoinAddress' => ['field' => 'bitcoin_address',],
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