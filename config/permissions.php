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
        'customers' => [
            'customers.view' => 'بینینی کڕیاران',
            'customers.create' => 'زیادکردنی کڕیار',
            'customers.update' => 'دەستکاریکردنی کڕیار',
            'customers.delete' => 'ئەرشیفکردنی کڕیار',
        ],
        'vehicles' => [
            'vehicles.view' => 'بینینی ئۆتۆمبێلەکان',
            'vehicles.create' => 'زیادکردنی ئۆتۆمبێل',
            'vehicles.update' => 'دەستکاریکردنی ئۆتۆمبێل',
            'vehicles.transfer' => 'گواستنەوەی خاوەندارێتی ئۆتۆمبێل',
            'vehicles.delete' => 'ئەرشیفکردنی ئۆتۆمبێل',
        ],
        'appointments' => [
            'appointments.view' => 'بینینی نۆرە و تابلۆی وۆرکشۆپ',
            'appointments.manage' => 'دانان، گۆڕین و سپاردنی نۆرە',
        ],
        'scheduling' => [
            'services.manage' => 'بەڕێوەبردنی خزمەتگوزاری و نرخ',
            'bays.manage' => 'بەڕێوەبردنی شوێنەکانی کار',
            'technicians.manage' => 'شارەزایی، شەفت و مۆڵەتی وەستاکان',
        ],
        'payments' => [
            'payments.create' => 'زیادکردنی پارەدان',
        ],
        'pos' => [
            'pos.open_session' => 'کردنەوەی دانیشتنی کاشێر',
        ],
    ],
];
