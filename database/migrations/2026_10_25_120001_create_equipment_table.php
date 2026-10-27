<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name', 120);
            $table->string('serial_number', 80)->nullable()->unique();
            $table->string('equipment_type', 60);
            $table->date('purchase_date')->nullable();
            $table->date('next_service_due')->nullable();
            $table->string('status', 20)->default('operational');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('equipment');
    }
};