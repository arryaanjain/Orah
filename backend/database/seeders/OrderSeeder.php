<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Orders
        $orders = [
            [
                'company_id' => 1,
                'user_id' => 1,
                'customer_id' => 1, // ABC Electronics Ltd.
                'product_id' => 1, // Steel Cabinet
                'qty' => 20.000,
                'unit_price' => 299.99,
                'total_amount' => 5999.80,
                'order_date' => Carbon::now()->subDays(8),
                'expected_delivery_date' => Carbon::now()->addDays(7),
                'status' => 'confirmed',
                'notes' => 'Urgent order for new facility setup',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => 1,
                'user_id' => 1,
                'customer_id' => 2, // XYZ Manufacturing Co.
                'product_id' => 2, // Plastic Container
                'qty' => 100.000,
                'unit_price' => 24.99,
                'total_amount' => 2499.00,
                'order_date' => Carbon::now()->subDays(5),
                'expected_delivery_date' => Carbon::now()->addDays(10),
                'status' => 'in_production',
                'notes' => 'Monthly bulk order',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => 1,
                'user_id' => 2,
                'customer_id' => 3, // Green Energy Solutions
                'product_id' => 3, // Electronic Control Panel
                'qty' => 5.000,
                'unit_price' => 1499.99,
                'total_amount' => 7499.95,
                'order_date' => Carbon::now()->subDays(2),
                'expected_delivery_date' => Carbon::now()->addDays(21),
                'status' => 'pending',
                'notes' => 'Custom specifications required',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('order_book')->insert($orders);

        // Sales (completed orders)
        $sales = [
            [
                'company_id' => 1,
                'user_id' => 1,
                'order_id' => 1,
                'customer_id' => 1, // ABC Electronics Ltd.
                'product_id' => 1, // Steel Cabinet
                'qty' => 10.000, // Partial delivery
                'unit_price' => 299.99,
                'total_amount' => 2999.90,
                'sale_date' => Carbon::now()->subDays(3),
                'payment_status' => 'paid',
                'notes' => 'First batch delivered and paid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => 1,
                'user_id' => 1,
                'order_id' => 2, // Added missing order_id
                'customer_id' => 2, // XYZ Manufacturing Co.
                'product_id' => 2, // Plastic Container
                'qty' => 50.000, // Partial delivery from second order
                'unit_price' => 24.99,
                'total_amount' => 1249.50,
                'sale_date' => Carbon::now()->subDays(1),
                'payment_status' => 'pending',
                'notes' => 'First half of bulk order delivered',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('sales_book')->insert($sales);

        // Stock movements for material consumption during production
        $productionMovements = [
            // Steel consumption for Cabinet production (10 units delivered)
            [
                'company_id' => 1,
                'user_id' => 1,
                'material_id' => 1, // Steel Sheet
                'movement_type' => 'consumption',
                'reference_type' => 'production',
                'reference_id' => 1, // Sale ID
                'qty_change' => -155.000, // 10 cabinets × 15.5 kg each = 155 kg consumed
                'unit_id' => 1, // kg
                'notes' => 'Steel consumption for 10 Steel Cabinets (Order #1)',
                'movement_date' => Carbon::now()->subDays(3),
            ],
            [
                'company_id' => 1,
                'user_id' => 1,
                'material_id' => 2, // Aluminum Rod
                'movement_type' => 'consumption',
                'reference_type' => 'production',
                'reference_id' => 1, // Sale ID
                'qty_change' => -40.000, // 10 cabinets × 4 pieces each = 40 pieces consumed
                'unit_id' => 3, // pieces
                'notes' => 'Aluminum rod consumption for 10 Steel Cabinets (Order #1)',
                'movement_date' => Carbon::now()->subDays(3),
            ],
            
            // Plastic consumption for Container production (50 units delivered)
            [
                'company_id' => 1,
                'user_id' => 1,
                'material_id' => 3, // Plastic Granules
                'movement_type' => 'consumption',
                'reference_type' => 'production',
                'reference_id' => 2, // Sale ID
                'qty_change' => -125.000, // 50 containers × 2.5 kg each = 125 kg consumed
                'unit_id' => 5, // kg
                'notes' => 'Plastic consumption for 50 Plastic Containers (Order #2)',
                'movement_date' => Carbon::now()->subDays(1),
            ],
        ];

        DB::table('stock_movements')->insert($productionMovements);
    }
}
