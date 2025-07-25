<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupportUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(!User::where('email', env('SUPPORT_EMAIL', 'support@example.com'))->exists()) {
            User::create([
                'name' => env('SUPPORT_NAME', 'Support'),
                'email' => env('SUPPORT_EMAIL', 'support@example.com'),
                'password' =>env('SUPPORT_PASSWORD', 'abc123'),
                'role' => 'suporte'
            ]);
        }
    }
}
