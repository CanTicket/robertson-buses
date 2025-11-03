@extends('managerial.components.app')

@section('page-title', 'Manager Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-0">Manager Dashboard</h2>
            <p class="text-muted">Welcome back, {{ auth()->user()->first_name }}! Here's what needs your attention.</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending Review</h6>
                            <h3 class="mb-0">{{ $pendingChecklists }}</h3>
                        </div>
                        <div class="text-warning" style="font-size: 2.5rem;">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Flagged</h6>
                            <h3 class="mb-0">{{ $flaggedChecklists }}</h3>
                        </div>
                        <div class="text-danger" style="font-size: 2.5rem;">
                            <i class="bi bi-flag-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Kids Alerts</h6>
                            <h3 class="mb-0">{{ $kidsAlerts }}</h3>
                        </div>
                        <div class="text-danger" style="font-size: 2.5rem;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Approved Today</h6>
                            <h3 class="mb-0">{{ $approvedToday }}</h3>
                        </div>
                        <div class="text-success" style="font-size: 2.5rem;">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Critical Alerts -->
    @if($kidsAlerts > 0)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Critical: {{ $kidsAlerts }} Kids Left Alert(s)</h5>
                <p class="mb-2">Immediate action required! Review these checklists now.</p>
                <a href="{{ route('managerial.checklists.review') }}" class="btn btn-danger">
                    <i class="bi bi-arrow-right"></i> Review Now
                </a>
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
                        <div class="col-md-4 mb-2">
                            <a href="{{ route('managerial.checklists.review') }}" class="btn btn-primary w-100">
                                <i class="bi bi-clipboard-check"></i> Review Checklists
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
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
                    <h5 class="mb-0"><i class="bi bi-list-check"></i> Checklists Needing Review</h5>
                </div>
                <div class="card-body">
                    @if($recentChecklists->isEmpty())
                        <p class="text-muted text-center py-4">No checklists pending review. Great job!</p>
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
                                    <tr class="{{ $checklist->kids_left_alert ? 'table-danger' : '' }}">
                                        <td>{{ $checklist->completed_at?->format('d/m/Y H:i') ?? $checklist->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $checklist->vehicle->bus_number ?? 'N/A' }}</td>
                                        <td>{{ $checklist->user->first_name ?? 'N/A' }} {{ $checklist->user->last_name ?? '' }}</td>
                                        <td>
                                            @if($checklist->status === 'Flagged')
                                                <span class="badge bg-danger">Flagged</span>
                                            @else
                                                <span class="badge bg-info">Awaiting Review</span>
                                            @endif
                                            @if($checklist->kids_left_alert)
                                                <span class="badge bg-danger ms-1">
                                                    <i class="bi bi-exclamation-triangle-fill"></i> KIDS ALERT
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('managerial.checklist.show', $checklist->checklist_uuid) }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> Review
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('managerial.checklists.review') }}" class="btn btn-outline-primary">
                                View All Checklists <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

