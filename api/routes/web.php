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

Route::get('/', function () {
    return view('welcome');
});

//Route::get('login', 'employeesController@login');

//Route::resource('Getlogin', 'employeesController');

Route::get('/login', ['uses' => 'ApiController@login']);
Route::put('/fcmtoken_referesh', ['uses' => 'ApiController@fcmtoken_referesh']);
Route::get('/get_publicholidays', ['uses' => 'ApiController@get_publicholidays']);
Route::get('/clocking_old', ['uses' => 'ApiController@clocking']);
Route::get('/clocking', ['uses' => 'ApiController@device_clocking_job']);
Route::any('/get_deviceDetail', ['uses' => 'ApiController@get_deviceDetail']);
Route::any('/get_employees', ['uses' => 'ApiController@get_employees']);
Route::any('/enter_fp_data', ['uses' => 'ApiController@enter_fp_data']);

Route::get('/test/{id}', ['uses' => 'ApiController@test']);