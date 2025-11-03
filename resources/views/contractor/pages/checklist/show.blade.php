@extends('contractor.components.app')

@section('page-title', 'Checklist Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('contractor.dashboard') }}" class="btn btn-outline-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Back to My Checklists
            </a>
            <h2 class="mb-0">Safety Checklist Details</h2>
            <p class="text-muted">Checklist #{{ substr($checklist->checklist_uuid, 0, 8) }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Status Card -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Status</h6>
                            @if($checklist->status === 'Pending')
                                <span class="badge bg-warning fs-6">Pending</span>
                            @elseif($checklist->status === 'Completed')
                                <span class="badge bg-info fs-6">Awaiting Review</span>
                            @elseif($checklist->status === 'Approved')
                                <span class="badge bg-success fs-6">Approved</span>
                            @elseif($checklist->status === 'Flagged')
                                <span class="badge bg-danger fs-6">Flagged</span>
                            @endif

                            @if($checklist->kids_left_alert)
                                <span class="badge bg-danger fs-6 ms-2">
                                    <i class="bi bi-exclamation-triangle-fill"></i> KIDS ALERT
                                </span>
                            @endif
                        </div>
                        <div class="col-md-6 text-end">
                            <h6 class="text-muted">Completed</h6>
                            <p class="mb-0">{{ $checklist->completed_at?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checklist Details -->
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Checklist Results</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Check Item</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($checklist->items as $item)
                            <tr>
                                <td><strong>{{ $item->check_label }}</strong></td>
                                <td>
                                    @if($item->check_type === 'kids_check' && $item->value === 'Yes')
                                        <span class="badge bg-danger">{{ $item->value }}</span>
                                    @else
                                        <span class="badge bg-info">{{ $item->value ?? 'N/A' }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">No checklist items found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Photos -->
            @if($checklist->photos && $checklist->photos->count() > 0)
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-images"></i> Photos ({{ $checklist->photos->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($checklist->photos as $photo)
                        <div class="col-md-4">
                            @php
                                $photoUrl = $photo->photo_path ? Storage::url($photo->photo_path) : '#';
                            @endphp
                            <a href="{{ $photoUrl }}" target="_blank">
                                <img src="{{ $photoUrl }}" 
                                     class="img-thumbnail" 
                                     alt="Checklist Photo" 
                                     style="width: 100%; height: 200px; object-fit: cover;"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Crect fill=\'%23ddd\' width=\'200\' height=\'200\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3ENo Image%3C/text%3E%3C/svg%3E';">
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Manager Review -->
            @if($checklist->reviewed_by && $checklist->reviewer)
            <div class="card shadow-sm mb-3 border-{{ $checklist->status === 'Approved' ? 'success' : 'danger' }}">
                <div class="card-header bg-{{ $checklist->status === 'Approved' ? 'success' : 'danger' }} text-white">
                    <h5 class="mb-0"><i class="bi bi-person-check"></i> Manager Review</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Reviewed by:</strong> {{ $checklist->reviewer->first_name }} {{ $checklist->reviewer->last_name }}</p>
                    <p class="mb-1"><strong>Date:</strong> {{ $checklist->reviewed_at?->format('d/m/Y H:i') ?? '-' }}</p>
                    @if($checklist->review_notes)
                        <hr>
                        <p class="mb-0"><strong>Notes:</strong></p>
                        <p class="text-muted">{{ $checklist->review_notes }}</p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Vehicle Info -->
            @if($checklist->vehicle)
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-bus-front"></i> Vehicle</h6>
                </div>
                <div class="card-body">
                    <h5>{{ $checklist->vehicle->bus_number ?? 'N/A' }}</h5>
                    <p class="mb-1"><strong>Rego:</strong> {{ $checklist->vehicle->registration_number ?? 'N/A' }}</p>
                    @if($checklist->vehicle->make)
                        <p class="mb-0"><strong>Make:</strong> {{ $checklist->vehicle->make }} {{ $checklist->vehicle->model ?? '' }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Driver Info -->
            @if($checklist->user)
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-person"></i> Driver</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        <strong>{{ $checklist->user->first_name }} {{ $checklist->user->last_name }}</strong>
                    </p>
                    <small class="text-muted">{{ $checklist->user->email }}</small>
                </div>
            </div>
            @endif

            <!-- Shift Info -->
            @if($checklist->shiftTimer)
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-clock"></i> Shift Details</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Start:</strong> {{ $checklist->shiftTimer->time_started?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>End:</strong> {{ $checklist->shiftTimer->time_finished?->format('d/m/Y H:i') ?? 'In Progress' }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

