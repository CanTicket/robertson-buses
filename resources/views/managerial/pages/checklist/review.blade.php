@extends('managerial.components.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">Checklist Review</h2>
            <p class="text-muted">Review and approve safety checklists</p>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="filter" id="filter_all" autocomplete="off" checked>
                <label class="btn btn-outline-primary" for="filter_all">All</label>

                <input type="radio" class="btn-check" name="filter" id="filter_completed" autocomplete="off">
                <label class="btn btn-outline-info" for="filter_completed">Needs Review</label>

                <input type="radio" class="btn-check" name="filter" id="filter_flagged" autocomplete="off">
                <label class="btn-outline-danger" for="filter_flagged">Flagged</label>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        @forelse($checklists as $checklist)
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card shadow-sm h-100 {{ $checklist->kids_left_alert ? 'border-danger' : '' }}">
                @if($checklist->kids_left_alert)
                <div class="card-header bg-danger text-white">
                    <strong><i class="bi bi-exclamation-triangle-fill"></i> CRITICAL ALERT: Kids Left</strong>
                </div>
                @endif
                
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title mb-0">
                            {{ $checklist->vehicle->bus_number }}
                        </h6>
                        @if($checklist->status === 'Completed')
                            <span class="badge bg-info">Needs Review</span>
                        @elseif($checklist->status === 'Approved')
                            <span class="badge bg-success">Approved</span>
                        @elseif($checklist->status === 'Flagged')
                            <span class="badge bg-danger">Flagged</span>
                        @endif
                    </div>

                    <p class="text-muted small mb-2">
                        <i class="bi bi-person"></i> {{ $checklist->user->first_name }} {{ $checklist->user->last_name }}
                    </p>

                    <p class="text-muted small mb-3">
                        <i class="bi bi-clock"></i> {{ $checklist->completed_at->format('d/m/Y H:i') }}
                    </p>

                    <!-- Quick Summary -->
                    <div class="mb-3">
                        @foreach($checklist->items as $item)
                            @if($item->check_type !== 'notes')
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small>{{ $item->check_label }}:</small>
                                <small>
                                    @if($item->hasProblem() || ($item->check_type === 'kids_check' && $item->value === 'Yes'))
                                        <span class="badge bg-danger">{{ $item->value }}</span>
                                    @else
                                        <span class="badge bg-success">{{ $item->value }}</span>
                                    @endif
                                </small>
                            </div>
                            @endif
                        @endforeach
                    </div>

                    @if($checklist->photos->count() > 0)
                        <p class="small text-muted mb-3">
                            <i class="bi bi-images"></i> {{ $checklist->photos->count() }} photo(s) attached
                        </p>
                    @endif
                </div>

                <div class="card-footer bg-light">
                    @if($checklist->status === 'Completed')
                        <div class="d-grid gap-2">
                            <a href="{{ route('managerial.checklist.show', $checklist->checklist_uuid) }}" 
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> Review Checklist
                            </a>
                        </div>
                    @elseif($checklist->reviewed_by)
                        <small class="text-muted">
                            Reviewed by {{ $checklist->reviewer->first_name }} {{ $checklist->reviewer->last_name }}
                            <br>{{ $checklist->reviewed_at->format('d/m/Y H:i') }}
                        </small>
                        <a href="{{ route('managerial.checklist.show', $checklist->checklist_uuid) }}" 
                           class="btn btn-sm btn-outline-primary mt-2 w-100">
                            <i class="bi bi-eye"></i> View Details
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center" role="alert">
                <i class="bi bi-inbox"></i>
                <p class="mb-0">No checklists to review</p>
            </div>
        </div>
        @endforelse
    </div>

    @if($checklists->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $checklists->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
// Simple client-side filter (optional - can be enhanced with AJAX)
document.querySelectorAll('[name="filter"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Implement filter logic or reload with query params
        console.log('Filter changed:', this.id);
    });
});
</script>
@endpush
@endsection



