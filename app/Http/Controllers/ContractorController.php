<?php

namespace App\Http\Controllers;

use App\Models\DailyChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractorController extends Controller
{
    /**
     * Show contractor dashboard with assigned checklists
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get checklists assigned to this contractor
        // For now, show all checklists (can be filtered by assignment later)
        $checklists = DailyChecklist::with(['vehicle', 'user', 'items'])
            ->where('status', '!=', 'Pending')
            ->orderBy('completed_at', 'desc')
            ->paginate(15);

        return view('contractor.pages.dashboard', compact('checklists'));
    }
}

