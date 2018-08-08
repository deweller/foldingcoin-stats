<?php

namespace Tests\Unit;

use App\Libraries\StatsDownload\Mock\MockStatsDownloadAPIClient;
use App\Libraries\StatsDownload\StatsDownloadAPIClient;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Assert as PHPUnit;
use Tests\TestCase;

class StatsDownloadTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetTeams()
    {
        $client = app(StatsDownloadAPIClient::class);
        $teams = $client->getTeams();
        PHPUnit::assertNotEmpty($teams);
        PHPUnit::assertCount(MockStatsDownloadAPIClient::MOCK_TEAM_COUNT, $teams['teams']);
    }

    public function testGetMemberStats()
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $client = app(StatsDownloadAPIClient::class);
        $member_stats = $client->getMemberStats(Carbon::now()->startOfHour()->subHours(24), Carbon::now());
        PHPUnit::assertNotEmpty($member_stats);
        PHPUnit::assertLessThan(MockStatsDownloadAPIClient::MOCK_MEMBER_WITH_TEAMS_COUNT, count($member_stats['members']));
    }

}
