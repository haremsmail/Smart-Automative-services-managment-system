<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('estimate_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estimate_id')->constrained('estimates')->cascadeOnDelete();
            $table->string('line_type', 10); // 'service' or 'part'
            $table->foreignId('service_id')->nullable()->constrained('service_catalog')->nullOnDelete();
            $table->foreignId('part_id')->nullable()->constrained('parts')->nullOnDelete();
            $table->string('description', 250);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->string('approval_status', 15)->default('pending'); // pending, approved, declined
            $table->dateTime('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('estimate_lines');
    }
};