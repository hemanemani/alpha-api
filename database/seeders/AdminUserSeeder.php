<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'user_name' => 'Admin',
                'email' => 'admin@admin.com',
                'mobile_number' => '9876543210',
                'password' => Hash::make('admin@123'),
                'is_admin' => 1,
            ]
        );
    }
}
