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
        Schema::create('inspection_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('vehicle_inspections')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->string('caption')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['inspection_id', 'file_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_evidence');
    }
};
