<?php

namespace App\Http\Controllers;

use App\Models\DailyChecklist;
use App\Models\ChecklistItem;
use App\Models\ChecklistPhoto;
use App\Models\Vehicle;
use App\Models\TaskTimer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Notifications\KidsLeftOnBusAlert;

class ChecklistController extends Controller
{
    /**
     * Show checklist form for current shift
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Get the active timer (current shift)
        $activeTimer = TaskTimer::where('user_id', $user->system_user_id)
            ->whereNull('time_finished')
            ->orderBy('time_started', 'desc')
            ->first();

        if (!$activeTimer) {
            return redirect()
                ->route('regular.dashboard')
                ->with('error', 'No active shift found. Please clock in first.');
        }

        // Check if checklist already exists for this shift
        $existingChecklist = DailyChecklist::where('shift_timer_id', $activeTimer->id)
            ->first();

        if ($existingChecklist && $existingChecklist->isCompleted()) {
            return redirect()
                ->route('regular.checklist.show', $existingChecklist->checklist_uuid)
                ->with('info', 'Checklist already completed for this shift.');
        }

        // Get available vehicles
        $vehicles = Vehicle::where('company_id', $user->company_id)
            ->where('status', 'Active')
            ->orderBy('bus_number')
            ->get();

        // Get or create pending checklist
        $checklist = $existingChecklist ?? DailyChecklist::create([
            'shift_timer_id' => $activeTimer->id,
            'vehicle_id' => $request->input('vehicle_id'),
            'user_id' => $user->system_user_id,
            'company_id' => $user->company_id,
            'status' => 'Pending',
        ]);

        return view('regular.pages.checklist.create', compact('checklist', 'vehicles', 'activeTimer'));
    }

    /**
     * Store checklist data
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shift_timer_id' => 'required|exists:task_timer,id',
            'vehicle_id' => 'required|exists:vehicles,vehicle_id',
            'tyre_front' => 'required|in:Good,Fair,Poor',
            'tyre_rear' => 'required|in:Good,Fair,Poor',
            'fuel_level' => 'required|numeric|min:0|max:100',
            'kids_left' => 'required|in:No,Yes',
            'notes' => 'nullable|string|max:1000',
            'photos.*' => 'nullable|image|mimes:jpg,jpeg,png|max:5120', // 5MB max
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            // Create or update checklist
            $checklist = DailyChecklist::updateOrCreate(
                [
                    'shift_timer_id' => $validated['shift_timer_id'],
                    'user_id' => $user->system_user_id,
                ],
                [
                    'vehicle_id' => $validated['vehicle_id'],
                    'company_id' => $user->company_id,
                    'status' => 'Completed',
                    'kids_left_alert' => $validated['kids_left'] === 'Yes',
                    'completed_at' => now(),
                ]
            );

            // Store checklist items
            $checklistData = [
                ['check_type' => 'tyre_front', 'check_label' => 'Front Tyres', 'value' => $validated['tyre_front'], 'sort_order' => 1],
                ['check_type' => 'tyre_rear', 'check_label' => 'Rear Tyres', 'value' => $validated['tyre_rear'], 'sort_order' => 2],
                ['check_type' => 'fuel_level', 'check_label' => 'Fuel Level', 'value' => $validated['fuel_level'] . '%', 'sort_order' => 3],
                ['check_type' => 'kids_check', 'check_label' => 'Kids Left on Bus', 'value' => $validated['kids_left'], 'sort_order' => 4],
            ];

            if (!empty($validated['notes'])) {
                $checklistData[] = ['check_type' => 'notes', 'check_label' => 'Additional Notes', 'value' => $validated['notes'], 'sort_order' => 5];
            }

            // Delete existing items and recreate
            ChecklistItem::where('checklist_id', $checklist->checklist_id)->delete();
            
            foreach ($checklistData as $item) {
                ChecklistItem::create([
                    'checklist_id' => $checklist->checklist_id,
                    ...$item
                ]);
            }

            // Handle photo uploads
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    $filename = time() . '_' . $index . '_' . $photo->getClientOriginalName();
                    $path = $photo->storeAs('checklist_photos/' . $checklist->checklist_uuid, $filename, 'public');

                    ChecklistPhoto::create([
                        'checklist_id' => $checklist->checklist_id,
                        'photo_path' => $path,
                        'photo_type' => 'exterior',
                        'original_filename' => $photo->getClientOriginalName(),
                        'file_size' => round($photo->getSize() / 1024), // KB
                        'mime_type' => $photo->getMimeType(),
                    ]);
                }
            }

            // Send alert if kids left on bus
            if ($validated['kids_left'] === 'Yes' && !$checklist->alert_sent) {
                $this->sendKidsLeftAlert($checklist);
                $checklist->update(['alert_sent' => true]);
            }

            DB::commit();

            return redirect()
                ->route('regular.checklist.show', $checklist->checklist_uuid)
                ->with('success', 'Checklist completed successfully. You may now clock out.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to save checklist: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified checklist
     */
    public function show($uuid)
    {
        $user = Auth::user();
        
        $checklist = DailyChecklist::where('checklist_uuid', $uuid)
            ->with(['vehicle', 'user', 'reviewer', 'items', 'photos', 'shiftTimer'])
            ->firstOrFail();

        // Check authorization
        if ($checklist->user_id !== $user->system_user_id && $checklist->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access.');
        }

        return view('regular.pages.checklist.show', compact('checklist'));
    }

