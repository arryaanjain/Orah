<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesBook;
use App\Models\FinishedProduct;
use App\Models\Customer;
use App\Models\OrderBook;
use App\Models\ProductBom;
use App\Models\RawMaterial;
use App\Models\RawMaterialPurchase;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesBookController extends Controller
{
    /**
     * Get all sales
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $sales = SalesBook::where('company_id', $companyId)
            ->with(['product', 'customer', 'order'])
            ->orderBy('sale_date', 'desc')
            ->get();
        
        return response()->json([
            'sales' => $sales
        ]);
    }
    
    /**
     * Escalate an order to sales book.
     * 
     * This is the core business logic:
     * 1. Verify inventory competency for the dispatched qty using unit conversion factors
     * 2. Create sales_book entry
     * 3. Create stock_movements (consumption) to deduct materials
     * 4. Handle partial dispatch → create residual order for remaining qty
     * 5. Update original order status
     */
    public function escalateOrder(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $validated = $request->validate([
            'order_id' => 'required|exists:order_book,id',
            'sales_date' => 'required|date',
            'dispatched_qty' => 'required|numeric|min:1',
        ]);
        
        $order = OrderBook::where('company_id', $companyId)
            ->with(['product', 'customer'])
            ->findOrFail($validated['order_id']);
        
        $dispatchedQty = (float) $validated['dispatched_qty'];
        $originalOrderQty = (float) $order->qty;
        
        // Cannot dispatch more than ordered
        if ($dispatchedQty > $originalOrderQty) {
            return response()->json([
                'message' => 'Dispatched quantity cannot exceed order quantity',
                'order_qty' => $originalOrderQty,
                'dispatched_qty' => $dispatchedQty,
            ], 422);
        }
        
        // Fetch BOM for the product
        $bomItems = ProductBom::where('product_id', $order->product_id)
            ->where('company_id', $companyId)
            ->with(['material', 'unit'])
            ->get();
        
        if ($bomItems->isEmpty()) {
            return response()->json([
                'message' => 'No Bill of Materials found for this product. Cannot calculate inventory.',
            ], 422);
        }
        
        // Check inventory competency using unit conversion factors
        $inventoryStatus = [];
        $allCompetent = true;
        
        foreach ($bomItems as $bom) {
            $bomFactor = ($bom->unit && (float) $bom->unit->conversion_factor > 0)
                ? (float) $bom->unit->conversion_factor
                : 1.0;

            // Required in base unit for this dispatch
            $requiredBase = ((float) $bom->qty_required * $bomFactor) * $dispatchedQty;
            
            // Available in base unit
            $availableBase = RawMaterial::getAvailableStockInBaseUnit($bom->material_id, $companyId);
            
            $differenceBase = $availableBase - $requiredBase;
            
            if ($differenceBase < 0) {
                $allCompetent = false;
            }
            
            // Convert to display unit
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
        
        if (!$allCompetent) {
            return response()->json([
                'message' => 'Insufficient raw materials to fulfill this order',
                'inventory_status' => $inventoryStatus,
            ], 422);
        }
        
        // All materials are competent — proceed with escalation
        DB::beginTransaction();
        
        try {
            // STEP 1: Record the sale in sales_book
            $sale = SalesBook::create([
                'company_id' => $companyId,
                'user_id' => $user->id,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'product_id' => $order->product_id,
                'qty' => $dispatchedQty,
                'unit_price' => $order->unit_price,
                'total_amount' => $dispatchedQty * $order->unit_price,
                'sale_date' => $validated['sales_date'],
                'payment_status' => 'pending',
            ]);
            
            // STEP 2: Deduct materials via stock_movements
            foreach ($bomItems as $bom) {
                $consumedQty = (float) $bom->qty_required * $dispatchedQty;
                
                StockMovement::create([
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'material_id' => $bom->material_id,
                    'movement_type' => 'consumption',
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'qty_change' => $consumedQty,
                    'unit_id' => $bom->unit_id,
                    'notes' => "Consumed for sale #{$sale->id} (Order #{$order->id}): {$dispatchedQty}x {$order->product->product_name}",
                    'movement_date' => now(),
                ]);
            }
            
            // STEP 3: Handle partial dispatch
            $remainingQty = $originalOrderQty - $dispatchedQty;
            $residualOrder = null;
            
            if ($remainingQty > 0) {
                // Create a new sub-order for the remaining quantity
                $residualOrder = OrderBook::create([
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'customer_id' => $order->customer_id,
                    'product_id' => $order->product_id,
                    'qty' => $remainingQty,
                    'unit_price' => $order->unit_price,
                    'total_amount' => $remainingQty * $order->unit_price,
                    'order_date' => $order->order_date,
                    'expected_delivery_date' => $order->expected_delivery_date,
                    'status' => 'pending',
                    'notes' => "Residual from partial dispatch of Order #{$order->id}. Original qty: {$originalOrderQty}, dispatched: {$dispatchedQty}",
                ]);
                
                // Mark original order as delivered (the dispatched portion)
                $order->update([
                    'status' => 'delivered',
                    'qty' => $dispatchedQty,
                    'total_amount' => $dispatchedQty * $order->unit_price,
                ]);
            } else {
                // Full dispatch — mark as delivered
                $order->update(['status' => 'delivered']);
            }
            
            DB::commit();
            
            $response = [
                'message' => $remainingQty > 0 
                    ? "Partial dispatch: {$dispatchedQty} units dispatched, {$remainingQty} units remaining in new order"
                    : "Full dispatch: {$dispatchedQty} units dispatched successfully",
                'sale' => $sale->load(['product', 'customer', 'order']),
                'inventory_deductions' => $inventoryStatus,
                'original_order_status' => 'delivered',
            ];
            
            if ($residualOrder) {
                $response['residual_order'] = $residualOrder->load(['product', 'customer']);
            }
            
            return response()->json($response, 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error processing sale',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create a new sale (direct, without order escalation)
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $validated = $request->validate([
            'order_id' => 'nullable|exists:order_book,id',
            'customer_id' => 'nullable|exists:customers,id',
            'product_id' => 'required|exists:finished_products,id',
            'qty' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
            'payment_status' => 'nullable|in:pending,partial,paid',
            'notes' => 'nullable|string',
        ]);
        
        // Verify product belongs to company
        $product = FinishedProduct::where('company_id', $companyId)
            ->findOrFail($validated['product_id']);
        
        $totalAmount = $validated['qty'] * $validated['unit_price'];
        
        $sale = SalesBook::create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'order_id' => $validated['order_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'product_id' => $validated['product_id'],
            'qty' => $validated['qty'],
            'unit_price' => $validated['unit_price'],
            'total_amount' => $totalAmount,
            'sale_date' => $validated['sale_date'],
            'payment_status' => $validated['payment_status'] ?? 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);
        
        // If sale is linked to an order, update order status
        if ($validated['order_id']) {
            $order = OrderBook::find($validated['order_id']);
            if ($order && $order->status !== 'delivered') {
                $order->update(['status' => 'delivered']);
            }
        }
        
        return response()->json([
            'message' => 'Sale recorded successfully',
            'sale' => $sale->load(['product', 'customer', 'order'])
        ], 201);
    }
    
    /**
     * Get a single sale
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $sale = SalesBook::where('company_id', $companyId)
            ->with(['product', 'customer', 'order'])
            ->findOrFail($id);
        
        return response()->json([
            'sale' => $sale
        ]);
    }
    
    /**
     * Update a sale
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $sale = SalesBook::where('company_id', $companyId)
            ->findOrFail($id);
        
        $validated = $request->validate([
            'order_id' => 'nullable|exists:order_book,id',
            'customer_id' => 'nullable|exists:customers,id',
            'product_id' => 'required|exists:finished_products,id',
            'qty' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
            'payment_status' => 'nullable|in:pending,partial,paid',
            'notes' => 'nullable|string',
        ]);
        
        $totalAmount = $validated['qty'] * $validated['unit_price'];
        
        $sale->update([
            'order_id' => $validated['order_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'product_id' => $validated['product_id'],
            'qty' => $validated['qty'],
            'unit_price' => $validated['unit_price'],
            'total_amount' => $totalAmount,
            'sale_date' => $validated['sale_date'],
            'payment_status' => $validated['payment_status'] ?? $sale->payment_status,
            'notes' => $validated['notes'] ?? null,
        ]);
        
        return response()->json([
            'message' => 'Sale updated successfully',
            'sale' => $sale->load(['product', 'customer', 'order'])
        ]);
    }
    
    /**
     * Delete a sale
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $sale = SalesBook::where('company_id', $companyId)
            ->findOrFail($id);
        
        $sale->delete();
        
        return response()->json([
            'message' => 'Sale deleted successfully'
        ]);
    }
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $sale = SalesBook::where('company_id', $companyId)
            ->findOrFail($id);
        
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,partial,paid',
        ]);
        
        $sale->update([
            'payment_status' => $validated['payment_status']
        ]);
        
        return response()->json([
            'message' => 'Payment status updated successfully',
            'sale' => $sale->load(['product', 'customer', 'order'])
        ]);
    }
    
    /**
     * Batch create sales
     */
    public function batchStore(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $validated = $request->validate([
            'sales' => 'required|array',
            'sales.*.product_id' => 'required|exists:finished_products,id',
            'sales.*.customer_id' => 'nullable|exists:customers,id',
            'sales.*.qty' => 'required|numeric|min:0',
            'sales.*.unit_price' => 'required|numeric|min:0',
            'sales.*.sale_date' => 'required|date',
        ]);
        
        DB::beginTransaction();
        
        try {
            $createdSales = [];
            
            foreach ($validated['sales'] as $saleData) {
                $totalAmount = $saleData['qty'] * $saleData['unit_price'];
                
                $sale = SalesBook::create([
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'customer_id' => $saleData['customer_id'] ?? null,
                    'product_id' => $saleData['product_id'],
                    'qty' => $saleData['qty'],
                    'unit_price' => $saleData['unit_price'],
                    'total_amount' => $totalAmount,
                    'sale_date' => $saleData['sale_date'],
                    'payment_status' => 'pending',
                ]);
                
                $createdSales[] = $sale;
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Sales recorded successfully',
                'sales' => $createdSales
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error recording sales',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
