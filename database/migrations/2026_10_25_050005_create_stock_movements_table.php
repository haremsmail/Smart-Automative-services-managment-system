<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('movement_type', 25)->index(); // purchase, job_card, adjustment, transfer
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('stock_movements');
    }
};