<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\InspectionType;
use App\Enums\InspectionStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicle_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->foreignId('reservation_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('inspected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('inspection_type')->default(InspectionType::GENERAL->value);
            $table->integer('mileage')->nullable();
            $table->string('fuel_level')->nullable();
            $table->string('overall_condition')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('inspection_date')->nullable();
            $table->string('status')->default(InspectionStatus::DRAFT->value);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['car_id', 'inspection_type']);
            $table->index(['reservation_id', 'inspection_type']);
            $table->index(['status', 'inspection_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_inspections');
    }
};
