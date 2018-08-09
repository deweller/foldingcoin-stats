<?php

namespace App\Models;

use Tokenly\LaravelApiProvider\Model\APIModel;
use Exception;

class FoldingMember extends APIModel {

    protected $api_attributes = ['user_name', 'friendly_name', 'bitcoin_address', 'team_number'];

}
