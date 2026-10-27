<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('part_categories')->nullOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('barcode', 60)->nullable()->index();
            $table->string('oem_number', 60)->nullable()->index();
            $table->string('name', 180);
            $table->text('description')->nullable();
            $table->string('unit', 20)->default('piece');
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('min_stock_level', 10, 2)->default(0);
            $table->decimal('reorder_quantity', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('parts');
    }
};