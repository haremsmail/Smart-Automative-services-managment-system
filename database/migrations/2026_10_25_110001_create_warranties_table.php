<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_line_id')->constrained('invoice_lines')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('mileage_limit')->nullable();
            $table->text('terms')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('warranties');
    }
};