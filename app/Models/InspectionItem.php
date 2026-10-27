<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionItem extends Model
{
    protected $fillable = [
        'inspection_id', 'template_item_id', 'name', 'condition', 'measured_value', 'notes'
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function templateItem(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplateItem::class, 'template_item_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(InspectionMedia::class);
    }
}