<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('qc_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('road_test_done')->default(false);
            $table->boolean('passed')->default(false);
            $table->text('findings')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('qc_checks');
    }
};