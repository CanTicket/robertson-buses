<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ChecklistController;

/*
|--------------------------------------------------------------------------
| Web Routes - Buses Application
|--------------------------------------------------------------------------
|
| This file contains routes specific to the Buses application.
| CanTicket routes are inherited from the base system.
|
*/

// Admin Routes - Vehicle Management
Route::middleware(['auth', 'role:Administrator'])->prefix('admin')->name('admin.')->group(function () {
    
    // Vehicles
    Route::resource('vehicles', VehicleController::class);
    Route::get('vehicles/api/active', [VehicleController::class, 'getActiveVehicles'])->name('vehicles.api.active');
    
    // Checklists (Admin view)
    Route::get('checklists', [ChecklistController::class, 'reviewIndex'])->name('checklists.index');
    Route::get('checklists/{uuid}', [ChecklistController::class, 'show'])->name('checklists.show');
    Route::post('checklists/{uuid}/approve', [ChecklistController::class, 'approve'])->name('checklists.approve');
    Route::post('checklists/{uuid}/flag', [ChecklistController::class, 'flag'])->name('checklists.flag');
});

// Manager Routes - Checklist Review
Route::middleware(['auth', 'role:Managerial'])->prefix('managerial')->name('managerial.')->group(function () {
    
    // Checklist Review
    Route::get('checklists/review', [ChecklistController::class, 'reviewIndex'])->name('checklists.review');
    Route::get('checklists/{uuid}', [ChecklistController::class, 'show'])->name('checklist.show');
    Route::post('checklists/{uuid}/approve', [ChecklistController::class, 'approve'])->name('checklist.approve');
    Route::post('checklists/{uuid}/flag', [ChecklistController::class, 'flag'])->name('checklist.flag');
});

// Staff/Driver Routes - Checklist Completion
Route::middleware(['auth', 'role:Regular'])->prefix('staff')->name('regular.')->group(function () {
    
    // End-of-Day Checklist
    Route::get('checklist/create', [ChecklistController::class, 'create'])->name('checklist.create');
    Route::post('checklist/store', [ChecklistController::class, 'store'])->name('checklist.store');
    Route::get('checklist/{uuid}', [ChecklistController::class, 'show'])->name('checklist.show');
    
    // Check if can clock out (AJAX)
    Route::get('checklist/can-clock-out', [ChecklistController::class, 'canClockOut'])->name('checklist.can-clock-out');
});

// Contractor Routes (if needed)
Route::middleware(['auth', 'role:Contractor'])->prefix('contractor')->name('contractor.')->group(function () {
    
    // Checklist view only
    Route::get('checklist/{uuid}', [ChecklistController::class, 'show'])->name('checklist.show');
});



