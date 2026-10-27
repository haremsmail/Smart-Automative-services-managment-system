<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            $table->foreignId('role_id')->nullable()->after('branch_id')->constrained('roles')->nullOnDelete();
            $table->string('phone', 30)->nullable()->after('email');
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('phone');
            $table->string('status', 20)->default('active')->after('hourly_rate');
            $table->string('avatar')->nullable()->after('status');
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['role_id']);
            $table->dropColumn(['branch_id', 'role_id', 'phone', 'hourly_rate', 'status', 'avatar']);
        });
    }
};