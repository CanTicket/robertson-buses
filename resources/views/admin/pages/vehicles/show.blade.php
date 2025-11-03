@extends('admin.components.app')

@section('page-title', 'Vehicle Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Back to Vehicles
            </a>
            <h2 class="mb-0">Vehicle Details</h2>
            <p class="text-muted">{{ $vehicle->display_name }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Vehicle Information -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-bus-front"></i> Vehicle Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Bus Number</h6>
                        <h4 class="mb-0">{{ $vehicle->bus_number }}</h4>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Registration Number</h6>
                        <p class="mb-0"><strong>{{ $vehicle->registration_number }}</strong></p>
                    </div>
                    
                    @if($vehicle->make || $vehicle->model)
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Make & Model</h6>
                        <p class="mb-0">{{ $vehicle->make }} {{ $vehicle->model }}</p>
                    </div>
                    @endif
                    
                    @if($vehicle->year)
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Year</h6>
                        <p class="mb-0">{{ $vehicle->year }}</p>
                    </div>
                    @endif
                    
                    @if($vehicle->capacity)
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Capacity</h6>
                        <p class="mb-0">{{ $vehicle->capacity }} passengers</p>
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Status</h6>
                        <p class="mb-0">
                            @if($vehicle->status === 'Active')
                                <span class="badge bg-success fs-6">Active</span>
                            @elseif($vehicle->status === 'Maintenance')
                                <span class="badge bg-warning fs-6">Maintenance</span>
                            @else
                                <span class="badge bg-secondary fs-6">Inactive</span>
                            @endif
                        </p>
                    </div>
                    
                    @if($vehicle->date_added)
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Date Added</h6>
                        <p class="mb-0">{{ $vehicle->date_added->format('d/m/Y') }}</p>
                    </div>
                    @endif
                    
                    @if($vehicle->notes)
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Notes</h6>
                        <p class="mb-0 text-muted">{{ $vehicle->notes }}</p>
                    </div>
                    @endif
                    
                    <hr>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.vehicles.edit', $vehicle->vehicle_id) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit Vehicle
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics & Checklists -->
        <div class="col-lg-8">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm border-start border-info border-4">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Total Checklists</h6>
                            <h3 class="mb-0">{{ $totalChecklists }}</h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm border-start border-success border-4">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Approved</h6>
                            <h3 class="mb-0">{{ $approvedChecklists }}</h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm border-start border-danger border-4">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Flagged</h6>
                            <h3 class="mb-0">{{ $flaggedChecklists }}</h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm border-start border-danger border-4">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Kids Alerts</h6>
                            <h3 class="mb-0">{{ $kidsAlerts }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checklists History -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Checklist History</h5>
                </div>
                <div class="card-body">
                    @if($recentChecklists->isEmpty())
                        <p class="text-muted text-center py-4">No checklists found for this vehicle yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Driver</th>
                                        <th>Status</th>
                                        <th>Reviewer</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentChecklists as $checklist)
                                    <tr class="{{ $checklist->kids_left_alert ? 'table-danger' : '' }}">
                                        <td>
                                            <strong>{{ $checklist->completed_at?->format('d/m/Y') ?? $checklist->created_at->format('d/m/Y') }}</strong><br>
                                            <small class="text-muted">{{ $checklist->completed_at?->format('H:i') ?? $checklist->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            {{ $checklist->user->first_name ?? 'N/A' }} {{ $checklist->user->last_name ?? '' }}
                                        </td>
                                        <td>
                                            @if($checklist->status === 'Pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($checklist->status === 'Completed')
                                                <span class="badge bg-info">Awaiting Review</span>
                                            @elseif($checklist->status === 'Approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($checklist->status === 'Flagged')
                                                <span class="badge bg-danger">Flagged</span>
                                            @endif
                                            
                                            @if($checklist->kids_left_alert)
                                                <span class="badge bg-danger ms-1">
                                                    <i class="bi bi-exclamation-triangle-fill"></i> KIDS ALERT
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($checklist->reviewer)
                                                {{ $checklist->reviewer->first_name }} {{ $checklist->reviewer->last_name }}
                                                @if($checklist->reviewed_at)
                                                    <br><small class="text-muted">{{ $checklist->reviewed_at->format('d/m/Y') }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">Not reviewed</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.checklists.show', $checklist->checklist_uuid) }}" 
                                               class="btn btn-sm btn-primary" title="View Details">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($recentChecklists->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $recentChecklists->links() }}
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

