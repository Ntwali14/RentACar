<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ConditionStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicle_condition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('vehicle_inspections')->onDelete('cascade');
            $table->string('area_name');
            $table->string('condition_status')->default(ConditionStatus::GOOD->value);
            $table->string('severity')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['inspection_id', 'area_name']);
            $table->index('condition_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_condition_items');
    }
};
