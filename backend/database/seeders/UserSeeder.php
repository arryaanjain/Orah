<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // Users for Company 1 (Orah Manufacturing)
            [
                'company_id' => 1,
                'name' => 'John Smith',
                'email' => 'john@orahmanufacturing.com',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => 1,
                'name' => 'Sarah Johnson',
                'email' => 'sarah@orahmanufacturing.com',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Users for Company 2 (Tech Solutions)
            [
                'company_id' => 2,
                'name' => 'Mike Davis',
                'email' => 'mike@techsolutions.com',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Users for Company 3 (Global Industries)
            [
                'company_id' => 3,
                'name' => 'Emily Chen',
                'email' => 'emily@globalindustries.com',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
    }
}
