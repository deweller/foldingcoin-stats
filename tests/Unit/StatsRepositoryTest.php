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
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Assert as PHPUnit;
use Tests\TestCase;

class StatsRepositoryTest extends TestCase
{

    use RefreshDatabase;

    public function testGetAggregateStats()
    {
        // setup
        $now = Carbon::now();
        Carbon::setTestNow($now);

        // objects
        $stats_repository = app(FoldingStatRepository::class);
        $member_synchronizer = app(MembersSynchronizer::class);

        // sync all teams
        app(TeamSynchronizer::class)->synchronizeTeamsData();

        // sync stats
        $member_synchronizer->synchronizeDateRange(
            Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE),
            Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE)->addDay(10)
        );

        // get aggregate stats
        $first_stat_points = $stats_repository->findAll()[0]['points'];
        $aggregate_stats = $stats_repository->aggregatePointsByDateAndPeriod(
            Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE),
            Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE)->addDay(10),
            FoldingStat::PERIOD_DAILY
        );
        PHPUnit::assertGreaterThan($first_stat_points * 3, $aggregate_stats[0]->points);

    }

    public function testPurgeOldStats()
    {
        // setup
        $now = Carbon::now();
        Carbon::setTestNow($now);

        // objects
        $stats_repository = app(FoldingStatRepository::class);
        $member_synchronizer = app(MembersSynchronizer::class);

        // sync all teams
        app(TeamSynchronizer::class)->synchronizeTeamsData();

        $now_1 = Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE)->addDay(10)->addHours(6);
        Carbon::setTestNow($now_1);

        // sync stats
        $member_synchronizer->synchronizeDateRange(
            Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE),
            $now_1
        );

        // get all stats
        $all_stats = DB::table('folding_stats')
            ->where('period_type', '=', FoldingStat::PERIOD_HOURLY)
            ->get();
        PHPUnit::assertCount(300, $all_stats); //  24 hours + 6 hours  @ 10 per hour

        // next day
        $now_2 = Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE)->addDay(11)->addHours(6);
        Carbon::setTestNow($now_2);
        app(StatsDownloadAPIClient::class)->reset();

        // purge
        $member_synchronizer->purgeOldHourlyStats();

        // only 60 left
        $all_stats = DB::table('folding_stats')
            ->where('period_type', '=', FoldingStat::PERIOD_HOURLY)
            ->get();
        PHPUnit::assertCount(60, $all_stats); //  6 hours  @ 10 per hour

        // sync the next day
        $member_synchronizer->synchronizeDateRange(
            Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE),
            $now_2
        );

        // get all stats will have 300 again
        $all_stats = DB::table('folding_stats')
            ->where('period_type', '=', FoldingStat::PERIOD_HOURLY)
            ->get();
        PHPUnit::assertCount(300, $all_stats); //  24 hours + 6 hours  @ 10 per hour

    }

}
