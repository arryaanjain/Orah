<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AnalyticsService
{
    private string $mlServiceUrl;
    private bool $isAvailable;
    private int $timeout;

    public function __construct()
    {
        $this->mlServiceUrl = env('ML_SERVICE_URL', 'http://127.0.0.1:8001');
        $this->timeout = 5; // 5 seconds timeout
        $this->isAvailable = $this->checkHealth();
    }

    /**
     * Check if ML service is available
     */
    public function checkHealth(): bool
    {
        try {
            $response = Http::timeout($this->timeout)->get("{$this->mlServiceUrl}/health");
            return $response->successful();
        } catch (Exception $e) {
            Log::warning('ML Service unavailable: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if analytics service is available
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * Get demand forecast for a warehouse and product category
     */
    public function getForecast(string $warehouseId, string $productCategory, ?int $horizonDays = null): ?array
    {
        if (!$this->isAvailable) {
            return null;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->mlServiceUrl}/forecast", [
                    'warehouse_id' => $warehouseId,
                    'product_category' => $productCategory,
                    'horizon_days' => $horizonDays
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('ML Service forecast error: ' . $response->body());
            return null;
        } catch (Exception $e) {
            Log::error('ML Service forecast exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get reorder alerts for all warehouses
     */
    public function getReorderAlerts(?int $horizonDays = null): ?array
    {
        if (!$this->isAvailable) {
            return null;
        }

        try {
            $url = "{$this->mlServiceUrl}/reorder-alerts";
            if ($horizonDays) {
                $url .= "?horizon_days={$horizonDays}";
            }

            $response = Http::timeout($this->timeout)->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('ML Service reorder alerts error: ' . $response->body());
            return null;
        } catch (Exception $e) {
            Log::error('ML Service reorder alerts exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get list of available trained models
     */
    public function getAvailableModels(): ?array
    {
        if (!$this->isAvailable) {
            return null;
        }

        try {
            $response = Http::timeout($this->timeout)->get("{$this->mlServiceUrl}/models");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (Exception $e) {
            Log::error('ML Service models exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get list of warehouses
     */
    public function getWarehouses(): ?array
    {
        if (!$this->isAvailable) {
            return null;
        }

        try {
            $response = Http::timeout($this->timeout)->get("{$this->mlServiceUrl}/warehouses");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (Exception $e) {
            Log::error('ML Service warehouses exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get list of product categories
     */
    public function getCategories(): ?array
    {
        if (!$this->isAvailable) {
            return null;
        }

        try {
            $response = Http::timeout($this->timeout)->get("{$this->mlServiceUrl}/categories");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (Exception $e) {
            Log::error('ML Service categories exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Trigger model retraining
     */
    public function retrain(): ?array
    {
        if (!$this->isAvailable) {
            return null;
        }

        try {
            $response = Http::timeout(60)->post("{$this->mlServiceUrl}/retrain");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (Exception $e) {
            Log::error('ML Service retrain exception: ' . $e->getMessage());
            return null;
        }
    }
}
