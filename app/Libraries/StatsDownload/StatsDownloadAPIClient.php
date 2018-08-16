<?php

namespace App\Libraries\StatsDownload;

use Illuminate\Support\Carbon;
use Tokenly\APIClient\Exception\APIException;
use Tokenly\APIClient\TokenlyAPI;

/**
 * StatsDownloadAPIClient
 */
class StatsDownloadAPIClient extends TokenlyAPI
{

    public function getTeams()
    {
        $teams_data = $this->getPublic('/GetTeams');
        return $teams_data;
    }

    public function getMemberStats(Carbon $start_date, Carbon $end_date, $with_time = true)
    {
        if ($with_time) {
            $format = 'Y-m-d\\TH:i:s';
        } else {
            $format = 'Y-m-d';
        }

        $params = [
            'startDate' => $start_date->format($format),
            'endDate' => $end_date->format($format),
        ];

        $stats_data = $this->getPublic('/GetMemberStats', $params);
        return $stats_data;
    }

    protected function checkForErrorsInResponse($response, $json)
    {
        $is_bad_status_code = ($response->status_code >= 400 and $response->status_code < 600);

        $error_message = null;
        $error_code = 1;
        if ($json) {
            // check for error
            if (isset($json['errors'])) {
                $messages = [];
                foreach ($json['errors'] as $error) {
                    $messages[] = $error['errorMessage'];
                }
                $error_message = is_array($messages) ? implode(", ", $messages) : $messages;
                $error_code = $json['firstErrorCode'] ?? $error_code;
            }
        }
        if ($is_bad_status_code) {
            if ($error_message === null) {
                $error_message = "Received bad status code: {$response->status_code}";
                $error_code = $response->status_code;
            }
        }

        // for any errors, throw an exception
        if ($error_message !== null) {
            throw new APIException($error_message, $error_code);
        }
    }

}
