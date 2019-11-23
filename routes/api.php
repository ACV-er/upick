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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::namespace('Api')->group(function () {
    Route::post('/login', "UserLoginController@login");

    Route::get('/evaluation/{id}', "EvaluationController@get")->where(["id" => "[0-9]+"]);

    // 登陆验证区
    Route::group(['middleware' => 'user.login.check'], function () {
        Route::post('/evaluation', "EvaluationController@publish");

        Route::group(["middleware" => 'evaluation.exist.check'], function () {
            Route::put('/evaluation/{id}', "EvaluationController@update")->where(["id" => "[0-9]+"]);
            Route::delete('/evaluation/{id}', "EvaluationController@delete")->where(["id" => "[0-9]+"]);

            Route::post('/like/{id}', "LikeController@mark")->where(["id" => "[0-9]+"]);
            Route::post('/image', "ImageController@upload");

            Route::post('/keep/{id}', "CollectionController@keep")->where(["id" => "[0-9]+"]);
        });

        Route::get('/user/{uid}/keep', "CollectionController@get_user_collection_list")->where(["uid" => "[0-9]+"]);

        Route::get('/user/{uid}/publish', "UserLoginController@get_user_publish_list")->where(["uid" => "[0-9]+"]);
    });

});
