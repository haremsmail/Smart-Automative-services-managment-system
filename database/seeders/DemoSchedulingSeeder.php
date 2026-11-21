<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Role;
use App\Models\ServiceBay;
use App\Models\ServiceCatalog;
use App\Models\Shift;
use App\Models\TechnicianSchedule;
use App\Models\TechnicianSkill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * داتای نموونەی مۆدیوڵی ٣: شەفت، خزمەتگوزاری، شوێنی کار، ٣ وەستا بە شارەزایی و خشتەی هەفتانە.
 * تەنها لە local ڕادەکرێت. وەستاکان پاسوۆردی هەڕەمەکییان هەیە (تەنها بۆ خشتە، نەک چوونەژوورەوە).
 */
class DemoSchedulingSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('is_active', true)->orderBy('id')->first();
        if (! $branch) {
            return;
        }

        // ---- شەفتەکان
        $day = Shift::firstOrCreate(['name' => '☀️ ڕۆژ'], ['start_time' => '08:00', 'end_time' => '16:00', 'color' => 'amber']);
        $night = Shift::firstOrCreate(['name' => '🌙 شەو'], ['start_time' => '20:00', 'end_time' => '04:00', 'color' => 'blue']);
        $full = Shift::firstOrCreate(['name' => '🔄 تەواو'], ['start_time' => '08:00', 'end_time' => '20:00', 'color' => 'emerald']);

        // ---- خزمەتگوزارییەکان
        $services = [
            ['چاککردنەوەی خولی', 'maintenance', 'general', 'گۆڕینی ڕۆن و فلتەر', 0.5, 25000],
            ['چاککردنەوەی خولی', 'maintenance', 'general', 'گۆڕینی فلتەری هەوا', 0.25, 10000],
            ['بریک', 'brakes', 'general', 'گۆڕینی پاتی بریکی پێشەوە', 1.5, 40000],
            ['بریک', 'brakes', 'general', 'تراشینی دیسک', 1, 30000],
            ['بزوێنەر', 'engine', 'general', 'گۆڕینی قایشی تایمینگ', 3, 120000],
            ['کارەبا', 'electrical', 'diagnostics', 'پشکنینی کۆمپیوتەری (OBD)', 0.5, 20000],
            ['تایە', 'alignment', 'alignment', 'ڕێکخستنی تایە (Alignment)', 1, 30000],
            ['سپلیت', 'ac', 'general', 'پڕکردنەوەی غازی سپلیت', 0.75, 35000],
        ];
        foreach ($services as [$category, $skill, $bayType, $name, $hours, $price]) {
            ServiceCatalog::firstOrCreate(['name' => $name], [
                'category' => $category, 'skill' => $skill, 'required_bay_type' => $bayType,
                'standard_hours' => $hours, 'base_price' => $price, 'is_active' => true,
            ]);
        }

        // ---- شوێنەکانی کار
        foreach ([['لیفتی ١', 'general'], ['لیفتی ٢', 'general'], ['شوێنی Alignment', 'alignment'], ['شوێنی پشکنین', 'diagnostics']] as [$name, $type]) {
            ServiceBay::firstOrCreate(['branch_id' => $branch->id, 'name' => $name], ['bay_type' => $type, 'status' => 'available']);
        }

        // ---- وەستاکان
        $roleId = Role::where('name', 'technician')->value('id');
        $techs = [
            ['کاروان عەلی', 'karwan', ['brakes' => 5, 'maintenance' => 4, 'alignment' => 2], [6 => $day, 0 => $day, 1 => $day, 2 => $day, 3 => $day, 4 => $day]],
            ['هێمن ڕەشید', 'hemn', ['engine' => 5, 'electrical' => 4, 'maintenance' => 3, 'ac' => 3], [6 => $night, 0 => $night, 1 => $night, 3 => $night, 4 => $night, 5 => $night]],
            ['ئاری کەریم', 'ari', ['alignment' => 5, 'brakes' => 3, 'maintenance' => 3, 'ac' => 4], [6 => $full, 0 => $full, 2 => $full, 3 => $full, 4 => $day]],
        ];

        foreach ($techs as [$name, $slug, $skills, $week]) {
            $user = User::firstOrCreate(
                ['email' => "{$slug}@automaster.test"],
                ['name' => $name, 'password' => Str::random(32), 'branch_id' => $branch->id, 'status' => 'active'],
            );
            $user->forceFill(['role_id' => $roleId])->save();

            foreach ($skills as $skill => $level) {
                TechnicianSkill::updateOrCreate(['user_id' => $user->id, 'skill' => $skill], ['level' => $level]);
            }
            foreach ($week as $weekday => $shift) {
                TechnicianSchedule::updateOrCreate(['user_id' => $user->id, 'weekday' => $weekday], ['shift_id' => $shift->id]);
            }
        }
    }
}
