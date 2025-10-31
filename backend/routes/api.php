<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// Public routes
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/auth/find-companies', [AuthController::class, 'findCompaniesByEmail']);
    Route::post('/auth/complete-profile', [AuthController::class, 'completeProfile']);
    Route::post('/auth/link-company', [AuthController::class, 'linkToCompany']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});
