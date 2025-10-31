<?php

namespace App\Http\Controllers;

use App\Models\DailyChecklist;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChecklistReportController extends Controller
{
    /**
     * Display checklist reports dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        return view('admin.pages.reports.checklist-index');
    }

    /**
     * Fetch checklist completion report data
     */
    public function fetchReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'vehicle_id' => 'nullable|exists:vehicles,vehicle_id',
            'user_id' => 'nullable|string',
            'status' => 'nullable|in:Pending,Completed,Approved,Flagged',
        ]);

        $user = Auth::user();
        $startDate = $validated['start_date'];
        $endDate = date('Y-m-d', strtotime($validated['end_date'] . ' +1 day'));

        // Build query
        $query = DailyChecklist::with(['vehicle', 'user', 'reviewer', 'items'])
            ->where('company_id', $user->company_id)
            ->whereBetween('completed_at', [$startDate, $endDate]);

        // Apply filters
        if (!empty($validated['vehicle_id'])) {
            $query->where('vehicle_id', $validated['vehicle_id']);
        }

        if (!empty($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $checklists = $query->orderBy('completed_at', 'desc')->get();

        // Calculate statistics
        $stats = [
            'total' => $checklists->count(),
            'completed' => $checklists->where('status', 'Completed')->count(),
            'approved' => $checklists->where('status', 'Approved')->count(),
            'flagged' => $checklists->where('status', 'Flagged')->count(),
            'kids_alerts' => $checklists->where('kids_left_alert', true)->count(),
            'completion_rate' => 0,
        ];

        // Calculate completion rate (approved / total)
        if ($stats['total'] > 0) {
            $stats['completion_rate'] = round(($stats['approved'] / $stats['total']) * 100, 1);
        }

        // Format records for table
        $records = $checklists->map(function ($checklist) {
            $issues = $checklist->items->filter(function($item) {
                return $item->hasProblem();
            })->count();

            return [
                'checklist_uuid' => $checklist->checklist_uuid,
                'date' => $checklist->completed_at->format('d/m/Y'),
                'time' => $checklist->completed_at->format('H:i'),
                'vehicle' => $checklist->vehicle->bus_number,
                'driver' => trim($checklist->user->first_name . ' ' . $checklist->user->last_name),
                'status' => $checklist->status,
                'kids_alert' => $checklist->kids_left_alert,
                'issues_count' => $issues,
                'reviewed_by' => $checklist->reviewer ? trim($checklist->reviewer->first_name . ' ' . $checklist->reviewer->last_name) : '-',
                'photos' => $checklist->photos->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'records' => $records,
        ]);
    }

    /**
     * Export checklist report to CSV
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'vehicle_id' => 'nullable|exists:vehicles,vehicle_id',
        ]);

        $user = Auth::user();
        $startDate = $validated['start_date'];
        $endDate = date('Y-m-d', strtotime($validated['end_date'] . ' +1 day'));

        $query = DailyChecklist::with(['vehicle', 'user', 'reviewer', 'items'])
            ->where('company_id', $user->company_id)
            ->whereBetween('completed_at', [$startDate, $endDate]);

        if (!empty($validated['vehicle_id'])) {
            $query->where('vehicle_id', $validated['vehicle_id']);
        }

        $checklists = $query->orderBy('completed_at', 'desc')->get();

        // Generate CSV
        $filename = 'checklist_report_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($checklists) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Date',
                'Time',
                'Vehicle',
                'Driver',
                'Status',
                'Kids Alert',
                'Front Tyres',
                'Rear Tyres',
                'Fuel Level',
                'Issues',
                'Reviewed By',
                'Photos',
            ]);

            // Data
            foreach ($checklists as $checklist) {
                $items = $checklist->items->keyBy('check_type');
                
                fputcsv($file, [
                    $checklist->completed_at->format('d/m/Y'),
                    $checklist->completed_at->format('H:i'),
                    $checklist->vehicle->bus_number,
                    trim($checklist->user->first_name . ' ' . $checklist->user->last_name),
                    $checklist->status,
                    $checklist->kids_left_alert ? 'YES' : 'NO',
                    $items->get('tyre_front')->value ?? '-',
                    $items->get('tyre_rear')->value ?? '-',
                    $items->get('fuel_level')->value ?? '-',
                    $checklist->items->filter(fn($i) => $i->hasProblem())->count(),
                    $checklist->reviewer ? trim($checklist->reviewer->first_name . ' ' . $checklist->reviewer->last_name) : '-',
                    $checklist->photos->count(),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get summary stats for dashboard
     */
    public function getDashboardStats()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $weekAgo = Carbon::today()->subDays(7);

        $stats = [
            'today' => DailyChecklist::where('company_id', $user->company_id)
                ->whereDate('completed_at', $today)
                ->count(),
            
            'this_week' => DailyChecklist::where('company_id', $user->company_id)
                ->whereBetween('completed_at', [$weekAgo, now()])
                ->count(),
            
            'pending_review' => DailyChecklist::where('company_id', $user->company_id)
                ->where('status', 'Completed')
                ->whereNull('reviewed_by')
                ->count(),
            
            'flagged' => DailyChecklist::where('company_id', $user->company_id)
                ->where('status', 'Flagged')
                ->count(),
            
            'kids_alerts_week' => DailyChecklist::where('company_id', $user->company_id)
                ->where('kids_left_alert', true)
                ->whereBetween('completed_at', [$weekAgo, now()])
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get checklist trends for charts
     */
    public function getTrends(Request $request)
    {
        $user = Auth::user();
        $days = $request->input('days', 30);
        $startDate = Carbon::today()->subDays($days);

        $dailyStats = DailyChecklist::where('company_id', $user->company_id)
            ->whereBetween('completed_at', [$startDate, now()])
            ->select(
                DB::raw('DATE(completed_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "Approved" THEN 1 ELSE 0 END) as approved'),
                DB::raw('SUM(CASE WHEN kids_left_alert = 1 THEN 1 ELSE 0 END) as alerts')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($dailyStats);
    }
}



