<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RawMaterialPurchase;
use App\Models\RawMaterial;
use App\Models\RawMaterialUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RawMaterialPurchaseController extends Controller
{
    /**
     * Get all purchases
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $purchases = RawMaterialPurchase::where('company_id', $companyId)
            ->with(['material', 'unit'])
            ->orderBy('purchase_date', 'desc')
            ->get();
        
        return response()->json([
            'purchases' => $purchases
        ]);
    }
    
    /**
     * Create a new purchase
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $validated = $request->validate([
            'material_id' => 'required|exists:rm_master,id',
            'supplier_name' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'qty' => 'required|numeric|min:0',
            'unit_id' => 'required|exists:rm_master_units,id',
            'rate' => 'nullable|numeric|min:0',
            'batch_number' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
        ]);
        
        // Verify material and unit belong to company
        $material = RawMaterial::where('company_id', $companyId)
            ->findOrFail($validated['material_id']);
        
        $unit = RawMaterialUnit::where('company_id', $companyId)
            ->where('material_id', $validated['material_id'])
            ->findOrFail($validated['unit_id']);
        
        $rate = $validated['rate'] ?? 0;
        $totalAmount = $validated['qty'] * $rate;
        
        $purchase = RawMaterialPurchase::create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'material_id' => $validated['material_id'],
            'supplier_name' => $validated['supplier_name'] ?? null,
            'purchase_date' => $validated['purchase_date'],
            'qty' => $validated['qty'],
            'unit_id' => $validated['unit_id'],
            'rate' => $rate,
            'total_amount' => $totalAmount,
            'batch_number' => $validated['batch_number'] ?? null,
            'expiry_date' => $validated['expiry_date'] ?? null,
        ]);
        
        return response()->json([
            'message' => 'Purchase recorded successfully',
            'purchase' => $purchase->load(['material', 'unit'])
        ], 201);
    }
    
    /**
     * Update a purchase
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $purchase = RawMaterialPurchase::where('company_id', $companyId)
            ->findOrFail($id);
        
        $validated = $request->validate([
            'material_id' => 'required|exists:rm_master,id',
            'supplier_name' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'qty' => 'required|numeric|min:0',
            'unit_id' => 'required|exists:rm_master_units,id',
            'rate' => 'nullable|numeric|min:0',
            'batch_number' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
        ]);
        
        $rate = $validated['rate'] ?? 0;
        $totalAmount = $validated['qty'] * $rate;
        
        $purchase->update([
            'material_id' => $validated['material_id'],
            'supplier_name' => $validated['supplier_name'] ?? null,
            'purchase_date' => $validated['purchase_date'],
            'qty' => $validated['qty'],
            'unit_id' => $validated['unit_id'],
            'rate' => $rate,
            'total_amount' => $totalAmount,
            'batch_number' => $validated['batch_number'] ?? null,
            'expiry_date' => $validated['expiry_date'] ?? null,
        ]);
        
        return response()->json([
            'message' => 'Purchase updated successfully',
            'purchase' => $purchase->load(['material', 'unit'])
        ]);
    }
    
    /**
     * Delete a purchase
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $purchase = RawMaterialPurchase::where('company_id', $companyId)
            ->findOrFail($id);
        
        $purchase->delete();
        
        return response()->json([
            'message' => 'Purchase deleted successfully'
        ]);
    }
    
    /**
     * Batch create purchases
     */
    public function batchStore(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $validated = $request->validate([
            'purchases' => 'required|array',
            'purchases.*.material_id' => 'required|exists:rm_master,id',
            'purchases.*.purchase_date' => 'required|date',
            'purchases.*.qty' => 'required|numeric|min:0',
            'purchases.*.unit_id' => 'required|exists:rm_master_units,id',
            'purchases.*.rate' => 'nullable|numeric|min:0',
            'purchases.*.supplier_name' => 'nullable|string|max:255',
        ]);
        
        DB::beginTransaction();
        
        try {
            $createdPurchases = [];
            
            foreach ($validated['purchases'] as $purchaseData) {
                $rate = $purchaseData['rate'] ?? 0;
                $totalAmount = $purchaseData['qty'] * $rate;
                
                $purchase = RawMaterialPurchase::create([
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'material_id' => $purchaseData['material_id'],
                    'supplier_name' => $purchaseData['supplier_name'] ?? null,
                    'purchase_date' => $purchaseData['purchase_date'],
                    'qty' => $purchaseData['qty'],
                    'unit_id' => $purchaseData['unit_id'],
                    'rate' => $rate,
                    'total_amount' => $totalAmount,
                ]);
                
                $createdPurchases[] = $purchase;
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Purchases recorded successfully',
                'purchases' => $createdPurchases
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error recording purchases',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
