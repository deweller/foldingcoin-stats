<?php

namespace Tests\Unit;

use App\Libraries\StatsDownload\MembersSynchronizer;
use App\Libraries\StatsDownload\Mock\MockStatsDownloadAPIClient;
use App\Libraries\StatsDownload\StatsDownloadAPIClient;
use App\Libraries\StatsDownload\TeamSynchronizer;
use App\Models\FoldingStat;
use App\Repositories\FoldingStatRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Assert as PHPUnit;
use Tests\TestCase;

class MemberStatsSyncTest extends TestCase
{

    use RefreshDatabase;

    public function testSyncMemberStats()
    {
        $stats_repository = app(FoldingStatRepository::class);
        $member_synchronizer = app(MembersSynchronizer::class);

        // sync all teams
        app(TeamSynchronizer::class)->synchronizeTeamsData();

        // sync members and stats
        $member_synchronizer->synchronizeMemberStats(Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE), FoldingStat::PERIOD_DAILY);

        // check members stats
        $stats = $stats_repository->findAll();
        PHPUnit::assertCount(MockStatsDownloadAPIClient::MOCK_MEMBERS_PER_DAY_COUNT, $stats);
        PHPUnit::assertEquals(FoldingStat::PERIOD_DAILY, $stats[0]['period_type']);

        // re-sync
        $member_synchronizer->synchronizeMemberStats(Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE), FoldingStat::PERIOD_DAILY);

        // no change
        $reloaded_stats = $stats_repository->findAll();
        PHPUnit::assertCount(MockStatsDownloadAPIClient::MOCK_MEMBERS_PER_DAY_COUNT, $reloaded_stats);
        PHPUnit::assertEquals($stats[0]['id'], $reloaded_stats[0]['id']);

        // update a stat
        // manually update the points to the wrong amount
        $stats = $stats_repository->findAll();
        $stat = $stats[0];
        $original_points = $stat['points'];
        $stats_repository->update($stat, ['points' => 9999999]);

        // and sync again to update
        $member_synchronizer->synchronizeMemberStats(Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE), FoldingStat::PERIOD_DAILY);

        // check updated points were corrected
        $stat->refresh();
        PHPUnit::assertEquals($original_points, $stat['points']);

        // test delete

        // delete from mock API data
        $NUMBER_TO_DELETE = '204348762';
        $api = app(StatsDownloadAPIClient::class);
        $member_stats_by_date = $api->getMemberStatsByDate();
        $first_date = Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE)->toAtomString();
        unset($member_stats_by_date[$first_date][0]);
        $api->setMemberStatsByDate($member_stats_by_date);

        // resync and check for deleted entry
        $member_synchronizer->synchronizeMemberStats(Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE), FoldingStat::PERIOD_DAILY);
        $reloaded_stats = $stats_repository->findAll();
        PHPUnit::assertCount(MockStatsDownloadAPIClient::MOCK_MEMBERS_PER_DAY_COUNT - 1, $reloaded_stats);
        // PHPUnit::assertEquals($stats[0]['id'], $reloaded_stats[0]['id']);

    }

    public function testSyncMultipleDayStats()
    {

        $stats_repository = app(FoldingStatRepository::class);
        $member_synchronizer = app(MembersSynchronizer::class);

        // sync all teams
        app(TeamSynchronizer::class)->synchronizeTeamsData();

        // sync members and stats for 4 days
        $member_synchronizer->synchronizeDateRange(Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE), Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE)->addDay(4));

        // check members stats
        $stats = $stats_repository->findAll();
        PHPUnit::assertCount(MockStatsDownloadAPIClient::MOCK_MEMBERS_PER_DAY_COUNT * 4, $stats);
        PHPUnit::assertEquals(FoldingStat::PERIOD_DAILY, $stats[0]['period_type']);


    }

}
