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

Route::get('getcategories_list', 'App\Http\Controllers\ApiController@getcategorie_list');

Route::get('getall_postreview', 'App\Http\Controllers\ApiController@get_all_post_review');
Route::get('delete_all_postreview', 'App\Http\Controllers\ApiController@delete_all_post_review');

// function for the like review post.

// api for liking the post.
Route::post('mobileUser/like_the_post', 'App\Http\Controllers\ApiController@like_the_post');
// api for un-do like the post
Route::post('mobileUser/delete_like_post', 'App\Http\Controllers\ApiController@undo_like_the_post');
// api to get the total count of likes of the post
Route::post('mobileUser/count_like_post', 'App\Http\Controllers\ApiController@count_like_post');
// api is to get list of users liked by the posts.
Route::post('mobileUser/users_liked_post', 'App\Http\Controllers\ApiController@users_liked_post');
// api check whether the current post is liked by the user or not.
Route::post('mobileUser/check_user_likepost', 'App\Http\Controllers\ApiController@check_user_likepost');


// api for view the comments of the posts.
Route::post('mobileUser/view_the_post_comment', 'App\Http\Controllers\ApiController@view_the_post_comment');
// api for liking/delete the comments.
Route::post('mobileUser/create_del_the_comment', 'App\Http\Controllers\ApiController@create_del_the_comment');
// api for liking/delete the comments.
Route::post('mobileUser/create_del_the_subcomment', 'App\Http\Controllers\ApiController@create_del_the_subcomment');
// API for the like the comments and aggree.
// api for liking/delete the comments.
Route::post('mobileUser/like_del_the_comment', 'App\Http\Controllers\ApiController@like_del_the_comment');
// api for  like/delete the sub-comments
Route::post('mobileUser/like_del_like_subcomment', 'App\Http\Controllers\ApiController@like_del_the_subcomment');

// api for agree/disagree the comments.
Route::post('mobileUser/agree_disagree_the_comment', 'App\Http\Controllers\ApiController@agree_disagree_the_comment');
// api for  agree/disagree the sub-comments
Route::post('mobileUser/agree_disagree_like_subcomment', 'App\Http\Controllers\ApiController@agree_disagree_the_subcomment');

// api function for the follow/unfollow users list

// api to follow the users.
Route::post('mobileUser/follow_user', 'App\Http\Controllers\ApiController@follow_user');

// api to follow list of the users.
Route::post('mobileUser/myfollower_list_user', 'App\Http\Controllers\ApiController@myfollower_list_user');
Route::post('mobileUser/myfollowing_list_user', 'App\Http\Controllers\ApiController@myfollowing_list_user');

// api of other follower list of the users.
Route::post('mobileUser/otherfollower_list_user', 'App\Http\Controllers\ApiController@otherfollower_list_user');
Route::post('mobileUser/otherfollowing_list_user', 'App\Http\Controllers\ApiController@otherfollowing_list_user');

// api to unfollow the users.
Route::post('mobileUser/unfollow_user', 'App\Http\Controllers\ApiController@unfollow_user');

// Api for the list of the posts review along with conditions of
// categories , trending , at the rate , most likes, most views....
Route::post('mobileUser/listPostApi','App\Http\Controllers\ApiController@listPostApi');
Route::post('mobileUser/create_viewCount','App\Http\Controllers\ApiController@Create_viewCount');

// Api to get the user details like pic, uname, email , no of follower, no of following, post review created...
Route::post('mobileUser/user_details_by_id','App\Http\Controllers\ApiController@User_details_By_id');

// Api to get the post reviews depending on the hashtags.
Route::post('mobileUser/get_post_hashtags','App\Http\Controllers\ApiController@get_post_hashtags');
// Api to get the post hashtags for the review post
Route::post('mobileUser/get_trending','App\Http\Controllers\ApiController@get_trending_post');

// Api to get the post hashtags for the review post
Route::post('mobileUser/get_trending_list','App\Http\Controllers\ApiController@get_trending_post_new');
