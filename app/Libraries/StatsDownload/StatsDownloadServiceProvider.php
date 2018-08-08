<?php

namespace App\Libraries\StatsDownload;

use App\Libraries\StatsDownload\Mock\MockStatsDownloadAPIClient;
use App\Libraries\StatsDownload\StatsDownloadAPIClient;
use Illuminate\Support\ServiceProvider;

/**
 * StatsDownloadServiceProvider
 */
class StatsDownloadServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(StatsDownloadAPIClient::class, function () {
            if (env('MOCK_STATS_DATA')) {
                return new MockStatsDownloadAPIClient(env('STATS_PROVIDER_URL'));
            } else {
                return new StatsDownloadAPIClient(env('STATS_PROVIDER_URL'));
            }
        });
    }
}
