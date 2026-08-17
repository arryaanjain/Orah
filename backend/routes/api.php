<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\RawMaterialController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\RawMaterialPurchaseController;
use App\Http\Controllers\Api\FinishedProductController;
use App\Http\Controllers\Api\OrderBookController;
use App\Http\Controllers\Api\SalesBookController;

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
    
    // Analytics endpoints
    Route::prefix('analytics')->group(function () {
        Route::get('/status', [AnalyticsController::class, 'status']);
        Route::post('/forecast', [AnalyticsController::class, 'getForecast']);
        Route::get('/reorder-alerts', [AnalyticsController::class, 'getReorderAlerts']);
        Route::get('/models', [AnalyticsController::class, 'getModels']);
        Route::get('/warehouses', [AnalyticsController::class, 'getWarehouses']);
        Route::get('/categories', [AnalyticsController::class, 'getCategories']);
        Route::post('/retrain', [AnalyticsController::class, 'retrain']);
    });
    
    // Dashboard endpoints
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/recent-activities', [DashboardController::class, 'recentActivities']);
        Route::get('/sales-overview', [DashboardController::class, 'salesOverview']);
        Route::get('/top-products', [DashboardController::class, 'topProducts']);
        Route::get('/inventory-summary', [DashboardController::class, 'inventorySummary']);
    });
    
    // Raw Materials endpoints
    Route::prefix('raw-materials')->group(function () {
        Route::get('/', [RawMaterialController::class, 'index']);
        Route::post('/', [RawMaterialController::class, 'store']);
        Route::put('/{id}', [RawMaterialController::class, 'update']);
        Route::delete('/{id}', [RawMaterialController::class, 'destroy']);
        Route::post('/batch', [RawMaterialController::class, 'batchStore']);
        
        // Units for a specific material
        Route::get('/{materialId}/units', [RawMaterialController::class, 'getUnits']);
        Route::post('/{materialId}/units', [RawMaterialController::class, 'addUnit']);
        Route::delete('/{materialId}/units/{unitId}', [RawMaterialController::class, 'deleteUnit']);
    });
    
    // Customers endpoints
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index']);
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('/{id}', [CustomerController::class, 'show']);
        Route::put('/{id}', [CustomerController::class, 'update']);
        Route::delete('/{id}', [CustomerController::class, 'destroy']);
        Route::post('/batch', [CustomerController::class, 'batchStore']);
    });
    
    // Raw Material Purchases endpoints
    Route::prefix('rm-purchases')->group(function () {
        Route::get('/', [RawMaterialPurchaseController::class, 'index']);
        Route::post('/', [RawMaterialPurchaseController::class, 'store']);
        Route::put('/{id}', [RawMaterialPurchaseController::class, 'update']);
        Route::delete('/{id}', [RawMaterialPurchaseController::class, 'destroy']);
        Route::post('/batch', [RawMaterialPurchaseController::class, 'batchStore']);
    });
    
    // Finished Products endpoints (with BOM management)
    Route::prefix('products')->group(function () {
        Route::get('/', [FinishedProductController::class, 'index']);
        Route::post('/', [FinishedProductController::class, 'store']);
        Route::get('/{id}', [FinishedProductController::class, 'show']);
        Route::put('/{id}', [FinishedProductController::class, 'update']);
        Route::delete('/{id}', [FinishedProductController::class, 'destroy']);
        Route::post('/{id}/toggle-active', [FinishedProductController::class, 'toggleActive']);
        Route::get('/{id}/bom', [FinishedProductController::class, 'getBom']);
        Route::put('/{id}/bom', [FinishedProductController::class, 'updateBom']);
    });
    
    // Orders endpoints (with inventory calculation)
    Route::prefix('orders')->group(function () {
        // Place non-parameterized routes BEFORE parameterized ones
        Route::post('/calculate', [OrderBookController::class, 'calculateMaterialRequirement']);
        Route::post('/batch', [OrderBookController::class, 'batchStore']);
        
        Route::get('/', [OrderBookController::class, 'index']);
        Route::post('/', [OrderBookController::class, 'store']);
        Route::get('/{id}', [OrderBookController::class, 'show']);
        Route::put('/{id}', [OrderBookController::class, 'update']);
        Route::delete('/{id}', [OrderBookController::class, 'destroy']);
        Route::post('/{id}/status', [OrderBookController::class, 'updateStatus']);
        Route::get('/{id}/material-check', [OrderBookController::class, 'calculateOrderMaterial']);
    });
    
    // Sales endpoints (with order escalation)
    Route::prefix('sales')->group(function () {
        // Place non-parameterized routes BEFORE parameterized ones
        Route::post('/escalate', [SalesBookController::class, 'escalateOrder']);
        Route::post('/batch', [SalesBookController::class, 'batchStore']);
        
        Route::get('/', [SalesBookController::class, 'index']);
        Route::post('/', [SalesBookController::class, 'store']);
        Route::get('/{id}', [SalesBookController::class, 'show']);
        Route::put('/{id}', [SalesBookController::class, 'update']);
        Route::delete('/{id}', [SalesBookController::class, 'destroy']);
        Route::post('/{id}/payment-status', [SalesBookController::class, 'updatePaymentStatus']);
    });
});
