<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicianSchedule extends Model
{
    /** Carbon dayOfWeek: 0 = یەکشەممە. ڕیزبەندی هەفتەی کوردستان لە شەممەوە دەستپێدەکات */
    public const WEEKDAYS = [
        6 => 'شەممە', 0 => 'یەکشەممە', 1 => 'دووشەممە', 2 => 'سێشەممە',
        3 => 'چوارشەممە', 4 => 'پێنجشەممە', 5 => 'هەینی',
    ];

    protected $fillable = ['user_id', 'weekday', 'shift_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
