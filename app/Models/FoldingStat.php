<?php

namespace App\Models;

use Tokenly\LaravelApiProvider\Model\APIModel;
use Exception;

class FoldingStat extends APIModel {

    const PERIOD_HOURLY = 1;
    const PERIOD_DAILY = 2;
    const PERIOD_MONTHLY = 3;

    protected $api_attributes = ['id',];

    public $timestamps = false;

}
