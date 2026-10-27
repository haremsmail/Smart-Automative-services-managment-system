<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_number', 30)->unique();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('bay_id')->nullable()->constrained('service_bays')->nullOnDelete();
            $table->foreignId('advisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('mileage_in')->nullable();
            $table->string('status', 25)->default('open')->index();
            $table->text('customer_complaint')->nullable();
            $table->text('diagnosis')->nullable();
            $table->dateTime('promised_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('work_orders');
    }
};