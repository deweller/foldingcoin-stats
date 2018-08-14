<?php

namespace Tests\Unit;

use App\Libraries\StatsDownload\MembersSynchronizer;
use App\Libraries\StatsDownload\Mock\MockStatsDownloadAPIClient;
use App\Libraries\StatsDownload\TeamSynchronizer;
use App\Repositories\FoldingStatRepository;
use App\Repositories\TeamAggregateStatRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Assert as PHPUnit;
use Tests\TestCase;

class TeamAggregateStatRepositoryTest extends TestCase
{

    use RefreshDatabase;

    public function testBuildTeamAggregateStats()
    {
        // setup
        $now = Carbon::now();
        Carbon::setTestNow(Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE)->addDay(10));

        // objects
        $stats_repository = app(FoldingStatRepository::class);
        $member_synchronizer = app(MembersSynchronizer::class);
        $team_aggregate_stat_repository = app(TeamAggregateStatRepository::class);

        // sync all teams
        app(TeamSynchronizer::class)->synchronizeTeamsData();

        // sync stats
        $member_synchronizer->synchronizeDateRange(
            Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE),
            Carbon::parse(MockStatsDownloadAPIClient::MOCK_FIRST_DATE)->addDay(10)
        );

        // aggregate
        $team_aggregate_stat_repository->aggregateStats();

        PHPUnit::assertGreaterThan(17, $team_aggregate_stat_repository->findAllStatObjects()->count());

        // check ranks
        $stat_1 = $team_aggregate_stat_repository->findByRank('all', 1);
        $stat_2 = $team_aggregate_stat_repository->findByRank('all', 2);
        PHPUnit::assertEquals(1, $stat_1->all_rank);
        PHPUnit::assertEquals(2, $stat_2->all_rank);
        PHPUnit::assertGreaterThan($stat_2->all_points, $stat_1->all_points);

        $stat_week_1 = $team_aggregate_stat_repository->findByRank('week', 1);
        $stat_week_2 = $team_aggregate_stat_repository->findByRank('week', 2);
        PHPUnit::assertGreaterThan($stat_week_2->week_points, $stat_week_1->week_points);

        $stat_day_1 = $team_aggregate_stat_repository->findByRank('day', 1);
        $stat_day_2 = $team_aggregate_stat_repository->findByRank('day', 2);
        PHPUnit::assertGreaterThan($stat_day_2->day_points, $stat_day_1->day_points);

    }

}
