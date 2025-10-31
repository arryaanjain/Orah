<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $purchases = [
            // Steel Sheet purchases
            [
                'company_id' => 1,
                'user_id' => 1,
                'material_id' => 1,
                'supplier_name' => 'MetalWorks Inc.',
                'purchase_date' => Carbon::now()->subDays(10),
                'qty' => 500.000,
                'unit_id' => 1, // kg
                'rate' => 2.50,
                'total_amount' => 1250.00,
                'batch_number' => 'ST001-2025',
                'expiry_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => 1,
                'user_id' => 1,
                'material_id' => 1,
                'supplier_name' => 'Steel Suppliers Ltd.',
                'purchase_date' => Carbon::now()->subDays(5),
                'qty' => 1.000, // 1 ton = 1000 kg
                'unit_id' => 2, // tons
                'rate' => 2400.00,
                'total_amount' => 2400.00,
                'batch_number' => 'ST002-2025',
                'expiry_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Aluminum Rod purchases
            [
                'company_id' => 1,
                'user_id' => 1,
                'material_id' => 2,
                'supplier_name' => 'Aluminum Solutions',
                'purchase_date' => Carbon::now()->subDays(7),
                'qty' => 100.000,
                'unit_id' => 3, // pieces
                'rate' => 15.75,
                'total_amount' => 1575.00,
                'batch_number' => 'AL001-2025',
                'expiry_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Plastic Granules purchases
            [
                'company_id' => 1,
                'user_id' => 1,
                'material_id' => 3,
                'supplier_name' => 'Polymer Industries',
                'purchase_date' => Carbon::now()->subDays(3),
                'qty' => 250.000,
                'unit_id' => 5, // kg
                'rate' => 3.20,
                'total_amount' => 800.00,
                'batch_number' => 'PL001-2025',
                'expiry_date' => Carbon::now()->addYears(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Copper Wire purchases
            [
                'company_id' => 1,
                'user_id' => 2,
                'material_id' => 4,
                'supplier_name' => 'Electrical Supplies Co.',
                'purchase_date' => Carbon::now()->subDays(2),
                'qty' => 1.000, // 1 km = 1000 meters
                'unit_id' => 8, // km
                'rate' => 450.00,
                'total_amount' => 450.00,
                'batch_number' => 'CU001-2025',
                'expiry_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('rm_purchase')->insert($purchases);

        // Stock Movements for purchases
        $stockMovements = [
            // Steel Sheet stock movements
            [
                'company_id' => 1,
                'user_id' => 1,
                'material_id' => 1,
                'movement_type' => 'purchase',
                'reference_type' => 'purchase',
                'reference_id' => 1,
                'qty_change' => 500.000,
                'unit_id' => 1,
                'notes' => 'Purchase from MetalWorks Inc.',
                'movement_date' => Carbon::now()->subDays(10),
            ],
            [
                'company_id' => 1,
                'user_id' => 1,
                'material_id' => 1,
                'movement_type' => 'purchase',
                'reference_type' => 'purchase',
                'reference_id' => 2,
                'qty_change' => 1000.000, // 1 ton converted to kg
                'unit_id' => 1, // converted to base unit (kg)
                'notes' => 'Purchase from Steel Suppliers Ltd. (1 ton)',
                'movement_date' => Carbon::now()->subDays(5),
            ],
            
            // Aluminum Rod stock movements
            [
                'company_id' => 1,
                'user_id' => 1,
                'material_id' => 2,
                'movement_type' => 'purchase',
                'reference_type' => 'purchase',
                'reference_id' => 3,
                'qty_change' => 100.000,
                'unit_id' => 3,
                'notes' => 'Purchase from Aluminum Solutions',
                'movement_date' => Carbon::now()->subDays(7),
            ],
            
            // Plastic Granules stock movements
            [
                'company_id' => 1,
                'user_id' => 1,
                'material_id' => 3,
                'movement_type' => 'purchase',
                'reference_type' => 'purchase',
                'reference_id' => 4,
                'qty_change' => 250.000,
                'unit_id' => 5,
                'notes' => 'Purchase from Polymer Industries',
                'movement_date' => Carbon::now()->subDays(3),
            ],
            
            // Copper Wire stock movements
            [
                'company_id' => 1,
                'user_id' => 2,
                'material_id' => 4,
                'movement_type' => 'purchase',
                'reference_type' => 'purchase',
                'reference_id' => 5,
                'qty_change' => 1000.000, // 1 km converted to meters
                'unit_id' => 7, // converted to base unit (meters)
                'notes' => 'Purchase from Electrical Supplies Co. (1 km)',
                'movement_date' => Carbon::now()->subDays(2),
            ],
        ];

        DB::table('stock_movements')->insert($stockMovements);
    }
}
