<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RawMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Raw Materials for Company 1, User 1
        $materials = [
            [
                'company_id' => 1,
                'user_id' => 1,
                'material' => 'Steel Sheet',
                'description' => 'High-grade steel sheets for manufacturing',
                'base_unit' => 'kg',
                'minimum_stock' => 100.000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => 1,
                'user_id' => 1,
                'material' => 'Aluminum Rod',
                'description' => 'Aluminum rods for structural components',
                'base_unit' => 'pieces',
                'minimum_stock' => 50.000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => 1,
                'user_id' => 1,
                'material' => 'Plastic Granules',
                'description' => 'Raw plastic granules for injection molding',
                'base_unit' => 'kg',
                'minimum_stock' => 200.000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => 1,
                'user_id' => 2,
                'material' => 'Copper Wire',
                'description' => 'Electrical copper wiring',
                'base_unit' => 'meters',
                'minimum_stock' => 500.000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('rm_master')->insert($materials);

        // Units for the materials
        $units = [
            // Steel Sheet units
            ['company_id' => 1, 'user_id' => 1, 'material_id' => 1, 'unit_name' => 'kg', 'conversion_factor' => 1.0000, 'created_at' => now()],
            ['company_id' => 1, 'user_id' => 1, 'material_id' => 1, 'unit_name' => 'tons', 'conversion_factor' => 1000.0000, 'created_at' => now()],
            
            // Aluminum Rod units
            ['company_id' => 1, 'user_id' => 1, 'material_id' => 2, 'unit_name' => 'pieces', 'conversion_factor' => 1.0000, 'created_at' => now()],
            ['company_id' => 1, 'user_id' => 1, 'material_id' => 2, 'unit_name' => 'dozen', 'conversion_factor' => 12.0000, 'created_at' => now()],
            
            // Plastic Granules units
            ['company_id' => 1, 'user_id' => 1, 'material_id' => 3, 'unit_name' => 'kg', 'conversion_factor' => 1.0000, 'created_at' => now()],
            ['company_id' => 1, 'user_id' => 1, 'material_id' => 3, 'unit_name' => 'grams', 'conversion_factor' => 0.0010, 'created_at' => now()],
            
            // Copper Wire units
            ['company_id' => 1, 'user_id' => 2, 'material_id' => 4, 'unit_name' => 'meters', 'conversion_factor' => 1.0000, 'created_at' => now()],
            ['company_id' => 1, 'user_id' => 2, 'material_id' => 4, 'unit_name' => 'km', 'conversion_factor' => 1000.0000, 'created_at' => now()],
        ];

        DB::table('rm_master_units')->insert($units);
    }
}
