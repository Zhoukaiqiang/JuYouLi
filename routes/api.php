<?php

use Illuminate\Http\Request;

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

Route::any("/foo/{name?}", function ($name = null) {
    return "Name is $name";
})->where("name", "[A-Za-z]+");

Route::get("agent_list", "AdminController@agent_list");
Route::get("test", "AdminController@test");
Route::get("query1", "AdminController@query1");