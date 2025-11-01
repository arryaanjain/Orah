<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Models\RawMaterialUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RawMaterialController extends Controller
{
    /**
     * Get all raw materials for the company
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $materials = RawMaterial::where('company_id', $companyId)
            ->with('units')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'materials' => $materials
        ]);
    }
    
    /**
     * Create a new raw material
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $validated = $request->validate([
            'material' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rm_master')->where(function ($query) use ($companyId, $user) {
                    return $query->where('company_id', $companyId)
                                ->where('user_id', $user->id);
                }),
            ],
            'description' => 'nullable|string',
            'base_unit' => 'required|string|max:50',
            'minimum_stock' => 'nullable|numeric|min:0',
        ]);
        
        $material = RawMaterial::create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'material' => $validated['material'],
            'description' => $validated['description'] ?? null,
            'base_unit' => $validated['base_unit'],
            'minimum_stock' => $validated['minimum_stock'] ?? 0,
        ]);
        
        return response()->json([
            'message' => 'Raw material created successfully',
            'material' => $material->load('units')
        ], 201);
    }
    
    /**
     * Update a raw material
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $material = RawMaterial::where('company_id', $companyId)
            ->findOrFail($id);
        
        $validated = $request->validate([
            'material' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rm_master')->where(function ($query) use ($companyId, $user) {
                    return $query->where('company_id', $companyId)
                                ->where('user_id', $user->id);
                })->ignore($material->id),
            ],
            'description' => 'nullable|string',
            'base_unit' => 'required|string|max:50',
            'minimum_stock' => 'nullable|numeric|min:0',
        ]);
        
        $material->update($validated);
        
        return response()->json([
            'message' => 'Raw material updated successfully',
            'material' => $material->load('units')
        ]);
    }
    
    /**
     * Delete a raw material
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $material = RawMaterial::where('company_id', $companyId)
            ->findOrFail($id);
        
        $material->delete();
        
        return response()->json([
            'message' => 'Raw material deleted successfully'
        ]);
    }
    
    /**
     * Get all units for a specific material
     */
    public function getUnits(Request $request, $materialId)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $units = RawMaterialUnit::whereHas('material', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
        ->where('material_id', $materialId)
        ->get();
        
        return response()->json([
            'units' => $units
        ]);
    }
    
    /**
     * Add a unit to a material
     */
    public function addUnit(Request $request, $materialId)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        // Verify material belongs to company
        $material = RawMaterial::where('company_id', $companyId)
            ->findOrFail($materialId);
        
        $validated = $request->validate([
            'unit_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('rm_master_units')->where(function ($query) use ($companyId, $user, $materialId) {
                    return $query->where('company_id', $companyId)
                                ->where('user_id', $user->id)
                                ->where('material_id', $materialId);
                }),
            ],
            'conversion_factor' => 'required|numeric|min:0',
        ]);
        
        $unit = RawMaterialUnit::create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'material_id' => $materialId,
            'unit_name' => $validated['unit_name'],
            'conversion_factor' => $validated['conversion_factor'],
        ]);
        
        return response()->json([
            'message' => 'Unit added successfully',
            'unit' => $unit
        ], 201);
    }
    
    /**
     * Delete a unit
     */
    public function deleteUnit(Request $request, $materialId, $unitId)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $unit = RawMaterialUnit::where('company_id', $companyId)
            ->where('material_id', $materialId)
            ->findOrFail($unitId);
        
        $unit->delete();
        
        return response()->json([
            'message' => 'Unit deleted successfully'
        ]);
    }
    
    /**
     * Batch create materials, units, and customers
     */
    public function batchStore(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $validated = $request->validate([
            'materials' => 'nullable|array',
            'materials.*.name' => 'required|string|max:255',
            'materials.*.base_unit' => 'required|string|max:50',
            'units' => 'nullable|array',
            'units.*.name' => 'required|string|max:50',
        ]);
        
        DB::beginTransaction();
        
        try {
            $createdMaterials = [];
            
            // Create materials
            if (!empty($validated['materials'])) {
                foreach ($validated['materials'] as $materialData) {
                    $material = RawMaterial::create([
                        'company_id' => $companyId,
                        'user_id' => $user->id,
                        'material' => $materialData['name'],
                        'base_unit' => $materialData['base_unit'],
                        'minimum_stock' => 0,
                    ]);
                    
                    $createdMaterials[] = $material;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Data created successfully',
                'materials' => $createdMaterials,
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
