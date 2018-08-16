<?php

namespace App\Console\Commands\Development;

use App\Repositories\MemberAggregateStatRepository;
use Illuminate\Console\Command;

class AggregateStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats-dev:aggregate-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregates stats already downloaded';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(MemberAggregateStatRepository $member_aggregate_stat_repository)
    {
        $member_aggregate_stat_repository->aggregateStats();
    }
}
