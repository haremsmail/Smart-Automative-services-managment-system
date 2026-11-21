<?php

/*
 * ڕێکخستنەکانی مۆدیوڵی ٣ (نۆرە و شەفت).
 * کاتی نۆرەی ئۆنلاین لە system_settings دا هەڵدەگیرێت و بەڕێوەبەر دەیگۆڕێت.
 */
return [
    // ماوەی هەر خانەیەکی نۆرە (خولەک)
    'slot_minutes' => 30,

    // نۆرەی ئۆنلاین تا چەند ڕۆژ لە پێشەوە
    'online_days_ahead' => 14,

    // بنەڕەتی کاتی نۆرەی ئۆنلاین (ئەگەر لە settings دا نەبێت)
    'online_start' => '08:00',
    'online_end' => '22:00',

    // شارەزاییەکانی وەستا
    'skills' => [
        'maintenance' => 'چاککردنەوەی خولی (ڕۆن، فلتەر)',
        'brakes' => 'بریک',
        'engine' => 'بزوێنەر',
        'electrical' => 'کارەبا',
        'suspension' => 'سیستەمی هەڵگرتن (Suspension)',
        'alignment' => 'ڕێکخستنی تایە (Alignment)',
        'tires' => 'تایە',
        'ac' => 'سپلیت (AC)',
        'diagnostics' => 'پشکنینی کۆمپیوتەری',
        'transmission' => 'گێڕ',
    ],

    // جۆرەکانی شوێنی کار
    'bay_types' => [
        'general' => 'گشتی (لیفت)',
        'alignment' => 'Alignment',
        'diagnostics' => 'پشکنین',
        'wash' => 'شوشتن',
    ],

    // خاڵەکانی AI Dispatch (کۆی = 100)
    'dispatch_weights' => [
        'skill' => 40,
        'availability' => 30,
        'workload' => 20,
        'branch' => 10,
    ],

    // کاتژمێری کار کە بە «ڕۆژێکی پڕ» دادەنرێت بۆ دابەشکردنی دادپەروەرانە
    'full_day_minutes' => 480,
];
