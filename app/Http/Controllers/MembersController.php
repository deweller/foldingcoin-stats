<?php

namespace App\Http\Controllers;

use App\Repositories\FoldingMemberRepository;
use App\Repositories\FoldingTeamRepository;
use App\Repositories\MemberAggregateStatRepository;
use Illuminate\Support\Facades\Log;

class MembersController extends Controller
{
    public function members()
    {
        return view('members.index');
    }
    public function showMember($username, MemberAggregateStatRepository $member_aggregate_stat_repository, FoldingTeamRepository $folding_team_repository)
    {
        $member = $member_aggregate_stat_repository->findByUsername($username);
        if (!$member) {
            return redirect()->route('members.index')->withErrors("This username was not found.");
        }


        $member_for_api = [];
        foreach($member as $member_key => $member_val) {
            $member_for_api[camel_case($member_key)] = $member_val;
        }
        unset($member_for_api['updatedAt']);

        // get all teams for this member
        $member_for_api['teams'] = $folding_team_repository->findAllTeamNumberAndNamesForUsername($member->user_name);


        return view('members.show', [
            'member' => $member_for_api,
        ]);
    }

}
