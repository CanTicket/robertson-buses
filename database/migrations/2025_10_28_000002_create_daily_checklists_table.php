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
        Schema::create('daily_checklists', function (Blueprint $table) {
            $table->id('checklist_id');
            $table->string('checklist_uuid', 100)->unique()->comment('Public-facing identifier');
            $table->unsignedBigInteger('shift_timer_id')->comment('Reference to task_timer (shift)');
            $table->unsignedBigInteger('vehicle_id')->comment('Which bus/vehicle');
            $table->string('user_id', 100)->comment('Driver who completed checklist');
            $table->unsignedBigInteger('company_id')->nullable();
            
            // Checklist status
            $table->enum('status', ['Pending', 'Completed', 'Approved', 'Flagged'])->default('Pending');
            $table->string('reviewed_by', 100)->nullable()->comment('Manager who reviewed');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable()->comment('Manager comments');
            
            // Safety critical flag
            $table->boolean('kids_left_alert')->default(false)->comment('CRITICAL: Kids left on bus');
            $table->boolean('alert_sent')->default(false)->comment('Alert notification sent');
            
            // Timestamps
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            
            // Indexes
            $table->index('shift_timer_id');
            $table->index('vehicle_id');
            $table->index('user_id');
            $table->index('company_id');
            $table->index('status');
            $table->index('kids_left_alert');
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_checklists');
    }
};


