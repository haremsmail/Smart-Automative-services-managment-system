<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('inspection_templates')->nullOnDelete();
            $table->unsignedInteger('odometer_reading')->nullable();
            $table->string('status', 20)->default('in_progress');
            $table->string('public_token', 64)->nullable()->unique();
            $table->text('summary')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('viewed_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('inspections');
    }
};