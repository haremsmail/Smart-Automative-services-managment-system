<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('contact_person', 100)->nullable();
            $table->string('phone', 30)->index();
            $table->string('email', 120)->nullable();
            $table->string('tax_number', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('payment_terms', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('suppliers');
    }
};