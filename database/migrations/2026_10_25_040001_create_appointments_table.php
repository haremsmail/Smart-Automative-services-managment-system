<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('bay_id')->nullable()->constrained('service_bays')->nullOnDelete();
            $table->dateTime('scheduled_at')->index();
            $table->string('status', 25)->default('scheduled')->index();
            $table->text('requested_services')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('appointments');
    }
};