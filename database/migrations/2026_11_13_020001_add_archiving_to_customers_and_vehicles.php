<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * مۆدیوڵی ٢: پاراستنی مێژوو.
 * - کڕیار و ئۆتۆمبێل ئەرشیف دەکرێن (soft delete)، هەرگیز بە تەواوی ناسڕدرێنەوە.
 * - سڕینەوەی کڕیار چیتر ئۆتۆمبێلەکانی ناسڕێتەوە (cascade لابرا).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('full_name');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->softDeletes();
            $table->dropForeign(['customer_id']);
            $table->foreign('customer_id')->references('id')->on('customers')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->dropSoftDeletes();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['full_name']);
            $table->dropSoftDeletes();
        });
    }
};
