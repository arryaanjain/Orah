<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Finished Products
        $products = [
            [
                'company_id' => 1,
                'user_id' => 1,
                'product_name' => 'Steel Cabinet',
                'description' => 'Heavy-duty steel storage cabinet',
                'base_unit' => 'pieces',
                'selling_price' => 299.99,
                'minimum_stock' => 10.000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => 1,
                'user_id' => 1,
                'product_name' => 'Plastic Container',
                'description' => 'Food-grade plastic storage container',
                'base_unit' => 'pieces',
                'selling_price' => 24.99,
                'minimum_stock' => 50.000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => 1,
                'user_id' => 2,
                'product_name' => 'Electronic Control Panel',
                'description' => 'Industrial control panel with copper wiring',
                'base_unit' => 'pieces',
                'selling_price' => 1499.99,
                'minimum_stock' => 5.000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('finished_products')->insert($products);

        // Product Bill of Materials (BOM)
        $bom = [
            // Steel Cabinet BOM (Product 1)
            [
                'company_id' => 1,
                'user_id' => 1,
                'product_id' => 1,
                'material_id' => 1, // Steel Sheet
                'qty_required' => 15.5000, // 15.5 kg per cabinet
                'unit_id' => 1, // kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => 1,
                'user_id' => 1,
                'product_id' => 1,
                'material_id' => 2, // Aluminum Rod
                'qty_required' => 4.0000, // 4 pieces per cabinet
                'unit_id' => 3, // pieces
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Plastic Container BOM (Product 2)
            [
                'company_id' => 1,
                'user_id' => 1,
                'product_id' => 2,
                'material_id' => 3, // Plastic Granules
                'qty_required' => 2.5000, // 2.5 kg per container
                'unit_id' => 5, // kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Electronic Control Panel BOM (Product 3)
            [
                'company_id' => 1,
                'user_id' => 2,
                'product_id' => 3,
                'material_id' => 4, // Copper Wire
                'qty_required' => 25.0000, // 25 meters per panel
                'unit_id' => 7, // meters
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => 1,
                'user_id' => 2,
                'product_id' => 3,
                'material_id' => 1, // Steel Sheet (for panel housing)
                'qty_required' => 5.0000, // 5 kg per panel
                'unit_id' => 1, // kg
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('product_bom')->insert($bom);
    }
}
