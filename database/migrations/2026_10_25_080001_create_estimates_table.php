<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();
            $table->string('estimate_number', 30)->unique();
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('inspection_id')->nullable()->constrained('inspections')->nullOnDelete();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->string('status', 20)->default('draft')->index();
            $table->string('public_token', 64)->nullable()->unique();
            $table->string('customer_ip', 45)->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('estimates');
    }
};