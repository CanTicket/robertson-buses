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
        Schema::create('checklist_photos', function (Blueprint $table) {
            $table->id('photo_id');
            $table->unsignedBigInteger('checklist_id')->comment('Reference to daily_checklist');
            $table->string('photo_path', 500)->comment('Storage path to photo file');
            $table->string('photo_type', 50)->default('exterior')->comment('Type: exterior, interior, damage, etc.');
            $table->string('original_filename', 255)->nullable()->comment('Original filename when uploaded');
            $table->unsignedInteger('file_size')->nullable()->comment('File size in bytes');
            $table->string('mime_type', 100)->nullable()->comment('MIME type (e.g., image/jpeg)');
            $table->text('caption')->nullable()->comment('Optional photo caption/description');
            $table->timestamp('uploaded_at')->useCurrent()->comment('When photo was uploaded');
            
            // Foreign key
            $table->foreign('checklist_id')
                  ->references('checklist_id')
                  ->on('daily_checklists')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('checklist_id');
            $table->index('photo_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_photos');
    }
};
