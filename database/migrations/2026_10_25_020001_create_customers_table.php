<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_type', 20)->default('retail');
            $table->string('full_name', 150);
            $table->string('phone', 30)->index();
            $table->string('whatsapp', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('tax_number', 50)->nullable();
            $table->decimal('credit_limit', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('customers');
    }
};