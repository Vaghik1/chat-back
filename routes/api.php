<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['jwt.auth']], function () {
    Route::get('users/list', function(){
        $users = App\User::all();
        
        $response = ['success' => true, 'data' => $users];
        return response()->json($response, 201);
    });
});

Route::group(['prefix' => 'user'], function () {
    Route::post('login', 'UserController@login');
    Route::post('register', 'UserController@register');
    Route::post('verify', 'UserController@verifyUser');
});