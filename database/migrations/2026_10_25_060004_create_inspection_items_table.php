<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('inspections')->cascadeOnDelete();
            $table->foreignId('template_item_id')->nullable()->constrained('inspection_template_items')->nullOnDelete();
            $table->string('name', 120);
            $table->string('condition', 15)->default('good'); // good, fair, poor, critical
            $table->string('measured_value', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('inspection_items');
    }
};