<?php

namespace App\Http\Controllers;

class WelcomeController extends Controller
{

    public function index()
    {
        return view('welcome', [
            'statsBeginDate' => env('STATS_BEGIN_DATE'),
        ]);
    }
}