    /**
     * Manager: List checklists needing review
     */
    public function reviewIndex()
    {
        $user = Auth::user();
        
        $checklists = DailyChecklist::where('company_id', $user->company_id)
            ->whereIn('status', ['Completed', 'Flagged'])
            ->with(['vehicle', 'user', 'items'])
            ->orderByRaw('CASE WHEN kids_left_alert = 1 THEN 0 ELSE 1 END')
            ->orderBy('completed_at', 'desc')
            ->paginate(20);

        return view('managerial.pages.checklist.review', compact('checklists'));
    }

    /**
     * Manager: Approve checklist
     */
    public function approve(Request $request, $uuid)
    {
        $user = Auth::user();
        
        $checklist = DailyChecklist::where('checklist_uuid', $uuid)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $validated = $request->validate([
            'review_notes' => 'nullable|string|max:500',
        ]);

        $checklist->approve($user->system_user_id, $validated['review_notes'] ?? null);

        return redirect()
            ->back()
            ->with('success', 'Checklist approved.');
    }

    /**
     * Manager: Flag checklist for follow-up
     */
    public function flag(Request $request, $uuid)
    {
        $user = Auth::user();
        
        $checklist = DailyChecklist::where('checklist_uuid', $uuid)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $validated = $request->validate([
            'review_notes' => 'required|string|max:500',
        ]);

        $checklist->flag($user->system_user_id, $validated['review_notes']);

        return redirect()
            ->back()
            ->with('warning', 'Checklist flagged for follow-up.');
    }

    /**
     * Send critical alert when kids left on bus
     */
    private function sendKidsLeftAlert(DailyChecklist $checklist)
    {
        // Get all managers for this company
        $managers = \App\Models\User::where('company_id', $checklist->company_id)
            ->whereHas('role', function($query) {
                $query->where('user_access', 'Managerial');
            })
            ->get();

        foreach ($managers as $manager) {
            // Send notification (email + in-app)
            $manager->notify(new KidsLeftOnBusAlert($checklist));
        }

        // Log critical event
        \Log::critical('KIDS LEFT ON BUS ALERT', [
            'checklist_id' => $checklist->checklist_id,
            'vehicle_id' => $checklist->vehicle_id,
            'driver_id' => $checklist->user_id,
            'time' => now(),
        ]);
    }

    /**
     * Check if current user can clock out (has completed checklist)
     */
    public function canClockOut()
    {
        $user = Auth::user();
        
        $activeTimer = TaskTimer::where('user_id', $user->system_user_id)
            ->whereNull('time_finished')
            ->first();

        if (!$activeTimer) {
            return response()->json(['can_clock_out' => true, 'message' => 'No active shift.']);
        }

        $checklist = DailyChecklist::where('shift_timer_id', $activeTimer->id)
            ->where('status', 'Completed')
            ->first();

        if (!$checklist) {
            return response()->json([
                'can_clock_out' => false,
                'message' => 'Please complete your end-of-day safety checklist before clocking out.',
                'checklist_url' => route('regular.checklist.create')
            ]);
        }

        return response()->json([
            'can_clock_out' => true,
            'message' => 'Checklist completed. You may clock out.',
            'checklist_uuid' => $checklist->checklist_uuid
        ]);
    }
}



