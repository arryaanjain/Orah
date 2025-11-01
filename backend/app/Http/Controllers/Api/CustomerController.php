<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Get all customers
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $customers = Customer::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'customers' => $customers
        ]);
    }
    
    /**
     * Create a new customer
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('customers')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);
        
        $customer = Customer::create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);
        
        return response()->json([
            'message' => 'Customer created successfully',
            'customer' => $customer
        ], 201);
    }
    
    /**
     * Update a customer
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $customer = Customer::where('company_id', $companyId)
            ->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('customers')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })->ignore($customer->id),
            ],
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);
        
        $customer->update($validated);
        
        return response()->json([
            'message' => 'Customer updated successfully',
            'customer' => $customer
        ]);
    }
    
    /**
     * Delete a customer
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $customer = Customer::where('company_id', $companyId)
            ->findOrFail($id);
        
        $customer->delete();
        
        return response()->json([
            'message' => 'Customer deleted successfully'
        ]);
    }
    
    /**
     * Get a single customer
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $customer = Customer::where('company_id', $companyId)
            ->findOrFail($id);
        
        return response()->json([
            'customer' => $customer
        ]);
    }
    
    /**
     * Batch create customers
     */
    public function batchStore(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        
        $validated = $request->validate([
            'customers' => 'required|array',
            'customers.*.name' => 'required|string|max:255',
            'customers.*.email' => 'nullable|email',
            'customers.*.phone' => 'nullable|string|max:50',
            'customers.*.address' => 'nullable|string',
        ]);
        
        $createdCustomers = [];
        
        foreach ($validated['customers'] as $customerData) {
            $customer = Customer::create([
                'company_id' => $companyId,
                'user_id' => $user->id,
                'name' => $customerData['name'],
                'email' => $customerData['email'] ?? null,
                'phone' => $customerData['phone'] ?? null,
                'address' => $customerData['address'] ?? null,
            ]);
            
            $createdCustomers[] = $customer;
        }
        
        return response()->json([
            'message' => 'Customers created successfully',
            'customers' => $createdCustomers
        ], 201);
    }
}
