<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('service_bays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name', 60);
            $table->string('bay_type', 40)->default('general');
            $table->string('status', 20)->default('available');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('service_bays');
    }
};