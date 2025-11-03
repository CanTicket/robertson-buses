@extends('regular.components.app')

@section('page-title', 'Driver Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-0">Driver Dashboard</h2>
            <p class="text-muted">Welcome back, {{ auth()->user()->first_name }}! Here's your overview.</p>
        </div>
    </div>

    <!-- Active Shift Card -->
    @if($activeTimer)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-1"><i class="bi bi-clock"></i> Active Shift</h5>
                            <p class="mb-0 text-muted">
                                Started: <strong>{{ $activeTimer->time_started->format('d/m/Y H:i') }}</strong>
                                @if($activeTimer->time_finished)
                                    | Ended: <strong>{{ $activeTimer->time_finished->format('d/m/Y H:i') }}</strong>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            @if($hasCompletedChecklist)
                                <span class="badge bg-success fs-6 mb-2">
                                    <i class="bi bi-check-circle"></i> Checklist Completed
                                </span>
                                <br>
                                <small class="text-muted">You can clock out</small>
                            @else
                                <a href="{{ route('regular.checklist.create') }}" class="btn btn-primary">
                                    <i class="bi bi-clipboard-check"></i> Complete Checklist
                                </a>
                                <br>
                                <small class="text-danger">Required before clock out</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card shadow-sm border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Checklists</h6>
                            <h3 class="mb-0">{{ $totalChecklists }}</h3>
                        </div>
                        <div class="text-info" style="font-size: 2.5rem;">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Approved</h6>
                            <h3 class="mb-0">{{ $approvedChecklists }}</h3>
                        </div>
                        <div class="text-success" style="font-size: 2.5rem;">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-3">
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
    </div>

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
                            <a href="{{ route('regular.checklist.create') }}" class="btn btn-primary w-100">
                                <i class="bi bi-clipboard-check"></i> Complete Checklist
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

    <!-- My Recent Checklists -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> My Recent Checklists</h5>
                </div>
                <div class="card-body">
                    @if($myChecklists->isEmpty())
                        <p class="text-muted text-center py-4">No checklists yet. Complete your first checklist to get started!</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Vehicle</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($myChecklists as $checklist)
                                    <tr>
                                        <td>{{ $checklist->completed_at?->format('d/m/Y H:i') ?? $checklist->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $checklist->vehicle->bus_number ?? 'N/A' }}</td>
                                        <td>
                                            @if($checklist->status === 'Approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($checklist->status === 'Flagged')
                                                <span class="badge bg-danger">Flagged</span>
                                            @elseif($checklist->status === 'Completed')
                                                <span class="badge bg-info">Awaiting Review</span>
                                            @else
                                                <span class="badge bg-warning">{{ $checklist->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('regular.checklist.show', $checklist->checklist_uuid) }}" class="btn btn-sm btn-primary">
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

