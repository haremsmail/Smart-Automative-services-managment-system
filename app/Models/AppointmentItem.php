<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** خزمەتگوزارییەکی ناو نۆرەیەک (نرخ و کات لەو کاتەدا هەڵدەگیرێت) */
class AppointmentItem extends Model
{
    protected $table = 'appointment_services';

    protected $fillable = ['appointment_id', 'service_catalog_id', 'name', 'standard_hours', 'price'];

    protected function casts(): array
    {
        return ['standard_hours' => 'decimal:2', 'price' => 'decimal:2'];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_catalog_id');
    }
}
