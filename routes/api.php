<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::get('/stats/all', 'Api\StatsController@all')->name('api.stats.all');
    Route::get('/stats/member/{username}', 'Api\StatsController@memberStats')->name('api.stats.member');
    Route::get('/stats/team/{number}', 'Api\StatsController@teamStats')->name('api.stats.team');
    
    Route::get('/members', 'Api\MembersController@index')->name('api.members.index');
    Route::get('/teams', 'Api\TeamsController@index')->name('api.teams.index');
    Route::get('/team/{number}/members', 'Api\MembersController@membersForTeam')->name('api.members.byTeam');

    Route::get('/ranked/members', 'Api\MembersController@byRank')->name('api.stats.membersByRank');
    Route::get('/ranked/teams', 'Api\TeamsController@byRank')->name('api.stats.teamsByRank');
});

