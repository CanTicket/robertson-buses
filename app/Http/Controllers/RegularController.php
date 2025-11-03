<?php

namespace App\Http\Controllers;

use App\Models\DailyChecklist;
use App\Models\TaskTimer;
use Illuminate\Support\Facades\Auth;

class RegularController extends Controller
{
    /**
     * Show regular/driver dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get active timer (current shift)
        $activeTimer = TaskTimer::where('user_id', $user->id)
            ->whereNull('time_finished')
            ->orderBy('time_started', 'desc')
            ->first();
        
        // Get user's checklists
        $myChecklists = DailyChecklist::where('user_id', $user->id)
            ->with(['vehicle', 'items'])
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();
            
        $totalChecklists = DailyChecklist::where('user_id', $user->id)->count();
        $approvedChecklists = DailyChecklist::where('user_id', $user->id)
            ->where('status', 'Approved')
            ->count();
        $pendingChecklists = DailyChecklist::where('user_id', $user->id)
            ->where('status', 'Completed')
            ->count();
            
        // Check if user has completed checklist for current shift
        $hasCompletedChecklist = false;
        if ($activeTimer) {
            $hasCompletedChecklist = DailyChecklist::where('shift_timer_id', $activeTimer->id)
                ->where('status', 'Completed')
                ->exists();
        }

        return view('regular.pages.dashboard', compact(
            'activeTimer',
            'myChecklists',
            'totalChecklists',
            'approvedChecklists',
            'pendingChecklists',
            'hasCompletedChecklist'
        ));
    }
}

