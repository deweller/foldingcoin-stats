<?php

namespace App\Http\Controllers;

class MembersController extends Controller
{
    public function teams()
    {
        return view('teams.index');
    }
    public function members()
    {
        return view('members.index');
    }
}
