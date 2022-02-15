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
Route::post('mobileUser/check_userdetials', 'App\Http\Controllers\ApiController@check_userdetials');
Route::post('mobileUser/check_username', 'App\Http\Controllers\ApiController@check_username');
Route::post('mobileUser/check_mobileno', 'App\Http\Controllers\ApiController@check_mobileno');
Route::post('mobileUser/check_email', 'App\Http\Controllers\ApiController@check_email');

Route::get('mobileUser/test_otpsms', 'App\Http\Controllers\ApiController@send_testsms');

Route::post('mobileUser/resend_otpsms', 'App\Http\Controllers\ApiController@resend_otp');


Route::post('mobileUser/upload_pic', 'App\Http\Controllers\ApiController@upload_pic');
Route::post('mobileUser/get_upload_pic', 'App\Http\Controllers\ApiController@getupload_pic');


Route::post('mobileUser/get_user_details', 'App\Http\Controllers\ApiController@getuser_details');

Route::post('mobileUser/get_user_followers','App\Http\Controllers\ApiController@getuser_followers');

Route::post('mobileUser/get_trending_list','App\Http\Controllers\ApiController@get_trending_list');

Route::get('mobileUser/test_getjsonurl', 'App\Http\Controllers\ApiController@get_jsonurl');

Route::post('mobileUser/get_upload_media', 'App\Http\Controllers\ApiController@upload_media_img');

// function to create the review 
Route::post('mobileUser/create_post', 'App\Http\Controllers\ApiController@create_review');
// function to get the postreview by user_id
Route::post('getpostreview_by_id', 'App\Http\Controllers\ApiController@getpost_review_by_shortcode');
Route::post('getpostreview', 'App\Http\Controllers\ApiController@getpost_review');
Route::get('getall_postreview', 'App\Http\Controllers\ApiController@get_all_post_review');