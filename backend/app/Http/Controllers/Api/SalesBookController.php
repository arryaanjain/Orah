<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesBook;
use App\Models\FinishedProduct;
use App\Models\Customer;
use App\Models\OrderBook;
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
     * Create a new sale
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
