<?php

namespace Tests\Unit;

use App\Libraries\StatsDownload\Mock\MockStatsDownloadAPIClient;
use App\Libraries\StatsDownload\StatsDownloadAPIClient;
use App\Libraries\StatsDownload\TeamSynchronizer;
use App\Models\FoldingTeam;
use App\Repositories\FoldingTeamRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Assert as PHPUnit;
use Tests\TestCase;

class TeamSyncTest extends TestCase
{

    use RefreshDatabase;

    public function testSyncTeams()
    {
        $team_synchronizer = app(TeamSynchronizer::class);
        $team_synchronizer->synchronizeTeamsData();

        $team_repository = app(FoldingTeamRepository::class);

        // check add new teams
        $teams = $team_repository->findAll();
        PHPUnit::assertCount(MockStatsDownloadAPIClient::MOCK_TEAM_COUNT, $teams);


        // check update team
        // manually update the team to the wrong name
        $team = $team_repository->findByTeamNumber($teams[0]['number']);
        $team_repository->update($team, ['name' => 'BAD NAME']);

        // and sync again to update
        $team_synchronizer->synchronizeTeamsData();

        // check updated name
        $team->refresh();
        PHPUnit::assertEquals('kyla61 anita29 Club', $team['name']);

        // check delete team

        // delete from mock API data
        $NUMBER_TO_DELETE = '204348762';
        $api = app(StatsDownloadAPIClient::class);
        $teams_by_number = $api->getMockTeamsByNumber();
        PHPUnit::assertArrayHasKey($NUMBER_TO_DELETE, $teams_by_number);
        unset($teams_by_number[$NUMBER_TO_DELETE]);
        $api->setMockTeamsByNumber($teams_by_number);

        // resync and check for deleted team
        $team_synchronizer->synchronizeTeamsData();
        $teams = $team_repository->findAll();
        PHPUnit::assertCount(MockStatsDownloadAPIClient::MOCK_TEAM_COUNT - 1, $teams);
        $team = $team_repository->findByTeamNumber($NUMBER_TO_DELETE);
        PHPUnit::assertEmpty($team);
    }

    public function testAddOneTeam()
    {
        $team_synchronizer = app(TeamSynchronizer::class);
        $team_synchronizer->synchronizeTeamsData();

        $team_repository = app(FoldingTeamRepository::class);

        // add a team to the mock API data
        $NUMBER_TO_ADD = '123456700';
        $api = app(StatsDownloadAPIClient::class);
        $teams_by_number = $api->getMockTeamsByNumber();
        PHPUnit::assertArrayNotHasKey($NUMBER_TO_ADD, $teams_by_number);
        $teams_by_number[$NUMBER_TO_ADD] = [
            'teamNumber' => $NUMBER_TO_ADD,
            'teamName' => "Added One TEAM"
        ];
        $api->setMockTeamsByNumber($teams_by_number);

        // resync and check for added team
        $team_synchronizer->synchronizeTeamsData();
        $teams = $team_repository->findAll();
        PHPUnit::assertCount(MockStatsDownloadAPIClient::MOCK_TEAM_COUNT + 1, $teams);
        $team = $team_repository->findByTeamNumber($NUMBER_TO_ADD);
        PHPUnit::assertEquals('Added One TEAM', $team['name']);
    }

}
