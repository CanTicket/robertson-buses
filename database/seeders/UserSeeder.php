<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Administrator User
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@robertsonbuses.com',
            'password' => Hash::make('password123'),
            'role' => 'Administrator',
            'email_verified_at' => now(),
        ]);

        // Manager User
        User::create([
            'first_name' => 'Sarah',
            'last_name' => 'Johnson',
            'email' => 'manager@robertsonbuses.com',
            'password' => Hash::make('password123'),
            'role' => 'Managerial',
            'email_verified_at' => now(),
        ]);

        // Driver/Staff Users
        User::create([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'driver1@robertsonbuses.com',
            'password' => Hash::make('password123'),
            'role' => 'Regular',
            'email_verified_at' => now(),
        ]);

        User::create([
            'first_name' => 'Emily',
            'last_name' => 'Davis',
            'email' => 'driver2@robertsonbuses.com',
            'password' => Hash::make('password123'),
            'role' => 'Regular',
            'email_verified_at' => now(),
        ]);

        User::create([
            'first_name' => 'Michael',
            'last_name' => 'Brown',
            'email' => 'driver3@robertsonbuses.com',
            'password' => Hash::make('password123'),
            'role' => 'Regular',
            'email_verified_at' => now(),
        ]);

        // Contractor User
        User::create([
            'first_name' => 'David',
            'last_name' => 'Wilson',
            'email' => 'contractor@robertsonbuses.com',
            'password' => Hash::make('password123'),
            'role' => 'Contractor',
            'email_verified_at' => now(),
        ]);

        // Additional Staff
        User::create([
            'first_name' => 'Lisa',
            'last_name' => 'Anderson',
            'email' => 'staff@robertsonbuses.com',
            'password' => Hash::make('password123'),
            'role' => 'Regular',
            'email_verified_at' => now(),
        ]);

        User::create([
            'first_name' => 'Robert',
            'last_name' => 'Martinez',
            'email' => 'driver4@robertsonbuses.com',
            'password' => Hash::make('password123'),
            'role' => 'Regular',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Created 8 sample users with roles!');
        $this->command->info('');
        $this->command->info('📋 Login Credentials (all passwords: password123):');
        $this->command->info('');
        $this->command->info('👑 Administrator:');
        $this->command->info('   • admin@robertsonbuses.com (Administrator)');
        $this->command->info('');
        $this->command->info('👔 Managerial:');
        $this->command->info('   • manager@robertsonbuses.com (Managerial)');
        $this->command->info('');
        $this->command->info('🚌 Regular (Drivers/Staff):');
        $this->command->info('   • driver1@robertsonbuses.com (Regular)');
        $this->command->info('   • driver2@robertsonbuses.com (Regular)');
        $this->command->info('   • driver3@robertsonbuses.com (Regular)');
        $this->command->info('   • driver4@robertsonbuses.com (Regular)');
        $this->command->info('   • staff@robertsonbuses.com (Regular)');
        $this->command->info('');
        $this->command->info('🏗️ Contractor:');
        $this->command->info('   • contractor@robertsonbuses.com (Contractor)');
    }
}
