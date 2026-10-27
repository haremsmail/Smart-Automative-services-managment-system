<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customer_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('channel', 20)->default('sms'); // sms, whatsapp, email, system
            $table->string('title', 150);
            $table->text('body');
            $table->string('status', 20)->default('queued'); // queued, sent, failed
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('customer_notifications');
    }
};