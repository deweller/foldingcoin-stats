<?php

return [
    'proxies' => [
        env('TRUSTED_PROXY', '172.0.0.0/8'),
    ],
    'headers' => Illuminate\Http\Request::HEADER_X_FORWARDED_ALL,
];
