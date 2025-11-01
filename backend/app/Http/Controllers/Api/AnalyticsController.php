<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    private AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Check analytics service status
     */
    public function status()
    {
        return response()->json([
            'available' => $this->analyticsService->isAvailable(),
            'service' => 'ML Analytics',
            'message' => $this->analyticsService->isAvailable() 
                ? 'Analytics service is available' 
                : 'Analytics service is currently unavailable'
        ]);
    }

    /**
     * Get demand forecast
     */
    public function getForecast(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|string',
            'product_category' => 'required|string',
            'horizon_days' => 'nullable|integer|min:1|max:90'
        ]);

        if (!$this->analyticsService->isAvailable()) {
            return response()->json([
                'available' => false,
                'message' => 'Analytics service is currently unavailable'
            ], 503);
        }

        $forecast = $this->analyticsService->getForecast(
            $request->warehouse_id,
            $request->product_category,
            $request->horizon_days
        );

        if ($forecast === null) {
            return response()->json([
                'available' => false,
                'message' => 'Unable to retrieve forecast'
            ], 500);
        }

        return response()->json([
            'available' => true,
            'data' => $forecast
        ]);
    }

    /**
     * Get reorder alerts
     */
    public function getReorderAlerts(Request $request)
    {
        $request->validate([
            'horizon_days' => 'nullable|integer|min:1|max:90'
        ]);

        if (!$this->analyticsService->isAvailable()) {
            return response()->json([
                'available' => false,
                'message' => 'Analytics service is currently unavailable',
                'alerts' => []
            ], 200);
        }

        $alerts = $this->analyticsService->getReorderAlerts($request->horizon_days);

        if ($alerts === null) {
            return response()->json([
                'available' => false,
                'message' => 'Unable to retrieve reorder alerts',
                'alerts' => []
            ], 200);
        }

        return response()->json([
            'available' => true,
            'data' => $alerts
        ]);
    }

    /**
     * Get available models
     */
    public function getModels()
    {
        if (!$this->analyticsService->isAvailable()) {
            return response()->json([
                'available' => false,
                'message' => 'Analytics service is currently unavailable',
                'models' => []
            ], 200);
        }

        $models = $this->analyticsService->getAvailableModels();

        if ($models === null) {
            return response()->json([
                'available' => false,
                'message' => 'Unable to retrieve models',
                'models' => []
            ], 200);
        }

        return response()->json([
            'available' => true,
            'data' => $models
        ]);
    }

    /**
     * Get warehouses
     */
    public function getWarehouses()
    {
        if (!$this->analyticsService->isAvailable()) {
            return response()->json([
                'available' => false,
                'warehouses' => []
            ], 200);
        }

        $warehouses = $this->analyticsService->getWarehouses();

        return response()->json([
            'available' => true,
            'data' => $warehouses ?? ['warehouses' => []]
        ]);
    }

    /**
     * Get product categories
     */
    public function getCategories()
    {
        if (!$this->analyticsService->isAvailable()) {
            return response()->json([
                'available' => false,
                'categories' => []
            ], 200);
        }

        $categories = $this->analyticsService->getCategories();

        return response()->json([
            'available' => true,
            'data' => $categories ?? ['categories' => []]
        ]);
    }

    /**
     * Trigger model retraining (admin only)
     */
    public function retrain()
    {
        if (!$this->analyticsService->isAvailable()) {
            return response()->json([
                'available' => false,
                'message' => 'Analytics service is currently unavailable'
            ], 503);
        }

        $result = $this->analyticsService->retrain();

        if ($result === null) {
            return response()->json([
                'available' => false,
                'message' => 'Unable to trigger retraining'
            ], 500);
        }

        return response()->json([
            'available' => true,
            'data' => $result
        ]);
    }
}
