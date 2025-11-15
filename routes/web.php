<?php

use App\Http\Controllers\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/auth', action: [ Auth::class,'index']);

Route::post('/login', action: [AuthController::class, 'login']);
Route::post('/check', action: [AuthController::class, 'check']);

Route::middleware('api_token')->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('/user', 'index');
    });
});
