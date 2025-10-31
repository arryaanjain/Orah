<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            // Customers for Company 1
            [
                'company_id' => 1,
                'user_id' => 1,
                'name' => 'ABC Electronics Ltd.',
                'email' => 'orders@abcelectronics.com',
                'phone' => '+1-555-0123',
                'address' => '123 Industrial Ave, Tech City, TC 12345',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => 1,
                'user_id' => 1,
                'name' => 'XYZ Manufacturing Co.',
                'email' => 'procurement@xyzmanufacturing.com',
                'phone' => '+1-555-0456',
                'address' => '456 Factory St, Manufacturing District, MD 67890',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => 1,
                'user_id' => 2,
                'name' => 'Green Energy Solutions',
                'email' => 'supply@greenenergy.com',
                'phone' => '+1-555-0789',
                'address' => '789 Renewable Blvd, Eco City, EC 11111',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Customers for Company 2
            [
                'company_id' => 2,
                'user_id' => 3,
                'name' => 'Digital Innovations Inc.',
                'email' => 'orders@digitalinnovations.com',
                'phone' => '+1-555-0321',
                'address' => '321 Tech Park, Innovation Valley, IV 22222',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('customers')->insert($customers);
    }
}
