<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\DailyChecklist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AIInsightsController extends Controller
{
    /**
     * Get AI-powered insights for dashboard
     */
    public function getInsights()
    {
        $user = Auth::user();
        $companyId = $user->company_id ?? null;
        
        $insights = [];
        
        // 1. Predictive Maintenance Insights
        $insights['maintenance'] = $this->getMaintenanceInsights($companyId);
        
        // 2. Checklist Pattern Analysis
        $insights['patterns'] = $this->getPatternInsights($companyId);
        
        // 3. Safety Trends
        $insights['safety'] = $this->getSafetyInsights($companyId);
        
        // 4. Performance Recommendations
        $insights['recommendations'] = $this->getRecommendations($companyId);
        
        return response()->json($insights);
    }
    
    protected function getMaintenanceInsights($companyId)
    {
        // Find vehicles with frequent issues
        $vehiclesWithIssues = DailyChecklist::where('status', 'Flagged')
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->select('vehicle_id', DB::raw('COUNT(*) as flag_count'))
            ->groupBy('vehicle_id')
            ->having('flag_count', '>=', 2)
            ->with('vehicle')
            ->get()
            ->map(fn($item) => [
                'vehicle' => $item->vehicle->bus_number ?? 'Unknown',
                'flags' => $item->flag_count,
                'recommendation' => $item->flag_count >= 3 
                    ? 'Schedule maintenance inspection' 
                    : 'Monitor closely'
            ]);
            
        return [
            'title' => 'Predictive Maintenance',
            'type' => 'warning',
            'items' => $vehiclesWithIssues->toArray(),
            'icon' => 'bi-tools'
        ];
    }
    
    protected function getPatternInsights($companyId)
    {
        // Analyze checklist completion patterns
        $recentChecklists = DailyChecklist::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->where('completed_at', '>=', now()->subDays(30))
            ->count();
            
        $previousPeriod = DailyChecklist::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->whereBetween('completed_at', [now()->subDays(60), now()->subDays(30)])
            ->count();
            
        $trend = $recentChecklists > $previousPeriod ? 'increasing' : 'decreasing';
        $changePercent = $previousPeriod > 0 
            ? round((($recentChecklists - $previousPeriod) / $previousPeriod) * 100, 1)
            : 0;
            
        return [
            'title' => 'Completion Trends',
            'type' => $trend === 'increasing' ? 'success' : 'info',
            'message' => "Checklist completion is {$trend} ({$changePercent}% change)",
            'icon' => $trend === 'increasing' ? 'bi-arrow-up-circle' : 'bi-arrow-down-circle'
        ];
    }
    
    protected function getSafetyInsights($companyId)
    {
        // Kids left alerts analysis
        $recentAlerts = DailyChecklist::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->where('kids_left_alert', true)
            ->where('completed_at', '>=', now()->subDays(7))
            ->count();
            
        $avgAlerts = DailyChecklist::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->where('kids_left_alert', true)
            ->where('completed_at', '>=', now()->subDays(30))
            ->count() / 4; // Average per week
            
        $status = $recentAlerts > $avgAlerts ? 'critical' : 'good';
        
        return [
            'title' => 'Safety Analysis',
            'type' => $status,
            'message' => $recentAlerts > 0 
                ? "{$recentAlerts} kids left alerts in past week. Review protocols."
                : "No kids left alerts. Great safety record!",
            'icon' => $status === 'critical' ? 'bi-exclamation-triangle-fill' : 'bi-shield-check'
        ];
    }
    
    protected function getRecommendations($companyId)
    {
        $recommendations = [];
        
        // Check for vehicles with no recent checklists
        $vehicles = Vehicle::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->where('status', 'Active')
            ->get();
            
        foreach ($vehicles as $vehicle) {
            $recentChecklist = DailyChecklist::where('vehicle_id', $vehicle->vehicle_id)
                ->where('completed_at', '>=', now()->subDays(7))
                ->exists();
                
            if (!$recentChecklist) {
                $recommendations[] = [
                    'type' => 'action',
                    'message' => "{$vehicle->bus_number} hasn't been used in 7+ days",
                    'action' => 'Review vehicle assignment'
                ];
            }
        }
        
        // Check for pending reviews
        $pendingReviews = DailyChecklist::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->where('status', 'Completed')
            ->where('completed_at', '<=', now()->subHours(24))
            ->count();
            
        if ($pendingReviews > 0) {
            $recommendations[] = [
                'type' => 'urgent',
                'message' => "{$pendingReviews} checklists pending review for over 24 hours",
                'action' => 'Review now'
            ];
        }
        
        return [
            'title' => 'Smart Recommendations',
            'items' => $recommendations,
            'icon' => 'bi-lightbulb'
        ];
    }
}

