<?php

namespace App\Console\Commands;

use App\Libraries\StatsDownload\MembersSynchronizer;
use App\Models\FoldingStat;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PurgeStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:purge-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purges old hourly stats';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(MembersSynchronizer $members_synchronizer)
    {
        $this->info("Begin purge stats");
        Log::debug("Begin purge stats");

        $members_synchronizer->purgeOldHourlyStats();

        Log::debug("End purge stats");
        $this->info("End purge stats");
    }
}
