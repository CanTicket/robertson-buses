@extends('contractor.components.app')

@section('page-title', 'My Checklists')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-0">My Checklists</h2>
            <p class="text-muted">View checklists assigned to you</p>
        </div>
    </div>

    @if($checklists->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-clipboard-check" style="font-size: 3rem; color: #cbd5e0;"></i>
                <h4 class="mt-3 mb-2">No Checklists Available</h4>
                <p class="text-muted">You don't have any assigned checklists yet. Please contact your administrator for access.</p>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Checklist History</h5>
            </div>
            <div class="card-body">
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
                            @foreach($checklists as $checklist)
                            <tr>
                                <td>
                                    <strong>{{ $checklist->completed_at?->format('d/m/Y') ?? $checklist->created_at->format('d/m/Y') }}</strong><br>
                                    <small class="text-muted">{{ $checklist->completed_at?->format('H:i') ?? $checklist->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    @if($checklist->vehicle)
                                        <strong>{{ $checklist->vehicle->bus_number }}</strong><br>
                                        <small class="text-muted">{{ $checklist->vehicle->registration_number }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($checklist->user)
                                        {{ $checklist->user->first_name }} {{ $checklist->user->last_name }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
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
                                    <a href="{{ route('contractor.checklist.show', $checklist->checklist_uuid) }}" 
                                       class="btn btn-sm btn-primary" title="View Details">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($checklists->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $checklists->links() }}
                </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

