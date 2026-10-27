<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inspection_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_item_id')->constrained('inspection_items')->cascadeOnDelete();
            $table->string('media_type', 10)->default('photo');
            $table->text('file_path');
            $table->string('caption', 250)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('inspection_media');
    }
};