<?php

namespace App\Console\Commands;

use App\Libraries\StatsDownload\TeamSynchronizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:sync-teams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizes all teams with the database';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(TeamSynchronizer $team_synchronizer)
    {
        $this->info("Begin sync teams");
        Log::debug("Begin sync teams");

        $team_synchronizer->synchronizeTeamsData();

        Log::debug("End sync teams");
        $this->info("End sync teams");
    }
}
