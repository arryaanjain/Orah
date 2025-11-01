<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderBook;
use App\Models\FinishedProduct;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderBookController extends Controller
{
    /**
     * Get all orders
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $orders = OrderBook::where('company_id', $companyId)
            ->with(['product', 'customer'])
            ->orderBy('order_date', 'desc')
            ->get();
        
        return response()->json([
            'orders' => $orders
        ]);
    }
    
    /**
     * Create a new order
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'product_id' => 'required|exists:finished_products,id',
            'qty' => 'required|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        
        // Verify product belongs to company
        $product = FinishedProduct::where('company_id', $companyId)
            ->findOrFail($validated['product_id']);
        
        $unitPrice = $validated['unit_price'] ?? $product->selling_price;
        $totalAmount = $validated['qty'] * $unitPrice;
        
        $order = OrderBook::create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'customer_id' => $validated['customer_id'] ?? null,
            'product_id' => $validated['product_id'],
            'qty' => $validated['qty'],
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount,
            'order_date' => $validated['order_date'],
            'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);
        
        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order->load(['product', 'customer'])
        ], 201);
    }
    
    /**
     * Get a single order
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $order = OrderBook::where('company_id', $companyId)
            ->with(['product', 'customer'])
            ->findOrFail($id);
        
        return response()->json([
            'order' => $order
        ]);
    }
    
    /**
     * Update an order
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $order = OrderBook::where('company_id', $companyId)
            ->findOrFail($id);
        
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'product_id' => 'required|exists:finished_products,id',
            'qty' => 'required|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'status' => 'in:pending,confirmed,in_production,ready,delivered,cancelled',
            'notes' => 'nullable|string',
        ]);
        
        $unitPrice = $validated['unit_price'] ?? 0;
        $totalAmount = $validated['qty'] * $unitPrice;
        
        $order->update([
            'customer_id' => $validated['customer_id'] ?? null,
            'product_id' => $validated['product_id'],
            'qty' => $validated['qty'],
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount,
            'order_date' => $validated['order_date'],
            'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
            'status' => $validated['status'] ?? $order->status,
            'notes' => $validated['notes'] ?? null,
        ]);
        
        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order->load(['product', 'customer'])
        ]);
    }
    
    /**
     * Delete an order
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $order = OrderBook::where('company_id', $companyId)
            ->findOrFail($id);
        
        $order->delete();
        
        return response()->json([
            'message' => 'Order deleted successfully'
        ]);
    }
    
    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $order = OrderBook::where('company_id', $companyId)
            ->findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,in_production,ready,delivered,cancelled',
        ]);
        
        $order->update([
            'status' => $validated['status']
        ]);
        
        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order->load(['product', 'customer'])
        ]);
    }
    
    /**
     * Batch create orders
     */
    public function batchStore(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*.product_id' => 'required|exists:finished_products,id',
            'orders.*.customer_id' => 'nullable|exists:customers,id',
            'orders.*.qty' => 'required|numeric|min:0',
            'orders.*.order_date' => 'required|date',
        ]);
        
        DB::beginTransaction();
        
        try {
            $createdOrders = [];
            
            foreach ($validated['orders'] as $orderData) {
                $product = FinishedProduct::where('company_id', $companyId)
                    ->findOrFail($orderData['product_id']);
                
                $unitPrice = $product->selling_price;
                $totalAmount = $orderData['qty'] * $unitPrice;
                
                $order = OrderBook::create([
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'customer_id' => $orderData['customer_id'] ?? null,
                    'product_id' => $orderData['product_id'],
                    'qty' => $orderData['qty'],
                    'unit_price' => $unitPrice,
                    'total_amount' => $totalAmount,
                    'order_date' => $orderData['order_date'],
                    'status' => 'pending',
                ]);
                
                $createdOrders[] = $order;
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Orders created successfully',
                'orders' => $createdOrders
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
