<?php

namespace App\Http\Controllers;

use App\Models\DailyChecklist;
use Illuminate\Support\Facades\Auth;

class ManagerialController extends Controller
{
    /**
     * Show managerial dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get statistics
        $companyId = $user->company_id ?? null;
        
        $pendingChecklists = $companyId
            ? DailyChecklist::where('company_id', $companyId)->where('status', 'Completed')->count()
            : DailyChecklist::where('status', 'Completed')->count();
            
        $flaggedChecklists = $companyId
            ? DailyChecklist::where('company_id', $companyId)->where('status', 'Flagged')->count()
            : DailyChecklist::where('status', 'Flagged')->count();
            
        $kidsAlerts = $companyId
            ? DailyChecklist::where('company_id', $companyId)
                ->where('kids_left_alert', true)
                ->where('status', '!=', 'Approved')
                ->count()
            : DailyChecklist::where('kids_left_alert', true)
                ->where('status', '!=', 'Approved')
                ->count();
        
        $approvedToday = $companyId
            ? DailyChecklist::where('company_id', $companyId)
                ->where('status', 'Approved')
                ->whereDate('reviewed_at', today())
                ->count()
            : DailyChecklist::where('status', 'Approved')
                ->whereDate('reviewed_at', today())
                ->count();
        
        // Recent checklists needing review
        $recentChecklists = $companyId
            ? DailyChecklist::where('company_id', $companyId)
                ->whereIn('status', ['Completed', 'Flagged'])
                ->with(['vehicle', 'user', 'items'])
                ->orderByRaw('CASE WHEN kids_left_alert = 1 THEN 0 ELSE 1 END')
                ->orderBy('completed_at', 'desc')
                ->limit(10)
                ->get()
            : DailyChecklist::whereIn('status', ['Completed', 'Flagged'])
                ->with(['vehicle', 'user', 'items'])
                ->orderByRaw('CASE WHEN kids_left_alert = 1 THEN 0 ELSE 1 END')
                ->orderBy('completed_at', 'desc')
                ->limit(10)
                ->get();

        return view('managerial.pages.dashboard', compact(
            'pendingChecklists',
            'flaggedChecklists',
            'kidsAlerts',
            'approvedToday',
            'recentChecklists'
        ));
    }
}

