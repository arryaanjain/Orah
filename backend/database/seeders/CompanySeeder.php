<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Orah Manufacturing Ltd.',
                'email' => 'info@orahmanufacturing.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tech Solutions Inc.',
                'email' => 'admin@techsolutions.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Global Industries',
                'email' => 'contact@globalindustries.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('companies')->insert($companies);
    }
}
