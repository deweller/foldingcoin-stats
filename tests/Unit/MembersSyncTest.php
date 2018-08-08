<?php

namespace Tests\Unit;

use App\Libraries\StatsDownload\MembersSynchronizer;
use App\Libraries\StatsDownload\Mock\MockStatsDownloadAPIClient;
use App\Libraries\StatsDownload\TeamSynchronizer;
use App\Models\FoldingStat;
use App\Repositories\FoldingMemberRepository;
use App\Repositories\FoldingTeamRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Assert as PHPUnit;
use Tests\TestCase;

class MembersSyncTest extends TestCase
{

    use RefreshDatabase;

    public function testSyncMembersWithNewTeams()
    {
        $member_synchronizer = app(MembersSynchronizer::class);
        $member_synchronizer->synchronizeMemberStats(Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE), FoldingStat::PERIOD_DAILY);

        $team_repository = app(FoldingTeamRepository::class);
        $member_repository = app(FoldingMemberRepository::class);

        // check add new members
        $members = $member_repository->findAll();
        PHPUnit::assertCount(MockStatsDownloadAPIClient::MOCK_MEMBERS_PER_DAY_COUNT, $members);

        // also created new teams
        $teams = $team_repository->findAll();
        $unique_teams_count = collect($members)->pluck('team_number')->unique()->count();
        PHPUnit::assertCount($unique_teams_count, $teams);
        // echo "\$teams: ".json_encode($teams, 192)."\n";
    }

    public function testSyncMembersWithExistingTeams()
    {
        $team_repository = app(FoldingTeamRepository::class);
        $member_repository = app(FoldingMemberRepository::class);

        // sync all teams
        $team_synchronizer = app(TeamSynchronizer::class);
        $team_synchronizer->synchronizeTeamsData();

        // sync members for 1 hour
        $member_synchronizer = app(MembersSynchronizer::class);
        $member_synchronizer->synchronizeMemberStats(Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE), FoldingStat::PERIOD_DAILY);

        // check add new members
        $members = $member_repository->findAll();
        PHPUnit::assertCount(MockStatsDownloadAPIClient::MOCK_MEMBERS_PER_DAY_COUNT, $members);

        // used existing teams
        $teams = $team_repository->findAll();
        PHPUnit::assertCount(MockStatsDownloadAPIClient::MOCK_TEAM_COUNT, $teams);
        // echo "\$teams: ".json_encode($teams, 192)."\n";
    }

    public function testUpdateMember()
    {
        $team_repository = app(FoldingTeamRepository::class);
        $member_repository = app(FoldingMemberRepository::class);

        // sync all teams
        $team_synchronizer = app(TeamSynchronizer::class);
        $team_synchronizer->synchronizeTeamsData();

        // sync members for 1 hour
        $member_synchronizer = app(MembersSynchronizer::class);
        $member_synchronizer->synchronizeMemberStats(Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE), FoldingStat::PERIOD_DAILY);

        // check update member
        // manually update the member to the wrong name
        $members = $member_repository->findAll();
        $member = $members[0];
        $original_bitcoin_address = $member['bitcoin_address'];
        $member_repository->update($member, ['bitcoin_address' => 'BAD BITCOIN_ADDRESS']);
        $member->refresh();

        // and sync again to update
        $member_synchronizer->synchronizeMemberStats(Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE), FoldingStat::PERIOD_DAILY);

        // check updated name
        $member->refresh();
        PHPUnit::assertEquals($original_bitcoin_address, $member['bitcoin_address']);
    }



}
