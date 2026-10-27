<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique();
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('pos_session_id')->nullable()->constrained('pos_sessions')->nullOnDelete();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->string('status', 20)->default('unpaid')->index(); // unpaid, partial, paid, void
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('invoices');
    }
};