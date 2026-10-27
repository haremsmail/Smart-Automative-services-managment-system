<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionTemplateItem extends Model
{
    protected $fillable = ['template_id', 'category', 'item_name', 'order'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplate::class, 'template_id');
    }
}