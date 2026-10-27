<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    protected $table = 'equipment';
    protected $fillable = [
        'branch_id', 'name', 'serial_number', 'equipment_type',
        'purchase_date', 'next_service_due', 'status'
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'next_service_due' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(EquipmentMaintenance::class);
    }
}