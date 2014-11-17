<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', 'MasterController@home');

Route::get('details/{id}', 'DetailsController@item')->where('id', '[0-9]+');

Route::controller('settings', 'SettingsController');

Route::controller('import', 'ImportController');
