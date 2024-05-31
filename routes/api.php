<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Bank\BankingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api'],function ($router){
    Route::post('/users',[AuthController::class,'create_user']);
    Route::post('/login',[AuthController::class,'login']);

    Route::get('/',[BankingController::class,'show_transactions']);
    Route::get('/deposit',[BankingController::class,'show_deposits']);
    Route::post('/deposit',[BankingController::class,'deposit']);
    Route::post('/withdrawal',[BankingController::class,'withdraw']);
    Route::get('/withdrawal',[BankingController::class,'showWithdrawals']);
});
