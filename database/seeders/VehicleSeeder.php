<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            [
                'bus_number' => 'Bus 101',
                'registration_number' => 'RB-101',
                'make' => 'Mercedes-Benz',
                'model' => 'Sprinter',
                'year' => 2022,
                'capacity' => 30,
                'status' => 'Active',
                'company_id' => 1,
                'notes' => 'Primary route bus',
                'date_added' => now()->subMonths(6),
            ],
            [
                'bus_number' => 'Bus 102',
                'registration_number' => 'RB-102',
                'make' => 'Ford',
                'model' => 'Transit',
                'year' => 2021,
                'capacity' => 25,
                'status' => 'Active',
                'company_id' => 1,
                'notes' => null,
                'date_added' => now()->subMonths(5),
            ],
            [
                'bus_number' => 'Bus 103',
                'registration_number' => 'RB-103',
                'make' => 'Toyota',
                'model' => 'Coaster',
                'year' => 2023,
                'capacity' => 35,
                'status' => 'Active',
                'company_id' => 1,
                'notes' => 'New addition',
                'date_added' => now()->subMonths(2),
            ],
            [
                'bus_number' => 'Bus 104',
                'registration_number' => 'RB-104',
                'make' => 'Mercedes-Benz',
                'model' => 'Sprinter',
                'year' => 2020,
                'capacity' => 28,
                'status' => 'Maintenance',
                'company_id' => 1,
                'notes' => 'Scheduled maintenance',
                'date_added' => now()->subMonths(8),
            ],
            [
                'bus_number' => 'Bus 105',
                'registration_number' => 'RB-105',
                'make' => 'Iveco',
                'model' => 'Daily',
                'year' => 2019,
                'capacity' => 32,
                'status' => 'Active',
                'company_id' => 1,
                'notes' => null,
                'date_added' => now()->subMonths(12),
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::create($vehicle);
        }

        $this->command->info('✅ Created ' . count($vehicles) . ' vehicles!');
    }
}

