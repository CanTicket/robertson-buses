<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaskTimer;
use App\Models\User;
use Carbon\Carbon;

class TaskTimerSeeder extends Seeder
{
    public function run(): void
    {
        $regularUsers = User::where('role', 'Regular')->get();
        
        if ($regularUsers->isEmpty()) {
            $this->command->warn('⚠️  No Regular users found. Please run UserSeeder first.');
            return;
        }

        $timers = [];
        
        // Create active shifts (current day)
        foreach ($regularUsers->take(3) as $user) {
            $timers[] = [
                'user_id' => $user->id,
                'company_id' => $user->company_id ?? 1,
                'time_started' => Carbon::today()->setTime(6, 0),
                'time_finished' => null,
                'notes' => 'Current shift',
                'created_at' => Carbon::today()->setTime(6, 0),
                'updated_at' => Carbon::today()->setTime(6, 0),
            ];
        }
        
        // Create completed shifts for past 7 days
        foreach ($regularUsers as $user) {
            for ($day = 1; $day <= 7; $day++) {
                $startTime = Carbon::now()->subDays($day)->setTime(rand(6, 8), rand(0, 59));
                $endTime = (clone $startTime)->addHours(rand(7, 10));
                
                $timers[] = [
                    'user_id' => $user->id,
                    'company_id' => $user->company_id ?? 1,
                    'time_started' => $startTime,
                    'time_finished' => $endTime,
                    'notes' => null,
                    'created_at' => $startTime,
                    'updated_at' => $endTime,
                ];
            }
        }

        TaskTimer::insert($timers);

        $this->command->info('✅ Created ' . count($timers) . ' task timers (shifts)!');
    }
}

