@extends('managerial.components.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('managerial.checklists.review') }}" class="btn btn-outline-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Back to Review List
            </a>
            <h2 class="mb-0">Review Safety Checklist</h2>
            <p class="text-muted">Checklist #{{ substr($checklist->checklist_uuid, 0, 8) }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($checklist->kids_left_alert)
    <div class="alert alert-danger" role="alert">
        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> CRITICAL SAFETY ALERT</h5>
        <p class="mb-0">Driver reported that kids may have been left on the bus. <strong>Immediate action required!</strong></p>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Status Card -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Current Status</h6>
                            @if($checklist->status === 'Completed')
                                <span class="badge bg-info fs-6">Awaiting Review</span>
                            @elseif($checklist->status === 'Approved')
                                <span class="badge bg-success fs-6">Approved</span>
                            @elseif($checklist->status === 'Flagged')
                                <span class="badge bg-danger fs-6">Flagged</span>
                            @endif
                        </div>
                        <div class="col-md-6 text-end">
                            <h6 class="text-muted">Completed</h6>
                            <p class="mb-0">{{ $checklist->completed_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checklist Results -->
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Inspection Results</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Check Item</th>
                                <th>Result</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($checklist->items as $item)
                            <tr>
                                <td><strong>{{ $item->check_label }}</strong></td>
                                <td>
                                    {{ $item->formatted_value }}
                                    @if($item->notes)
                                        <br><small class="text-muted">{{ $item->notes }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item->check_type === 'kids_check' && $item->value === 'Yes')
                                        <span class="badge bg-danger">ALERT</span>
                                    @elseif($item->hasProblem())
                                        <span class="badge bg-warning">Issue</span>
                                    @else
                                        <span class="badge bg-success">OK</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Photos -->
            @if($checklist->photos->count() > 0)
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-images"></i> Attached Photos</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($checklist->photos as $photo)
                        <div class="col-md-4">
                            <a href="{{ $photo->url }}" target="_blank" data-lightbox="checklist">
                                <img src="{{ $photo->url }}" 
                                     class="img-thumbnail" 
                                     alt="Checklist Photo" 
                                     style="width: 100%; height: 200px; object-fit: cover;">
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Review Actions -->
            @if($checklist->status === 'Completed' && !$checklist->reviewed_by)
            <div class="card shadow-sm mb-3 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-check"></i> Manager Review</h5>
                </div>
                <div class="card-body">
                    <form id="approveForm" action="{{ route('managerial.checklist.approve', $checklist->checklist_uuid) }}" method="POST" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <label for="review_notes_approve" class="form-label">Review Notes (Optional)</label>
                            <textarea class="form-control" 
                                      id="review_notes_approve" 
                                      name="review_notes" 
                                      rows="2" 
                                      placeholder="Add any comments..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Approve Checklist
                        </button>
                    </form>

                    <hr>

                    <form id="flagForm" action="{{ route('managerial.checklist.flag', $checklist->checklist_uuid) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="review_notes_flag" class="form-label">Reason for Flagging <span class="text-danger">*</span></label>
                            <textarea class="form-control" 
                                      id="review_notes_flag" 
                                      name="review_notes" 
                                      rows="3" 
                                      placeholder="Describe issues that need follow-up..." 
                                      required></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-flag"></i> Flag for Follow-Up
                        </button>
                    </form>
                </div>
            </div>
            @elseif($checklist->reviewed_by)
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle"></i> Already Reviewed</h6>
                <p class="mb-1">Reviewed by: <strong>{{ $checklist->reviewer->first_name }} {{ $checklist->reviewer->last_name }}</strong></p>
                <p class="mb-0">Date: {{ $checklist->reviewed_at->format('d/m/Y H:i') }}</p>
                @if($checklist->review_notes)
                    <hr>
                    <p class="mb-0"><strong>Notes:</strong> {{ $checklist->review_notes }}</p>
                @endif
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Vehicle -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-bus-front"></i> Vehicle</h6>
                </div>
                <div class="card-body">
                    <h5>{{ $checklist->vehicle->bus_number }}</h5>
                    <p class="mb-1"><strong>Rego:</strong> {{ $checklist->vehicle->registration_number }}</p>
                    @if($checklist->vehicle->make)
                        <p class="mb-1">{{ $checklist->vehicle->make }} {{ $checklist->vehicle->model }}</p>
                    @endif
                    <span class="badge bg-{{ $checklist->vehicle->status === 'Active' ? 'success' : 'secondary' }}">
                        {{ $checklist->vehicle->status }}
                    </span>
                </div>
            </div>

            <!-- Driver -->
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-person"></i> Driver Details</h6>
                </div>
                <div class="card-body">
                    <h6>{{ $checklist->user->first_name }} {{ $checklist->user->last_name }}</h6>
                    <p class="text-muted small mb-0">{{ $checklist->user->email_address }}</p>
                </div>
            </div>

            <!-- Shift Info -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-clock"></i> Shift Information</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Start:</strong> {{ $checklist->shiftTimer->time_started }}</p>
                    <p class="mb-0"><strong>End:</strong> {{ $checklist->shiftTimer->time_finished ?? 'In Progress' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



