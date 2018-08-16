<?php

namespace App\Console\Commands;

use App\Libraries\StatsDownload\MembersSynchronizer;
use App\Models\FoldingStat;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SyncStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:sync-stats
        { --all : Sync all available data }
        { --days= : Number of days to sync }
        { --hours= : Number of hours to sync }
        { --report-only : Just report stat totals instead of syncing }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizes all member stats with the database';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(MembersSynchronizer $members_synchronizer)
    {
        $this->info("Begin sync member stats");
        Log::debug("Begin sync member stats");

        $days = $this->option('days');
        $hours = $this->option('hours');
        $sync_all = $this->option('all');
        $report_only = $this->option('report-only');

        if (!$days and !$hours and !$sync_all) {
            Log::warning("No time specified");
            $this->error("Specify either days, hours or all");
            return 1;
        }

        $now = Carbon::now();
        $end = $now->copy()->startOfHour()->addHour()->subMinute();

        if ($hours) {
            $start = $now->copy()->startOfHour()->subHours($hours - 1);
        }
        if ($days) {
            $start = $now->copy()->startOfDay()->subDays($days - 1);
        }
        if ($sync_all) {
            $start = Carbon::parse(env('STATS_BEGIN_DATE'));
        }

        // sync stats
        $this->comment('syncing from '.$start.' to '.$end);
        Log::debug('syncing from '.$start.' to '.$end);
        $report_output = $members_synchronizer->synchronizeDateRange($start, $end, $report_only);

        if ($report_only) {
            $this->info(json_encode($report_output, 192));
        }

        Log::debug("End sync member stats");
        $this->info("End sync member stats");
    }
}
