<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Shift extends Model
{
    public const COLORS = [
        'amber' => 'زەرد', 'blue' => 'شین', 'emerald' => 'سەوز',
        'violet' => 'مۆر', 'rose' => 'سوور', 'slate' => 'خۆڵەمێشی',
    ];

    protected $fillable = ['name', 'start_time', 'end_time', 'color', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** شەفتی شەو: کۆتاییەکەی دەگاتە ڕۆژی دواتر */
    public function isOvernight(): bool
    {
        return substr($this->end_time, 0, 5) <= substr($this->start_time, 0, 5);
    }

    public function label(): string
    {
        return $this->name.' ('.substr($this->start_time, 0, 5).'–'.substr($this->end_time, 0, 5).')';
    }

    /**
     * ماوەی ئەم شەفتە ئەگەر لە ڕۆژی $date دەستپێبکات.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function windowOn(Carbon $date): array
    {
        $start = $date->copy()->startOfDay()->setTimeFromTimeString($this->start_time);
        $end = $date->copy()->startOfDay()->setTimeFromTimeString($this->end_time);

        if ($this->isOvernight()) {
            $end->addDay();
        }

        return [$start, $end];
    }
}
