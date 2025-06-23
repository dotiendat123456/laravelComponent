<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'superadmin@khgc.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Super',
                'email' => 'superadmin@khgc.com',
                'password' => Hash::make('Abcd@1234'),
                'address' => 'Admin Address',
                'status' => 1,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
