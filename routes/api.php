<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['jwt.auth']], function () {
    Route::get('auth/me', 'AuthController@me');
    Route::put('user/update', 'UserController@update');
    Route::get('users', 'UserController@index');
    Route::get('auth/logout', 'AuthController@logout');
});

Route::group(['middleware' => ['jwt.auth'], 'prefix' => 'chat'], function () {
    Route::post('{user}', 'ChatController@store');
    Route::get('{user}', 'ChatController@getMessages');
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    Route::post('verify', 'AuthController@verifyUser');
});