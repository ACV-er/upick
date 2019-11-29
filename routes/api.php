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

    Route::get('/evaluation/{id}', "EvaluationController@get")->where(["id" => "[0-9]+"])->middleware("evaluation.exist.check");
    Route::get('/evaluation/list/{page}', "EvaluationController@get_list")->where(["page" => "[0-9]+"]);

    // 用户登陆验证区
    Route::group(['middleware' => 'user.login.check'], function () {
        Route::post('/evaluation', "EvaluationController@publish");

        Route::group(["middleware" => 'evaluation.exist.check'], function () {
            Route::post('/like/{id}', "LikeController@mark")->where(["id" => "[0-9]+"]);

            Route::post('/keep/{id}', "CollectionController@keep")->where(["id" => "[0-9]+"]);
        });

        Route::get('/user/{uid}/keep', "CollectionController@get_user_collection_list")->where(["uid" => "[0-9]+"]);
        Route::get('/user/{uid}/publish', "UserLoginController@get_user_publish_list")->where(["uid" => "[0-9]+"]);
    });

    // 管理员和用户都可以使用
    Route::post('/image', "ImageController@upload")->middleware("login.check");

    // 测评所有者和管理员均可操作
    Route::group(["middleware" => ['owner.check', "evaluation.exist.check"]], function () {
        Route::put('/evaluation/{id}', "EvaluationController@update")->where(["id" => "[0-9]+"]);
        Route::delete('/evaluation/{id}', "EvaluationController@delete")->where(["id" => "[0-9]+"]);
    });

    Route::post('/manager/login', "ManagerController@login");
    //管理员登录验证区
    Route::group(['middleware' => 'manager.login.check'],function (){
        Route::post('/manager/update', "ManagerController@update");
        Route::get('/manager/list', "ManagerController@list");

        // 超级管理员验证
        Route::group(['middleware' => 'manager.super.check'],function (){
            Route::post('/manager/add', "ManagerController@add");
            Route::delete('/manager/{id}', "ManagerController@delete")->where(["id" => "[0-9]+"]);
        });

        // 美食库区域
        Route::post('/food',"FoodLibraryController@publish");
        Route::group(['middleware' => 'food.exist.check'], function (){
            Route::put('/food/{id}',"FoodLibraryController@update")->where(["id" => "[0-9]+"]);
            Route::delete('/food/{id}',"FoodLibraryController@delete")->where(["id" => "[0-9]+"]);
        });
        Route::get('/food/list/{page}',"FoodLibraryController@get_list")->where(["page" => "[0-9]+"]);
    });
});
