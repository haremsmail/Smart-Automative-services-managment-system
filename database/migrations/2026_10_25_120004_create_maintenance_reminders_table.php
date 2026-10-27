<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('maintenance_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('service_type', 100);
            $table->date('due_date')->nullable();
            $table->unsignedInteger('due_mileage')->nullable();
            $table->string('status', 20)->default('pending'); // pending, sent, completed, cancelled
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('maintenance_reminders');
    }
};