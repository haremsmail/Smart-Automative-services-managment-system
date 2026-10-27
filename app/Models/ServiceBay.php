<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceBay extends Model
{
    protected $fillable = ['branch_id', 'name', 'bay_type', 'status'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'bay_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'bay_id');
    }
}