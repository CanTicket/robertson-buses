@extends('admin.components.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">Fleet Management</h2>
            <p class="text-muted">Manage your bus fleet</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Vehicle
            </a>
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

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Bus Number</th>
                            <th>Registration</th>
                            <th>Make & Model</th>
                            <th>Year</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicles as $vehicle)
                        <tr>
                            <td><strong>{{ $vehicle->bus_number }}</strong></td>
                            <td>{{ $vehicle->registration_number }}</td>
                            <td>{{ $vehicle->make }} {{ $vehicle->model }}</td>
                            <td>{{ $vehicle->year ?? '-' }}</td>
                            <td>{{ $vehicle->capacity ?? '-' }}</td>
                            <td>
                                @if($vehicle->status === 'Active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($vehicle->status === 'Maintenance')
                                    <span class="badge bg-warning">Maintenance</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.vehicles.show', $vehicle->vehicle_id) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.vehicles.edit', $vehicle->vehicle_id) }}" 
                                   class="btn btn-sm btn-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.vehicles.destroy', $vehicle->vehicle_id) }}" 
                                      method="POST" class="d-inline" 
                                      onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted mb-3">No vehicles found</p>
                                <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Add Your First Vehicle
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($vehicles->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $vehicles->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection



