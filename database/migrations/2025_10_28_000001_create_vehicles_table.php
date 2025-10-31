<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id('vehicle_id');
            $table->string('bus_number', 50)->unique()->comment('Display number like "Bus 101"');
            $table->string('registration_number', 100)->unique()->comment('License plate / rego number');
            $table->string('make', 100)->nullable()->comment('Vehicle manufacturer');
            $table->string('model', 100)->nullable()->comment('Vehicle model');
            $table->year('year')->nullable()->comment('Manufacturing year');
            $table->integer('capacity')->nullable()->comment('Passenger capacity');
            $table->enum('status', ['Active', 'Maintenance', 'Inactive'])->default('Active');
            $table->unsignedBigInteger('company_id')->nullable()->comment('Multi-tenant support');
            $table->text('notes')->nullable();
            $table->timestamp('date_added')->useCurrent();
            $table->timestamp('date_updated')->nullable()->useCurrentOnUpdate();
            
            $table->index('company_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};


