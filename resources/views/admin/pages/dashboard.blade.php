@extends('admin.components.app')

@section('page-title', 'Admin Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-0">Admin Dashboard</h2>
            <p class="text-muted">Welcome back, {{ auth()->user()->first_name }}! Here's your overview.</p>
        </div>
    </div>

    <!-- AI Quick Stats Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-1"><i class="bi bi-robot me-2"></i>AI-Powered Analytics Active</h5>
                            <p class="mb-0 opacity-75">Get real-time insights with one click →</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-light btn-sm" onclick="toggleAIInsights()">
                                <i class="bi bi-lightbulb me-1"></i> View AI Insights
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm border-start border-primary border-4 hover-lift">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Vehicles</h6>
                            <h3 class="mb-0">{{ $totalVehicles }}</h3>
                        </div>
                        <div class="text-primary" style="font-size: 2.5rem;">
                            <i class="bi bi-bus-front animated-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm border-start border-success border-4 hover-lift stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Active Vehicles</h6>
                            <h3 class="mb-0 stat-number">{{ $activeVehicles }}</h3>
                        </div>
                        <div class="text-success" style="font-size: 2.5rem;">
                            <i class="bi bi-check-circle animated-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm border-start border-info border-4 hover-lift stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Checklists</h6>
                            <h3 class="mb-0 stat-number">{{ $totalChecklists }}</h3>
                        </div>
                        <div class="text-info" style="font-size: 2.5rem;">
                            <i class="bi bi-clipboard-check animated-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm border-start border-warning border-4 hover-lift stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending Review</h6>
                            <h3 class="mb-0 stat-number {{ $pendingChecklists > 0 ? 'notification-badge' : '' }}">{{ $pendingChecklists }}</h3>
                        </div>
                        <div class="text-warning" style="font-size: 2.5rem;">
                            <i class="bi bi-clock-history animated-icon pulse"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Card -->
    @if($kidsAlerts > 0 || $flaggedChecklists > 0)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill"></i> Alerts & Flags</h5>
                </div>
                <div class="card-body">
                    @if($kidsAlerts > 0)
                        <div class="alert alert-danger mb-2">
                            <strong><i class="bi bi-exclamation-triangle-fill"></i> {{ $kidsAlerts }} Kids Left Alert(s)</strong>
                            <a href="{{ route('admin.checklists.index') }}" class="btn btn-sm btn-outline-danger float-end">View</a>
                        </div>
                    @endif
                    @if($flaggedChecklists > 0)
                        <div class="alert alert-warning mb-0">
                            <strong><i class="bi bi-flag-fill"></i> {{ $flaggedChecklists }} Flagged Checklist(s)</strong>
                            <a href="{{ route('admin.checklists.index') }}" class="btn btn-sm btn-outline-warning float-end">View</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning-charge"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle"></i> Add Vehicle
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-bus-front"></i> Manage Vehicles
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('admin.checklists.index') }}" class="btn btn-outline-info w-100">
                                <i class="bi bi-clipboard-check"></i> View Checklists
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-house"></i> Home Page
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Checklists -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Checklists</h5>
                </div>
                <div class="card-body">
                    @if($recentChecklists->isEmpty())
                        <p class="text-muted text-center py-4">No checklists yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Vehicle</th>
                                        <th>Driver</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentChecklists as $checklist)
                                    <tr>
                                        <td>{{ $checklist->completed_at?->format('d/m/Y H:i') ?? $checklist->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $checklist->vehicle->bus_number ?? 'N/A' }}</td>
                                        <td>{{ $checklist->user->first_name ?? 'N/A' }} {{ $checklist->user->last_name ?? '' }}</td>
                                        <td>
                                            @if($checklist->status === 'Approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($checklist->status === 'Flagged')
                                                <span class="badge bg-danger">Flagged</span>
                                            @elseif($checklist->status === 'Completed')
                                                <span class="badge bg-info">Pending Review</span>
                                            @else
                                                <span class="badge bg-warning">{{ $checklist->status }}</span>
                                            @endif
                                            @if($checklist->kids_left_alert)
                                                <span class="badge bg-danger ms-1">KIDS ALERT</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.checklists.show', $checklist->checklist_uuid) }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

