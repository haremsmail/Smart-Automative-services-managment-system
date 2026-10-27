<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('service_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50)->default('General');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('standard_hours', 5, 2)->default(1.00);
            $table->decimal('base_price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('service_catalog');
    }
};