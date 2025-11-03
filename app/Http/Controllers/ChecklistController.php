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
        // Use user id directly
        $activeTimer = TaskTimer::where('user_id', $user->id)
            ->whereNull('time_finished')
            ->orderBy('time_started', 'desc')
            ->first();

        if (!$activeTimer) {
            // Create a temporary timer for the current session (for demo/testing)
            // In production, this would require clocking in first
            $activeTimer = TaskTimer::create([
                'user_id' => $user->id,
                'company_id' => $user->company_id ?? null,
                'time_started' => now(),
                'time_finished' => null,
            ]);
        }

        // Check if checklist already exists for this shift
        $existingChecklist = DailyChecklist::where('shift_timer_id', $activeTimer->id)
            ->first();

        if ($existingChecklist && $existingChecklist->isCompleted()) {
            return redirect()
                ->route('regular.checklist.show', $existingChecklist->checklist_uuid)
                ->with('info', 'Checklist already completed for this shift.');
        }

        // Get available vehicles (company_id is optional, if not exists get all active)
        $companyId = $user->company_id ?? null;
        $vehicles = $companyId 
            ? Vehicle::where('company_id', $companyId)->where('status', 'Active')->orderBy('bus_number')->get()
            : Vehicle::where('status', 'Active')->orderBy('bus_number')->get();

        // If checklist exists but not completed, use it
        // Otherwise, pass null and let the form handle creation on submit
        $checklist = $existingChecklist;

        return view('regular.pages.checklist.create', compact('checklist', 'vehicles', 'activeTimer'));
    }

    /**
     * Store checklist data
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shift_timer_id' => 'required|exists:task_timers,id',
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
            $companyId = $user->company_id ?? 1; // Default to 1 if no company_id
            
            // First, find or create the checklist with the vehicle_id
            $checklist = DailyChecklist::firstOrNew([
                'shift_timer_id' => $validated['shift_timer_id'],
                'user_id' => $user->id,
            ]);
            
            // Update or set the required fields
            $checklist->fill([
                'vehicle_id' => $validated['vehicle_id'],
                'company_id' => $companyId,
                'status' => 'Completed',
                'kids_left_alert' => $validated['kids_left'] === 'Yes',
                'completed_at' => now(),
            ]);
            
            $checklist->save();

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
        $userCompanyId = $user->company_id ?? null;
        if ($checklist->user_id !== $user->id && ($userCompanyId === null || $checklist->company_id !== $userCompanyId)) {
            // Allow if user is admin, manager, or contractor
            if (!in_array($user->role, ['Administrator', 'Managerial', 'Contractor'])) {
                abort(403, 'Unauthorized access.');
            }
        }

        // Return appropriate view based on user role
        $viewPath = match($user->role) {
            'Contractor' => 'contractor.pages.checklist.show',
            'Regular' => 'regular.pages.checklist.show',
            default => 'managerial.pages.checklist.show',
        };

        return view($viewPath, compact('checklist'));
    }

    /**
     * Manager: List checklists needing review
     */
    public function reviewIndex()
    {
        $user = Auth::user();
        
        // If company_id exists, filter by it, otherwise show all
        $companyId = $user->company_id ?? null;
        $checklists = $companyId
            ? DailyChecklist::where('company_id', $companyId)->whereIn('status', ['Completed', 'Flagged'])
            : DailyChecklist::whereIn('status', ['Completed', 'Flagged']);
        
        $checklists = $checklists->with(['vehicle', 'user', 'items'])
            ->orderByRaw('CASE WHEN kids_left_alert = 1 THEN 0 ELSE 1 END')
            ->orderBy('completed_at', 'desc')
            ->paginate(20);

        return view('admin.pages.checklist.review', compact('checklists'));
    }

    /**
     * Manager: Approve checklist
     */
    public function approve(Request $request, $uuid)
    {
        $user = Auth::user();
        
        $companyId = $user->company_id ?? null;
        $query = DailyChecklist::where('checklist_uuid', $uuid);
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        $checklist = $query->firstOrFail();

        $validated = $request->validate([
            'review_notes' => 'nullable|string|max:500',
        ]);

        $checklist->approve($user->id, $validated['review_notes'] ?? null);

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
        
        $companyId = $user->company_id ?? null;
        $query = DailyChecklist::where('checklist_uuid', $uuid);
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        $checklist = $query->firstOrFail();

        $validated = $request->validate([
            'review_notes' => 'required|string|max:500',
        ]);

        $checklist->flag($user->id, $validated['review_notes']);

        return redirect()
            ->back()
            ->with('warning', 'Checklist flagged for follow-up.');
    }

    /**
     * Send critical alert when kids left on bus
     */
    private function sendKidsLeftAlert(DailyChecklist $checklist)
    {
        // Get all managers for this company (or all if no company_id)
        $query = $checklist->company_id 
            ? \App\Models\User::where('company_id', $checklist->company_id)
            : \App\Models\User::query();
        
        $managers = $query->where('role', 'Managerial')->get();

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
        
        $activeTimer = TaskTimer::where('user_id', $user->id)
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



