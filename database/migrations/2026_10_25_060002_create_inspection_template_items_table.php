<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inspection_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('inspection_templates')->cascadeOnDelete();
            $table->string('category', 60);
            $table->string('item_name', 120);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('inspection_template_items');
    }
};