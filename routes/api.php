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
    Route::post('/evaluation', "EvaluationController@publish");
    Route::put('/evaluation/{id}', "EvaluationController@update")->where(["id" => "[0-9]+"]);
    Route::delete('/evaluation/{id}', "EvaluationController@delete")->where(["id" => "[0-9]+"]);
});
