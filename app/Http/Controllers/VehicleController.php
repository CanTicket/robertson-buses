<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\DailyChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    /**
     * Display a listing of vehicles
     */
    public function index()
    {
        $user = Auth::user();
        
        $vehicles = Vehicle::where('company_id', $user->company_id)
            ->orderBy('bus_number', 'asc')
            ->paginate(20);

        return view('admin.pages.vehicles.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new vehicle
     */
    public function create()
    {
        return view('admin.pages.vehicles.create');
    }

    /**
     * Store a newly created vehicle
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bus_number' => 'required|string|max:50|unique:vehicles,bus_number',
            'registration_number' => 'required|string|max:100|unique:vehicles,registration_number',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'capacity' => 'nullable|integer|min:1',
            'status' => 'required|in:Active,Maintenance,Inactive',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        
        $vehicle = Vehicle::create([
            ...$validated,
            'company_id' => $user->company_id,
            'date_added' => now(),
        ]);

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Vehicle added successfully.');
    }

    /**
     * Display the specified vehicle
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $vehicle = Vehicle::where('vehicle_id', $id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        // Get recent checklists for this vehicle
        $recentChecklists = DailyChecklist::where('vehicle_id', $id)
            ->with(['user', 'reviewer'])
            ->orderBy('completed_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.pages.vehicles.show', compact('vehicle', 'recentChecklists'));
    }

    /**
     * Show the form for editing the specified vehicle
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        $vehicle = Vehicle::where('vehicle_id', $id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        return view('admin.pages.vehicles.edit', compact('vehicle'));
    }

    /**
     * Update the specified vehicle
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        $vehicle = Vehicle::where('vehicle_id', $id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $validated = $request->validate([
            'bus_number' => 'required|string|max:50|unique:vehicles,bus_number,' . $id . ',vehicle_id',
            'registration_number' => 'required|string|max:100|unique:vehicles,registration_number,' . $id . ',vehicle_id',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'capacity' => 'nullable|integer|min:1',
            'status' => 'required|in:Active,Maintenance,Inactive',
            'notes' => 'nullable|string',
        ]);

        $vehicle->update([
            ...$validated,
            'date_updated' => now(),
        ]);

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Vehicle updated successfully.');
    }

    /**
     * Remove the specified vehicle
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        $vehicle = Vehicle::where('vehicle_id', $id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        // Check if vehicle has checklists
        $checklistCount = DailyChecklist::where('vehicle_id', $id)->count();
        
        if ($checklistCount > 0) {
            return redirect()
                ->route('admin.vehicles.index')
                ->with('error', 'Cannot delete vehicle with existing checklists. Set to Inactive instead.');
        }

        $vehicle->delete();

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Vehicle deleted successfully.');
    }

    /**
     * Get active vehicles for dropdown (AJAX)
     */
    public function getActiveVehicles()
    {
        $user = Auth::user();
        
        $vehicles = Vehicle::where('company_id', $user->company_id)
            ->where('status', 'Active')
            ->select('vehicle_id', 'bus_number', 'registration_number')
            ->orderBy('bus_number')
            ->get();

        return response()->json($vehicles);
    }
}



