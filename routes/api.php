<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['jwt.auth']], function () {
    Route::get('auth/me', 'AuthController@me');
    Route::put('user/update', 'UserController@update');
    Route::get('users', 'UserController@index');
    Route::get('auth/logout', 'AuthController@logout');
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    Route::post('verify', 'AuthController@verifyUser');
});