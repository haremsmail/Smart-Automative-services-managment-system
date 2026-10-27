<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentMaintenance extends Model
{
    protected $table = 'equipment_maintenance';
    protected $fillable = [
        'equipment_id', 'service_type', 'performed_by', 'service_date', 'cost', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'cost' => 'decimal:2',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}