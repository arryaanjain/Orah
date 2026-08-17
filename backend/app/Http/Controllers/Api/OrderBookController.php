<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderBook;
use App\Models\FinishedProduct;
use App\Models\Customer;
use App\Models\ProductBom;
use App\Models\RawMaterial;
use App\Models\RawMaterialPurchase;
use App\Models\StockMovement;
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
        
        $status = $request->query('status'); // optional filter
        
        $query = OrderBook::where('company_id', $companyId)
            ->with(['product', 'customer'])
            ->orderBy('order_date', 'desc');
        
        if ($status) {
            $statuses = explode(',', $status);
            $query->whereIn('status', $statuses);
        }
        
        $orders = $query->get();
        
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
    
    /**
     * Calculate material requirements for pending/partial orders.
     * 
     * High-precision unit conversion:
     * Converts purchased stock and BOM requirements through base units using conversion factors.
     */
    public function calculateMaterialRequirement(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $response = [];
        
        // Get all pending/confirmed/ready orders grouped by product
        $orders = OrderBook::where('company_id', $companyId)
            ->whereIn('status', ['pending', 'confirmed', 'in_production', 'ready'])
            ->with('product')
            ->get();
        
        // Group orders by product_id
        $ordersByProduct = $orders->groupBy('product_id');
        
        foreach ($ordersByProduct as $productId => $productOrders) {
            $product = $productOrders->first()->product;
            $totalOrderedQty = (float) $productOrders->sum('qty');
            
            // Fetch BOM for this product
            $bomItems = ProductBom::where('product_id', $productId)
                ->where('company_id', $companyId)
                ->with(['material', 'unit'])
                ->get();
            
            if ($bomItems->isEmpty()) {
                continue;
            }
            
            $inventoryStatus = [];
            
            foreach ($bomItems as $bom) {
                $bomFactor = ($bom->unit && (float) $bom->unit->conversion_factor > 0)
                    ? (float) $bom->unit->conversion_factor
                    : 1.0;

                // Total required in raw material's base unit
                $requiredBase = ((float) $bom->qty_required * $bomFactor) * $totalOrderedQty;
                
                // Available stock in raw material's base unit (purchases * unit factor - consumption * unit factor)
                $availableBase = RawMaterial::getAvailableStockInBaseUnit($bom->material_id, $companyId);
                
                $differenceBase = $availableBase - $requiredBase;

                // Convert quantities back to the BOM display unit for user readability
                $requiredDisplay = $requiredBase / $bomFactor;
                $availableDisplay = $availableBase / $bomFactor;
                $differenceDisplay = $differenceBase / $bomFactor;

                $unitName = $bom->unit->unit_name ?? $bom->material->base_unit;
                
                $inventoryStatus[] = [
                    'material' => $bom->material->material,
                    'material_id' => $bom->material_id,
                    'requiredQty' => round($requiredDisplay, 4),
                    'availableQty' => round($availableDisplay, 4),
                    'difference' => round($differenceDisplay, 4),
                    'unit' => $unitName,
                    'unit_id' => $bom->unit_id,
                ];
            }
            
            $productKey = $product->product_name;
            $response[$productKey] = [
                'product_id' => $productId,
                'total_ordered_qty' => $totalOrderedQty,
                'order_count' => $productOrders->count(),
                'inventory_status' => $inventoryStatus,
            ];
        }
        
        return response()->json([
            'products' => $response
        ]);
    }
    
    /**
     * Calculate material requirement for a single specific order.
     */
    public function calculateOrderMaterial(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $order = OrderBook::where('company_id', $companyId)
            ->with('product')
            ->findOrFail($id);
        
        $bomItems = ProductBom::where('product_id', $order->product_id)
            ->where('company_id', $companyId)
            ->with(['material', 'unit'])
            ->get();
        
        $inventoryStatus = [];
        
        foreach ($bomItems as $bom) {
            $bomFactor = ($bom->unit && (float) $bom->unit->conversion_factor > 0)
                ? (float) $bom->unit->conversion_factor
                : 1.0;

            // Total required for this order in base unit
            $requiredBase = ((float) $bom->qty_required * $bomFactor) * (float) $order->qty;
            
            // Total available stock in base unit
            $availableBase = RawMaterial::getAvailableStockInBaseUnit($bom->material_id, $companyId);
            
            $differenceBase = $availableBase - $requiredBase;

            // Convert base unit quantities back to BOM display unit
            $requiredDisplay = $requiredBase / $bomFactor;
            $availableDisplay = $availableBase / $bomFactor;
            $differenceDisplay = $differenceBase / $bomFactor;

            $unitName = $bom->unit->unit_name ?? $bom->material->base_unit;
            
            $inventoryStatus[] = [
                'material' => $bom->material->material,
                'material_id' => $bom->material_id,
                'requiredQty' => round($requiredDisplay, 4),
                'availableQty' => round($availableDisplay, 4),
                'difference' => round($differenceDisplay, 4),
                'unit' => $unitName,
                'unit_id' => $bom->unit_id,
            ];
        }
        
        return response()->json([
            'order_id' => $order->id,
            'product' => $order->product->product_name,
            'order_qty' => $order->qty,
            'inventory_status' => $inventoryStatus,
        ]);
    }
}
