<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\DailyChecklist;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get statistics
        $companyId = $user->company_id ?? null;
        
        $totalVehicles = $companyId 
            ? Vehicle::where('company_id', $companyId)->count()
            : Vehicle::count();
            
        $activeVehicles = $companyId
            ? Vehicle::where('company_id', $companyId)->where('status', 'Active')->count()
            : Vehicle::where('status', 'Active')->count();
            
        $totalChecklists = $companyId
            ? DailyChecklist::where('company_id', $companyId)->count()
            : DailyChecklist::count();
            
        $pendingChecklists = $companyId
            ? DailyChecklist::where('company_id', $companyId)->where('status', 'Completed')->count()
            : DailyChecklist::where('status', 'Completed')->count();
            
        $flaggedChecklists = $companyId
            ? DailyChecklist::where('company_id', $companyId)->where('status', 'Flagged')->count()
            : DailyChecklist::where('status', 'Flagged')->count();
            
        $kidsAlerts = $companyId
            ? DailyChecklist::where('company_id', $companyId)->where('kids_left_alert', true)->count()
            : DailyChecklist::where('kids_left_alert', true)->count();
        
        // Recent checklists
        $recentChecklists = $companyId
            ? DailyChecklist::where('company_id', $companyId)
                ->with(['vehicle', 'user'])
                ->orderBy('completed_at', 'desc')
                ->limit(5)
                ->get()
            : DailyChecklist::with(['vehicle', 'user'])
                ->orderBy('completed_at', 'desc')
                ->limit(5)
                ->get();

        return view('admin.pages.dashboard', compact(
            'totalVehicles',
            'activeVehicles',
            'totalChecklists',
            'pendingChecklists',
            'flaggedChecklists',
            'kidsAlerts',
            'recentChecklists'
        ));
    }
}

