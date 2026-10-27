<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_id')->constrained('warranties')->cascadeOnDelete();
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->nullOnDelete();
            $table->date('claim_date');
            $table->text('defect_description');
            $table->decimal('reimbursement_amount', 14, 2)->default(0);
            $table->string('status', 20)->default('pending')->index(); // pending, approved, rejected
            $table->dateTime('resolved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('warranty_claims');
    }
};