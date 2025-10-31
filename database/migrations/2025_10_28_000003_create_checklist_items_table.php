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
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id('item_id');
            $table->unsignedBigInteger('checklist_id');
            $table->string('check_type', 100)->comment('e.g., tyre_front, tyre_rear, fuel_level, interior_check');
            $table->string('check_label', 200)->comment('Display label like "Front Tyres"');
            $table->text('value')->nullable()->comment('Check result: Good/Fair/Poor, Yes/No, percentage, etc.');
            $table->text('notes')->nullable()->comment('Additional notes for this specific check');
            $table->integer('sort_order')->default(0)->comment('Display order in checklist');
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign key
            $table->foreign('checklist_id')
                  ->references('checklist_id')
                  ->on('daily_checklists')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('checklist_id');
            $table->index('check_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};


