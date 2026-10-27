<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('part_id')->constrained('parts');
            $table->decimal('ordered_quantity', 10, 2);
            $table->decimal('received_quantity', 10, 2)->default(0);
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('purchase_order_lines');
    }
};