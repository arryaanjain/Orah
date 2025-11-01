<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinishedProduct;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FinishedProductController extends Controller
{
    /**
     * Get all finished products
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $products = FinishedProduct::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'products' => $products
        ]);
    }
    
    /**
     * Create a new product
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $validated = $request->validate([
            'product_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('finished_products')->where(function ($query) use ($companyId, $user) {
                    return $query->where('company_id', $companyId)
                                ->where('user_id', $user->id);
                }),
            ],
            'description' => 'nullable|string',
            'base_unit' => 'required|string|max:50',
            'selling_price' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
        ]);
        
        $product = FinishedProduct::create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'product_name' => $validated['product_name'],
            'description' => $validated['description'] ?? null,
            'base_unit' => $validated['base_unit'],
            'selling_price' => $validated['selling_price'] ?? 0,
            'minimum_stock' => $validated['minimum_stock'] ?? 0,
            'is_active' => true,
        ]);
        
        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }
    
    /**
     * Get a single product
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $product = FinishedProduct::where('company_id', $companyId)
            ->findOrFail($id);
        
        return response()->json([
            'product' => $product
        ]);
    }
    
    /**
     * Update a product
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $product = FinishedProduct::where('company_id', $companyId)
            ->findOrFail($id);
        
        $validated = $request->validate([
            'product_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('finished_products')->where(function ($query) use ($companyId, $user) {
                    return $query->where('company_id', $companyId)
                                ->where('user_id', $user->id);
                })->ignore($product->id),
            ],
            'description' => 'nullable|string',
            'base_unit' => 'required|string|max:50',
            'selling_price' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);
        
        $product->update($validated);
        
        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product
        ]);
    }
    
    /**
     * Delete a product
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $product = FinishedProduct::where('company_id', $companyId)
            ->findOrFail($id);
        
        $product->delete();
        
        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
    
    /**
     * Toggle product active status
     */
    public function toggleActive(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $product = FinishedProduct::where('company_id', $companyId)
            ->findOrFail($id);
        
        $product->update([
            'is_active' => !$product->is_active
        ]);
        
        return response()->json([
            'message' => 'Product status updated successfully',
            'product' => $product
        ]);
    }
}
