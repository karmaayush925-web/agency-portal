<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User
        User::create([
            'name' => 'Agency Admin',
            'email' => 'admin@agency.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Project Manager
        $manager = User::create([
            'name' => 'Rahul Manager',
            'email' => 'manager@agency.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
        ]);

        // 3. Client User & Client Profile
        $clientUser = User::create([
            'name' => 'Acme Corp Client',
            'email' => 'client@acme.com',
            'password' => Hash::make('password'),
            'role' => 'client',
        ]);

        $client = Client::create([
            'user_id' => $clientUser->id,
            'company_name' => 'Acme Technologies Pvt Ltd',
            'contact_person' => 'Vikram Sharma',
            'email' => 'client@acme.com',
            'phone' => '+91-9876543210',
            'address' => 'Jaipur, Rajasthan',
            'tax_number' => '08AABCA1234D1Z5',
        ]);

        // 4. Sample Project
        Project::create([
            'client_id' => $client->id,
            'manager_id' => $manager->id,
            'title' => 'E-Commerce WooCommerce Migration',
            'description' => 'Migrate legacy store to modern architecture with payment gateway.',
            'hourly_rate' => 1500.00,
            'status' => 'active',
            'deadline' => now()->addDays(30),
        ]);
    }
}