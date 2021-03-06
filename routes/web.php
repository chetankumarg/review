<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/',  'App\Http\Controllers\AuthController@index');

Route::get('login', 'App\Http\Controllers\AuthController@index');
Route::post('post-login', 'App\Http\Controllers\AuthController@postLogin'); 
// Route::get('registration', 'App\Http\Controllers\AuthController@registration');
// Route::post('post-registration', 'App\Http\Controllers\AuthController@postRegistration'); 
Route::get('dashboard', 'App\Http\Controllers\AuthController@dashboard'); 
Route::get('mobile_usertables', 'App\Http\Controllers\AuthController@moblieUserDashboard'); 
Route::get('view_review', 'App\Http\Controllers\AuthController@viewReview'); 
Route::get('logout', 'App\Http\Controllers\AuthController@logout');
Route::get('docs', 'App\Http\Controllers\AuthController@swaggerlist');
Route::get('review_prod', 'App\Http\Controllers\AuthController@disp_review');
Route::get('review/{id}', 'App\Http\Controllers\AuthController@review_disp');
Route::get('review_detail/{id}', 'App\Http\Controllers\AuthController@review_detail');
