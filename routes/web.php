<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'WelcomeController@index')->name('welcome');

Route::get('/members', 'MembersController@members')->name('members.index');
Route::get('/member/{username}', 'MembersController@showMember')->name('members.show');

Route::get('/teams', 'TeamsController@teams')->name('teams.index');
Route::get('/team/{username}', 'TeamsController@showTeam')->name('teams.show');

