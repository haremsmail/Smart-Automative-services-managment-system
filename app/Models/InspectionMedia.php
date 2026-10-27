<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionMedia extends Model
{
    protected $table = 'inspection_media';

    protected $fillable = ['inspection_item_id', 'media_type', 'file_path', 'caption'];

    public function inspectionItem(): BelongsTo
    {
        return $this->belongsTo(InspectionItem::class);
    }
}