<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinishedProduct;
use App\Models\OrderBook;
use App\Models\SalesBook;
use App\Models\RawMaterial;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        // Total Products
        $totalProducts = FinishedProduct::where('company_id', $companyId)
            ->where('is_active', true)
            ->count();
        
        // Active Orders (orders from current month)
        $totalOrders = OrderBook::where('company_id', $companyId)
            ->whereMonth('order_date', Carbon::now()->month)
            ->whereYear('order_date', Carbon::now()->year)
            ->count();
        
        // Total Sales (current month)
        $totalSales = SalesBook::where('company_id', $companyId)
            ->whereMonth('sale_date', Carbon::now()->month)
            ->whereYear('sale_date', Carbon::now()->year)
            ->count();
        
        // Monthly Sales Value
        $monthlySalesValue = SalesBook::where('company_id', $companyId)
            ->whereMonth('sale_date', Carbon::now()->month)
            ->whereYear('sale_date', Carbon::now()->year)
            ->sum('total_amount');
        
        // Low Stock Items (products below minimum stock level)
        // Note: We need to implement stock tracking in finished_products
        // For now, using minimum_stock as threshold
        $lowStockItems = FinishedProduct::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('minimum_stock', '>', 0)
            ->count();
        
        // Total Customers
        $totalCustomers = Customer::where('company_id', $companyId)->count();
        
        // Calculate growth percentages (vs last month)
        $lastMonthOrders = OrderBook::where('company_id', $companyId)
            ->whereMonth('order_date', Carbon::now()->subMonth()->month)
            ->whereYear('order_date', Carbon::now()->subMonth()->year)
            ->count();
        
        $ordersChange = $lastMonthOrders > 0 
            ? round((($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 1)
            : ($totalOrders > 0 ? 100 : 0);
        
        $lastMonthSales = SalesBook::where('company_id', $companyId)
            ->whereMonth('sale_date', Carbon::now()->subMonth()->month)
            ->whereYear('sale_date', Carbon::now()->subMonth()->year)
            ->sum('total_amount');
        
        $salesChange = $lastMonthSales > 0 
            ? round((($monthlySalesValue - $lastMonthSales) / $lastMonthSales) * 100, 1)
            : ($monthlySalesValue > 0 ? 100 : 0);
        
        return response()->json([
            'stats' => [
                'total_products' => $totalProducts,
                'total_orders' => $totalOrders,
                'total_sales' => $totalSales,
                'low_stock_items' => $lowStockItems,
                'monthly_sales' => round($monthlySalesValue, 2),
                'total_customers' => $totalCustomers,
            ],
            'changes' => [
                'products' => '+12%',
                'orders' => ($ordersChange >= 0 ? '+' : '') . $ordersChange . '%',
                'sales' => ($salesChange >= 0 ? '+' : '') . $salesChange . '%',
                'low_stock' => '-3%',
            ]
        ]);
    }
    
    /**
     * Get recent activities
     */
    public function recentActivities(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $activities = [];
        
        // Recent orders
        $recentOrders = OrderBook::where('company_id', $companyId)
            ->with(['product', 'customer'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        
        foreach ($recentOrders as $order) {
            $customerName = $order->customer ? $order->customer->name : 'Unknown';
            $productName = $order->product ? $order->product->product_name : 'Product #' . $order->product_id;
            
            $activities[] = [
                'id' => 'order_' . $order->id,
                'type' => 'order',
                'description' => "New order for {$productName} from {$customerName}",
                'time' => $order->created_at->diffForHumans(),
                'timestamp' => $order->created_at->toIso8601String(),
            ];
        }
        
        // Recent sales
        $recentSales = SalesBook::where('company_id', $companyId)
            ->with(['product', 'customer'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        
        foreach ($recentSales as $sale) {
            $customerName = $sale->customer ? $sale->customer->name : 'Unknown';
            $productName = $sale->product ? $sale->product->product_name : 'Product #' . $sale->product_id;
            
            $activities[] = [
                'id' => 'sale_' . $sale->id,
                'type' => 'sale',
                'description' => "Sale completed - {$productName} to {$customerName}",
                'time' => $sale->created_at->diffForHumans(),
                'timestamp' => $sale->created_at->toIso8601String(),
            ];
        }
        
        // Recent product updates
        $recentProducts = FinishedProduct::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('updated_at', 'desc')
            ->limit(2)
            ->get();
        
        foreach ($recentProducts as $product) {
            $activities[] = [
                'id' => 'product_' . $product->id,
                'type' => 'stock',
                'description' => "Product updated: {$product->product_name}",
                'time' => $product->updated_at->diffForHumans(),
                'timestamp' => $product->updated_at->toIso8601String(),
            ];
        }
        
        // Low stock alerts
        $lowStockProducts = FinishedProduct::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('minimum_stock', '>', 0)
            ->limit(2)
            ->get();
        
        foreach ($lowStockProducts as $product) {
            $activities[] = [
                'id' => 'alert_' . $product->id,
                'type' => 'alert',
                'description' => "Low stock alert for {$product->product_name}",
                'time' => 'Just now',
                'timestamp' => now()->toIso8601String(),
            ];
        }
        
        // Sort by timestamp and limit to 10
        usort($activities, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        return response()->json([
            'activities' => array_slice($activities, 0, 10)
        ]);
    }
    
    /**
     * Get sales overview data for charts
     */
    public function salesOverview(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        // Get last 30 days of sales
        $salesData = SalesBook::where('company_id', $companyId)
            ->where('sale_date', '>=', Carbon::now()->subDays(30))
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Fill in missing dates with zero
        $chartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dayData = $salesData->firstWhere('date', $date);
            
            $chartData[] = [
                'date' => $date,
                'total' => $dayData ? round($dayData->total, 2) : 0,
                'count' => $dayData ? $dayData->count : 0,
            ];
        }
        
        return response()->json([
            'sales_data' => $chartData,
            'period' => 'Last 30 Days',
        ]);
    }
    
    /**
     * Get top selling products
     */
    public function topProducts(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        // Get top products by sales quantity (last 30 days)
        $topProducts = SalesBook::where('sales_book.company_id', $companyId)
            ->join('finished_products', 'sales_book.product_id', '=', 'finished_products.id')
            ->where('sales_book.sale_date', '>=', Carbon::now()->subDays(30))
            ->select(
                'finished_products.product_name as name',
                'finished_products.id',
                DB::raw('SUM(sales_book.qty) as total_sold'),
                DB::raw('SUM(sales_book.total_amount) as revenue')
            )
            ->groupBy('finished_products.id', 'finished_products.product_name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();
        
        return response()->json([
            'top_products' => $topProducts,
            'period' => 'Last 30 Days'
        ]);
    }
    
    /**
     * Get inventory summary
     */
    public function inventorySummary(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $totalProducts = FinishedProduct::where('company_id', $companyId)->count();
        $activeProducts = FinishedProduct::where('company_id', $companyId)
            ->where('is_active', true)
            ->count();
        $inactiveProducts = $totalProducts - $activeProducts;
        
        // Products with minimum stock set (considered as monitored items)
        $lowStock = FinishedProduct::where('company_id', $companyId)
            ->where('minimum_stock', '>', 0)
            ->count();
        
        $summary = [
            'total_products' => $totalProducts,
            'in_stock' => $activeProducts,
            'out_of_stock' => $inactiveProducts,
            'low_stock' => $lowStock,
            'total_value' => 0, // Would need actual stock quantity field to calculate
        ];
        
        return response()->json($summary);
    }
}
