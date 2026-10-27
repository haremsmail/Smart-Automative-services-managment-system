<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('vin', 17)->nullable()->unique();
            $table->string('plate_number', 30)->index();
            $table->string('make', 60);
            $table->string('model', 60);
            $table->smallInteger('year')->nullable();
            $table->string('color', 40)->nullable();
            $table->string('engine_type', 50)->nullable();
            $table->string('transmission', 30)->nullable();
            $table->unsignedInteger('current_mileage')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('vehicles');
    }
};