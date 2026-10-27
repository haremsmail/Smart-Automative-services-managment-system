<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inspection extends Model
{
    protected $fillable = [
        'vehicle_id', 'inspector_id', 'template_id', 'odometer_reading',
        'status', 'public_token', 'summary', 'sent_at', 'viewed_at', 'completed_at'
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'completed_at' => 'datetime',
            'odometer_reading' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplate::class, 'template_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InspectionItem::class);
    }
}