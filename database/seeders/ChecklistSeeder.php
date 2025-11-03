<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailyChecklist;
use App\Models\ChecklistItem;
use App\Models\TaskTimer;
use App\Models\Vehicle;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = Vehicle::where('status', 'Active')->get();
        $regularUsers = User::where('role', 'Regular')->get();
        $managers = User::where('role', 'Managerial')->get();
        
        // Get all timers, both finished and active
        $timers = TaskTimer::all();

        if ($vehicles->isEmpty() || $regularUsers->isEmpty()) {
            $this->command->warn('⚠️  Need vehicles and regular users. Run VehicleSeeder and UserSeeder first.');
            return;
        }

        $checklists = [];
        $statuses = ['Completed', 'Approved', 'Flagged'];
        $tyreConditions = ['Good', 'Fair', 'Poor'];
        $fuelLevels = [85, 90, 75, 95, 80, 70];
        $kidsOptions = ['No', 'Yes'];

        // Create checklists for past 30 days
        for ($day = 0; $day < 30; $day++) {
            $date = Carbon::now()->subDays($day);
            
            // Create 2-4 checklists per day
            $checklistsPerDay = rand(2, 4);
            
            for ($i = 0; $i < $checklistsPerDay; $i++) {
                $user = $regularUsers->random();
                $vehicle = $vehicles->random();
                
                // Find or create a timer for this day and user
                $startTime = $date->copy()->setTime(rand(6, 8), rand(0, 59));
                
                $timer = $timers->firstWhere(function($t) use ($user, $date, $startTime) {
                    $timerDate = Carbon::parse($t->time_started);
                    return $t->user_id == $user->id && $timerDate->isSameDay($date);
                });
                
                if (!$timer) {
                    // Create a timer for this checklist
                    $timer = TaskTimer::create([
                        'user_id' => $user->id,
                        'company_id' => $user->company_id ?? 1,
                        'time_started' => $startTime,
                        'time_finished' => $startTime->copy()->addHours(rand(7, 10)),
                        'notes' => null,
                    ]);
                    // Refresh timers collection
                    $timers->push($timer);
                }

                // Determine status - older checklists are more likely to be approved
                $status = 'Completed';
                if ($day <= 5) {
                    $status = $statuses[array_rand($statuses)];
                }

                $tyreFront = $tyreConditions[array_rand($tyreConditions)];
                $tyreRear = $tyreConditions[array_rand($tyreConditions)];
                $fuelLevel = $fuelLevels[array_rand($fuelLevels)];
                $kidsLeft = $kidsOptions[array_rand($kidsOptions)];
                
                // Occasional kids alerts (about 5% chance)
                $hasKidsAlert = $kidsLeft === 'Yes' && rand(1, 20) === 1;

                $completedAt = $date->copy()->setTime(rand(15, 18), rand(0, 59));

                $checklist = DailyChecklist::create([
                    'checklist_uuid' => (string) Str::uuid(),
                    'shift_timer_id' => $timer->id,
                    'vehicle_id' => $vehicle->vehicle_id,
                    'user_id' => $user->id,
                    'company_id' => $user->company_id ?? 1,
                    'status' => $status,
                    'reviewed_by' => ($status === 'Approved' || $status === 'Flagged') && $managers->isNotEmpty() 
                        ? $managers->random()->id 
                        : null,
                    'reviewed_at' => ($status === 'Approved' || $status === 'Flagged') 
                        ? $completedAt->copy()->addHours(rand(1, 24)) 
                        : null,
                    'review_notes' => $status === 'Flagged' 
                        ? 'Please address tyre condition and fuel level concerns.' 
                        : ($status === 'Approved' ? 'All checks passed.' : null),
                    'kids_left_alert' => $hasKidsAlert,
                    'alert_sent' => $hasKidsAlert,
                    'completed_at' => $completedAt,
                    'created_at' => $completedAt,
                    'updated_at' => $completedAt,
                ]);

                // Create checklist items
                $items = [
                    [
                        'check_type' => 'tyre_front',
                        'check_label' => 'Front Tyres',
                        'value' => $tyreFront,
                        'sort_order' => 1,
                        'notes' => $tyreFront === 'Poor' ? 'Needs attention' : null,
                    ],
                    [
                        'check_type' => 'tyre_rear',
                        'check_label' => 'Rear Tyres',
                        'value' => $tyreRear,
                        'sort_order' => 2,
                        'notes' => $tyreRear === 'Poor' ? 'Needs attention' : null,
                    ],
                    [
                        'check_type' => 'fuel_level',
                        'check_label' => 'Fuel Level',
                        'value' => $fuelLevel . '%',
                        'sort_order' => 3,
                    ],
                    [
                        'check_type' => 'kids_check',
                        'check_label' => 'Kids Left on Bus',
                        'value' => $kidsLeft,
                        'sort_order' => 4,
                    ],
                ];

                if ($day % 3 === 0) { // Add notes to every 3rd checklist
                    $items[] = [
                        'check_type' => 'notes',
                        'check_label' => 'Additional Notes',
                        'value' => 'Vehicle in good condition. All systems operational.',
                        'sort_order' => 5,
                    ];
                }

                foreach ($items as $item) {
                    ChecklistItem::create([
                        'checklist_id' => $checklist->checklist_id,
                        ...$item,
                        'created_at' => $completedAt,
                    ]);
                }
            }
        }

        $this->command->info('✅ Created ' . DailyChecklist::count() . ' checklists with items!');
        
        // Show summary
        $this->command->info('');
        $this->command->info('📊 Checklist Summary:');
        $this->command->info('   • Completed: ' . DailyChecklist::where('status', 'Completed')->count());
        $this->command->info('   • Approved: ' . DailyChecklist::where('status', 'Approved')->count());
        $this->command->info('   • Flagged: ' . DailyChecklist::where('status', 'Flagged')->count());
        $this->command->info('   • Kids Alerts: ' . DailyChecklist::where('kids_left_alert', true)->count());
    }
}

