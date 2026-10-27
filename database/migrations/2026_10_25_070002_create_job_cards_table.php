<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('job_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('service_catalog')->nullOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 150);
            $table->text('instructions')->nullable();
            $table->decimal('flat_rate_hours', 5, 2)->default(0);
            $table->string('status', 20)->default('pending')->index();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('job_cards');
    }
};