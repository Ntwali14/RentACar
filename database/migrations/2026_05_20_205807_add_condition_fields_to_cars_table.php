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
        Schema::table('cars', function (Blueprint $table) {
            $table->string('vin')->nullable()->after('license_plate')->comment('Vehicle Identification Number');
            $table->string('current_condition_status')->nullable()->after('status')->comment('Overall condition status of the vehicle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn(['vin', 'current_condition_status']);
        });
    }
};
