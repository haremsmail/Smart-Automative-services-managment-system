<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * حاڵەتی تایبەت بۆ ڕۆژێک: shift_id = null واتە پشوو/مۆڵەت، ئەگەر نا شەفتی جێگرەوە.
 */
class TechnicianTimeOff extends Model
{
    protected $table = 'technician_time_off';

    protected $fillable = ['user_id', 'date', 'shift_id', 'reason', 'created_by'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function isDayOff(): bool
    {
        return $this->shift_id === null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
