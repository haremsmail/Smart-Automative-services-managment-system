<?php

/*
 * هەموو کلیلەکانی دەسەڵاتی سیستەم لێرەدایە.
 * seeder و Gate هەمووی لێرە دەخوێننەوە، بۆیە هەر ناوێک کە لە seeder دا بەکار دێت، دەبێت لێرە هەبێت.
 */
return [
    'groups' => [
        'users' => [
            'users.view' => 'بینینی کارمەندان',
            'users.create' => 'زیادکردنی کارمەند',
            'users.update' => 'دەستکاریکردنی کارمەند',
            'users.assign_role' => 'گۆڕینی ڕۆڵی کارمەند',
            'users.grant_permission' => 'دانانی دەسەڵاتی تاکەکەسی',
        ],
        'roles' => [
            'roles.manage' => 'بەڕێوەبردنی ڕۆڵ و دەسەڵات',
        ],
        'branches' => [
            'branches.manage' => 'بەڕێوەبردنی لقەکان',
        ],
        'audit' => [
            'audit.view' => 'بینینی تۆماری گۆڕانکاری',
        ],
        'payments' => [
            'payments.create' => 'زیادکردنی پارەدان',
        ],
        'pos' => [
            'pos.open_session' => 'کردنەوەی دانیشتنی کاشێر',
        ],
    ],
];
