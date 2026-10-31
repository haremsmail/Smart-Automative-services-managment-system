<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;
class AuditLog extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'user_id', 'action', 'model_type', 'model_id',
        'old_values', 'new_values', 'ip_address', 'user_agent', 'created_at'
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }
      protected static function booted(): void
    {
        // تۆمار ناگۆڕدرێت و ناسڕدرێتەوە
        static::updating(function () {
            throw new LogicException('تۆماری گۆڕانکاری ناگۆڕدرێت.');
        });
        static::deleting(function () {
            throw new LogicException('تۆماری گۆڕانکاری ناسڕدرێتەوە.');
        });
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
