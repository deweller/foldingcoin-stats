<?php

namespace App\Libraries\StatsDownload\Mock;

use App\Libraries\StatsDownload\StatsDownloadAPIClient;
use Exception;
use Faker\Factory;
use Faker\Provider\Biased;
use Faker\Provider\Internet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * StatsDownloadAPIClient
 */
class MockStatsDownloadAPIClient extends StatsDownloadAPIClient
{

    const FAKER_SEED = 100001;
    const MOCK_TEAM_COUNT = 20;
    const MOCK_MEMBERS_PER_DAY_COUNT = 10;

    const MOCK_UNIQUE_MEMBERS_COUNT = 30;
    const MOCK_MEMBER_WITH_TEAMS_COUNT = 35;

    const MOCK_FIRST_DATE = '2018-07-01';

    const BASE_58 = ['1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];

    protected $member_stats_by_key = null;

    public function getTeams()
    {

        // "teams": [
        //     {
        //         "teamNumber": 1234567890
        //         "teamName": "Red Bulldogs"
        //     },
        //     {
        //         "teamNumber": 98765432
        //         "teamName": "Blue Wolves"
        //     }
        // ]

        $this->buildAllTeamsDataOnce();
        return [
            'teams' => $this->mock_teams_by_offset,
        ];
    }

    public function getMemberStats(Carbon $start_date, Carbon $end_date, $with_time = true)
    {
        // build fake member stats
        $member_stats_by_key = [];

        $stats_by_date = $this->allMemberStatsByDate();
        $working_date = clone $start_date;
        $now = Carbon::now();
        while ($working_date->lte($end_date) and $working_date->lte($now)) {
            if (!isset($this->mock_member_stats_by_date[$working_date->toAtomString()])) {
                Log::warning("member stats by date not set for ".$working_date->toAtomString());
                throw new Exception("Unknown date: $working_date", 1);
            }
            $day_stats = $this->mock_member_stats_by_date[$working_date->toAtomString()];

            // merge_stats
            foreach ($day_stats as $day_stat) {
                $key = $day_stat['userName'] . '|' . $day_stat['teamNumber'];
                if (isset($member_stats_by_key[$key])) {
                    $member_stats_by_key[$key]['workUnitsGained'] += $day_stat['workUnitsGained'];
                    $member_stats_by_key[$key]['pointsGained'] += $day_stat['pointsGained'];
                } else {
                    $member_stats_by_key[$key] = $day_stat;
                }
            }

            if ($this->dateIsLastDay($working_date)) {
                $working_date = $working_date->addHours(1);
            } else {
                $working_date = $working_date->addDays(1);
            }
        }

        return [
            'members' => array_values($member_stats_by_key),
        ];

        //     "members": [
        //         {
        //             "bitcoinAddress": "1HsMGTTTWQQiSHyb7wFv4KALgACKofMqiU",
        //             "friendlyName": "vorksholk",
        //             "pointsGained": 5303720,
        //             "teamNumber": 1234567890,
        //             "userName": "vorksholk_ALL_1HsMGTTTWQQiSHyb7wFv4KALgACKofMqiU",
        //             "workUnitsGained": 64
        //         },
        //         {
        //             "bitcoinAddress": "1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j",
        //             "friendlyName": "Other Dude",
        //             "pointsGained": 1,
        //             "teamNumber": 1234567890,
        //             "userName": "otherdude_ALL_1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j",
        //             "workUnitsGained": 1
        //         },
        //         {
        //             "bitcoinAddress":"{bitcoinAddress}",
        //             "friendlyName":"{friendlyName}",
        //             "pointsGained":{pointsGained},
        //             "teamNumber":{teamNumber},
        //             "userName":"{userName}",
        //             "workUnitsGained":{workUnitsGained}
        //         }
        //     ]
    }

    public function getMemberStatsByDate()
    {
        return $this->allMemberStatsByDate();
    }

    public function setMemberStatsByDate($mock_member_stats_by_date)
    {
        $this->mock_member_stats_by_date = $mock_member_stats_by_date;
    }

    public function getMockTeamsByNumber()
    {
        $this->buildAllTeamsDataOnce();
        return $this->mock_teams_by_number;
    }

    public function setMockTeamsByNumber($mock_teams_by_number)
    {
        $this->mock_teams_by_number = $mock_teams_by_number;
        $this->mock_teams_by_offset = array_values($this->mock_teams_by_number);
        $this->mock_team_numbers_by_offset = array_keys($this->mock_teams_by_number);
    }

    public function reset()
    {
        // clears all cached data
        unset($this->last_day_date);
        unset($this->mock_member_stats_by_date);
        unset($this->mock_members_with_teams);
        unset($this->mock_unique_members);
        unset($this->mock_teams_by_number);
        unset($this->mock_team_numbers_by_offset);

        // rebuild
        $this->allMemberStatsByDate();
    }

    // ------------------------------------------------------------------------

    protected function allMemberStatsByDate()
    {
        if (!isset($this->mock_member_stats_by_date)) {
            // always build teams data first for deteministic ordering of data
            $this->buildAllTeamsDataOnce();

            $this->mock_member_stats_by_date = [];
            $this->mock_members_with_teams = [];
            $this->mock_unique_members = [];
            $faker = $this->getFaker();

            $today = Carbon::now()->startOfHour();
            $working_date = Carbon::parse(self::MOCK_FIRST_DATE);
            while ($working_date->lte($today)) {
                $is_hourly = $this->dateIsLastDay($working_date);

                // get 20 random members
                $day_stats_unique_map = [];
                $day_stats = [];
                for ($i = 0; $i < self::MOCK_MEMBERS_PER_DAY_COUNT; $i++) {
                    while (true) {
                        $member = $this->createOrSelectMemberWithTeam($faker);
                        if (!isset($day_stats_unique_map[$member['teamNumber'].'|'.$member['userName']])) {
                            $day_stats_unique_map[$member['teamNumber'].'|'.$member['userName']] = true;
                            break;
                        }
                    }
                    $day_stat = $member;

                    // add work
                    if ($is_hourly) {
                        $day_stat['workUnitsGained'] = $this->biasedNumberBetween(1, floor(5000 / 24), $faker);
                    } else {
                        $day_stat['workUnitsGained'] = $this->biasedNumberBetween(1, 5000, $faker);
                    }
                    $day_stat['pointsGained'] = $day_stat['workUnitsGained'] * $faker->numberBetween(150, 250);

                    $day_stats[] = $day_stat;
                }
                $this->mock_member_stats_by_date[$working_date->toAtomString()] = $day_stats;

                if ($is_hourly) {
                    $working_date = $working_date->addHours(1);
                } else {
                    $working_date = $working_date->addDays(1);
                }
            }

            // echo "\$this->mock_member_stats_by_date: ".json_encode(count($this->mock_member_stats_by_date), 192)."\n";
            // echo "\$this->mock_member_stats_by_date: ".json_encode($this->mock_member_stats_by_date, 192)."\n";
        }

        return $this->mock_member_stats_by_date;
    }

    protected function createOrSelectMemberWithTeam($faker)
    {
        $member_offset = $faker->numberBetween(0, self::MOCK_MEMBER_WITH_TEAMS_COUNT - 1);
        if (!isset($this->mock_members_with_teams[$member_offset])) {
            $member = $this->createOrSelectUniqueMember($faker);

            // select a team
            $this->buildAllTeamsDataOnce();
            $team_offset = $faker->numberBetween(0, self::MOCK_TEAM_COUNT - 1);
            $member['teamNumber'] = $this->mock_team_numbers_by_offset[$team_offset];

            $this->mock_members_with_teams[$member_offset] = $member;
        }

        return $this->mock_members_with_teams[$member_offset];
    }

    protected function createOrSelectUniqueMember($faker)
    {
        $member_offset = $faker->numberBetween(0, self::MOCK_UNIQUE_MEMBERS_COUNT - 1);
        if (!isset($this->mock_unique_members[$member_offset])) {
            // create a new member
            $username = $faker->userName;
            $bitcoin_address = '1' . implode('', $faker->randomElements(self::BASE_58, 33));
            $tag = $faker->randomElement(['FLDC', 'ALL']);

            $member = [
                'userName' => $username . '_' . $tag . '_' . $bitcoin_address,
                'friendlyName' => $username,
                'bitcoinAddress' => $bitcoin_address,
            ];

            $this->mock_unique_members[$member_offset] = $member;
        }

        return $this->mock_unique_members[$member_offset];
    }

    protected function buildAllTeamsDataOnce()
    {
        if (!isset($this->mock_teams_by_number)) {
            $faker = $this->getFaker();
            $this->mock_teams_by_number = [];
            $this->mock_team_numbers_by_offset = [];

            while (count($this->mock_teams_by_number) < self::MOCK_TEAM_COUNT) {
                $team_number = $faker->randomNumber(9);
                if (isset($this->mock_teams_by_number[$team_number])) {
                    continue;
                }

                $this->mock_teams_by_number[$team_number] = [
                    'teamNumber' => $team_number,
                    'teamName' => $faker->userName . ' ' . $faker->userName . ' ' . $faker->randomElement(['Team', 'Group', 'Club', 'Society', 'Organization', 'League', 'Collective', 'Squad', 'Company', 'Party', 'Crew', 'Troupe', 'Cast',]),
                ];
            }

            $this->mock_teams_by_offset = array_values($this->mock_teams_by_number);
            $this->mock_team_numbers_by_offset = array_keys($this->mock_teams_by_number);
            // Log::debug("\$this->mock_team_numbers_by_offset=".json_encode($this->mock_team_numbers_by_offset, 192));
        }

    }

    protected function getFaker()
    {
        if (!isset($this->faker)) {
            $faker = Factory::create();
            $faker->seed(self::FAKER_SEED);

            $faker->addProvider(new Internet($faker));
            $faker->addProvider(new Biased($faker));
            $this->faker = $faker;
        }
        return $this->faker;
    }

    protected function biasedNumberBetween($min, $max, $faker)
    {
        // favors lower numbers
        return $faker->biasedNumberBetween($min, $max, function ($x) {
            return 1 - $x;
        });
    }

    protected function dateIsLastDay(Carbon $date)
    {
        if (!isset($this->last_day_date)) {
            $this->last_day_date = Carbon::now()->startOfDay()->subDay(1);
        }
        return $date->gte($this->last_day_date);
    }
}
