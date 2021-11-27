<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('mobileUser/create', 'App\Http\Controllers\ApiController@createUser');

Route::post('mobileUser/otp_verification', 'App\Http\Controllers\ApiController@verifield_otp');

Route::post('mobileUser/mobilelogin', 'App\Http\Controllers\ApiController@loginMobile');

Route::post('mobileUser/login_otp_verification', 'App\Http\Controllers\ApiController@loginverifield_otp');
Route::post('mobileUser/check_username', 'App\Http\Controllers\ApiController@check_username');
Route::post('mobileUser/check_mobileno', 'App\Http\Controllers\ApiController@check_mobileno');
Route::post('mobileUser/check_email', 'App\Http\Controllers\ApiController@check_email');

Route::get('mobileUser/test_otpsms', 'App\Http\Controllers\ApiController@send_testsms');
Route::post('mobileUser/resend_otpsms', 'App\Http\Controllers\ApiController@resend_otp');