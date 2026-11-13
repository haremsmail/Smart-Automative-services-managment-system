<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * مۆدیوڵی ٣: نۆرەبڕین و ڕێکخستنی زیرەکی وۆرکشۆپ
 * - خزمەتگوزاری: شارەزایی و جۆری شوێنی پێویست
 * - شەفت و خشتەی هەفتانەی وەستاکان، مۆڵەت
 * - شارەزایی وەستاکان (بۆ AI Dispatch)
 * - نۆرە: ماوە، وەستا، سەرچاوە، ژمارەی نۆرە، لینکی کڕیار
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_catalog', function (Blueprint $table) {
            $table->string('skill', 40)->nullable()->after('category');
            $table->string('required_bay_type', 40)->default('general')->after('skill');
        });

        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->time('start_time');
            $table->time('end_time'); // ئەگەر لە start_time کەمتر بێت، واتە دەگاتە ڕۆژی دواتر (شەفتی شەو)
            $table->string('color', 20)->default('blue');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('technician_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday'); // 0 = یەکشەممە ... 6 = شەممە
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'weekday']);
        });

        Schema::create('technician_time_off', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete(); // null = پشوو، ئەگەر نا = شەفتی جێگرەوە
            $table->string('reason', 200)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'date']);
        });

        Schema::create('technician_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('skill', 40);
            $table->unsignedTinyInteger('level'); // 1 تا 5
            $table->timestamps();
            $table->unique(['user_id', 'skill']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->string('reference', 20)->nullable()->unique()->after('id');
            $table->string('public_token', 64)->nullable()->unique()->after('reference');
            $table->foreignId('technician_id')->nullable()->after('bay_id')->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('duration_minutes')->default(30)->after('scheduled_at');
            $table->dateTime('ends_at')->nullable()->index()->after('duration_minutes');
            $table->string('source', 20)->default('phone')->after('status');
            $table->string('cancel_reason', 255)->nullable()->after('internal_notes');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('confirmed_at')->nullable();
        });

        Schema::create('appointment_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('service_catalog_id')->constrained('service_catalog');
            $table->string('name', 150);              // ناوی خزمەتگوزاری لەو کاتەدا
            $table->decimal('standard_hours', 5, 2);  // کاتی خەمڵێنراو لەو کاتەدا
            $table->decimal('price', 12, 2);          // نرخ لەو کاتەدا
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_services');

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('technician_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropUnique(['reference']);
            $table->dropUnique(['public_token']);
            $table->dropIndex(['ends_at']);
            $table->dropColumn(['reference', 'public_token', 'duration_minutes', 'ends_at', 'source', 'cancel_reason', 'confirmed_at']);
        });

        Schema::dropIfExists('technician_skills');
        Schema::dropIfExists('technician_time_off');
        Schema::dropIfExists('technician_schedules');
        Schema::dropIfExists('shifts');

        Schema::table('service_catalog', function (Blueprint $table) {
            $table->dropColumn(['skill', 'required_bay_type']);
        });
    }
};
