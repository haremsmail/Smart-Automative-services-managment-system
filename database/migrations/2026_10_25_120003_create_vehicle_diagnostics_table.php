<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vehicle_diagnostics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->nullOnDelete();
            $table->string('scanner_device', 100)->nullable();
            $table->string('dtc_code', 20)->index();
            $table->text('description')->nullable();
            $table->string('severity', 20)->nullable();
            $table->string('status', 20)->default('active'); // active, pending, cleared
            $table->dateTime('scanned_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('vehicle_diagnostics');
    }
};