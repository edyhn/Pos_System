<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiCategoryController;
use App\Http\Controllers\Api\ApiProductController;
use App\Http\Controllers\Api\ApiTransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [ApiAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/user', [ApiAuthController::class, 'user']);

    Route::middleware('role:owner')->group(function () {
        Route::apiResource('products', ApiProductController::class)->names('api.products');
        Route::apiResource('categories', ApiCategoryController::class)->names('api.categories')->except('show');
    });

    Route::get('/transactions', [ApiTransactionController::class, 'index']);
    Route::get('/transactions/today', [ApiTransactionController::class, 'today']);
    Route::get('/transactions/{transaction}', [ApiTransactionController::class, 'show']);
});
