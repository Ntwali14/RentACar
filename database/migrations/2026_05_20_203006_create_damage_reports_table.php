<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\DamageReportStatus;
use App\Enums\CustomerLiabilityStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('damage_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->foreignId('pickup_inspection_id')->nullable()->constrained('vehicle_inspections')->onDelete('set null');
            $table->foreignId('return_inspection_id')->nullable()->constrained('vehicle_inspections')->onDelete('set null');
            $table->foreignId('reported_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('damage_description');
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->string('customer_liability_status')->default(CustomerLiabilityStatus::PENDING->value);
            $table->text('admin_decision')->nullable();
            $table->string('status')->default(DamageReportStatus::OPEN->value);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['car_id', 'status']);
            $table->index(['reservation_id', 'status']);
            $table->index(['customer_liability_status', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damage_reports');
    }
};
