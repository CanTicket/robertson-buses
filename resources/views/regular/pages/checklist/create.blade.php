@extends('regular.components.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-0">End-of-Day Safety Checklist</h2>
            <p class="text-muted">Complete this checklist before clocking out</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Safety Inspection</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('regular.checklist.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="shift_timer_id" value="{{ $activeTimer->id }}">

                        <!-- Vehicle Selection -->
                        <div class="mb-4">
                            <label for="vehicle_id" class="form-label fw-bold">
                                Select Vehicle <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('vehicle_id') is-invalid @enderror" 
                                    id="vehicle_id" 
                                    name="vehicle_id" 
                                    required>
                                <option value="">-- Choose Bus --</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->vehicle_id }}" 
                                            {{ (old('vehicle_id') == $vehicle->vehicle_id || $checklist?->vehicle_id == $vehicle->vehicle_id) ? 'selected' : '' }}>
                                        {{ $vehicle->display_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('vehicle_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <!-- Tyre Checks -->
                        <h5 class="mb-3">Tyre Condition</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tyre_front" class="form-label">
                                    Front Tyres <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('tyre_front') is-invalid @enderror" 
                                        id="tyre_front" 
                                        name="tyre_front" 
                                        required>
                                    <option value="">-- Select --</option>
                                    <option value="Good" {{ old('tyre_front') === 'Good' ? 'selected' : '' }}>✅ Good</option>
                                    <option value="Fair" {{ old('tyre_front') === 'Fair' ? 'selected' : '' }}>⚠️ Fair</option>
                                    <option value="Poor" {{ old('tyre_front') === 'Poor' ? 'selected' : '' }}>❌ Poor</option>
                                </select>
                                @error('tyre_front')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="tyre_rear" class="form-label">
                                    Rear Tyres <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('tyre_rear') is-invalid @enderror" 
                                        id="tyre_rear" 
                                        name="tyre_rear" 
                                        required>
                                    <option value="">-- Select --</option>
                                    <option value="Good" {{ old('tyre_rear') === 'Good' ? 'selected' : '' }}>✅ Good</option>
                                    <option value="Fair" {{ old('tyre_rear') === 'Fair' ? 'selected' : '' }}>⚠️ Fair</option>
                                    <option value="Poor" {{ old('tyre_rear') === 'Poor' ? 'selected' : '' }}>❌ Poor</option>
                                </select>
                                @error('tyre_rear')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Fuel Level -->
                        <div class="mb-4">
                            <label for="fuel_level" class="form-label">
                                Fuel Level (%) <span class="text-danger">*</span>
                            </label>
                            <input type="range" 
                                   class="form-range" 
                                   id="fuel_level" 
                                   name="fuel_level" 
                                   min="0" 
                                   max="100" 
                                   value="{{ old('fuel_level', 50) }}" 
                                   oninput="document.getElementById('fuel_display').textContent = this.value + '%'">
                            <div class="text-center">
                                <span class="badge bg-primary fs-5" id="fuel_display">{{ old('fuel_level', 50) }}%</span>
                            </div>
                            @error('fuel_level')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <!-- CRITICAL: Kids Check -->
                        <div class="mb-4 p-4 border border-danger rounded" style="background-color: #fff3cd;">
                            <h5 class="text-danger mb-3">
                                <i class="bi bi-exclamation-triangle-fill"></i> 
                                CRITICAL SAFETY CHECK
                            </h5>
                            <label for="kids_left" class="form-label fw-bold">
                                Are there any kids left on the bus? <span class="text-danger">*</span>
                            </label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" 
                                       type="radio" 
                                       name="kids_left" 
                                       id="kids_no" 
                                       value="No" 
                                       {{ old('kids_left') === 'No' ? 'checked' : '' }} 
                                       required>
                                <label class="form-check-label" for="kids_no">
                                    <strong>No</strong> - All children have been accounted for
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="radio" 
                                       name="kids_left" 
                                       id="kids_yes" 
                                       value="Yes" 
                                       {{ old('kids_left') === 'Yes' ? 'checked' : '' }}>
                                <label class="form-check-label text-danger" for="kids_yes">
                                    <strong>Yes</strong> - Kids are still on the bus (ALERT WILL BE SENT)
                                </label>
                            </div>
                            @error('kids_left')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Photo Upload -->
                        <div class="mb-4">
                            <label for="photos" class="form-label">
                                Upload Photos (Optional)
                            </label>
                            <input type="file" 
                                   class="form-control @error('photos.*') is-invalid @enderror" 
                                   id="photos" 
                                   name="photos[]" 
                                   multiple 
                                   accept="image/jpeg,image/jpg,image/png">
                            <small class="text-muted">Maximum 5MB per image. JPG, JPEG, PNG accepted.</small>
                            @error('photos.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Additional Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Any issues, damage, or observations...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle-fill"></i> Complete Checklist
                            </button>
                            <small class="text-muted text-center">
                                Once completed, you will be able to clock out for the day.
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="card-title">Current Shift</h6>
                    <p class="mb-1"><strong>Clock In:</strong> {{ $activeTimer->time_started }}</p>
                    <p class="mb-0"><strong>Duration:</strong> {{ \Carbon\Carbon::parse($activeTimer->time_started)->diffForHumans(null, true) }}</p>
                </div>
            </div>

            <div class="card shadow-sm border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Checklist Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Inspect all tyres for wear and damage</li>
                        <li>Record accurate fuel level</li>
                        <li><strong>ALWAYS</strong> check for children before leaving</li>
                        <li>Upload photos of any damage or issues</li>
                        <li>Report any safety concerns immediately</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



