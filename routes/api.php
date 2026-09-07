<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\AuthController; // <-- Your Auth controller is back!

// --- YOUR LOGIN / REGISTER ROUTES ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// --- YOUR PROTECTED WALLET ROUTES ---
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/accounts/create', [WalletController::class, 'createWallet']);
    Route::get('/accounts/balance', [WalletController::class, 'getBalance']);
    Route::post('/accounts/fund-one', [WalletController::class, 'fundOne']);
    Route::post('/accounts/fund-all', [WalletController::class, 'fundAll']);
    Route::post('/accounts/withdraw', [WalletController::class, 'withdraw']);
});