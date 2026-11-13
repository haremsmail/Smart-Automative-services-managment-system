<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    public const STATUSES = [
        'pending' => 'چاوەڕێی پشتڕاستکردنەوە',
        'scheduled' => 'نۆرە دانرا',
        'arrived' => 'کڕیار گەیشت',
        'completed' => 'تەواوبوو',
        'cancelled' => 'هەڵوەشێنرایەوە',
        'no_show' => 'نەهات',
    ];

    /** دۆخەکانی کە کات و شوێن دەگرن */
    public const ACTIVE = ['pending', 'scheduled', 'arrived'];

    /** گۆڕانی دۆخ: تەنها ئەم ڕێگایانە ڕێگەپێدراون */
    public const TRANSITIONS = [
        'pending' => ['scheduled', 'cancelled'],
        'scheduled' => ['arrived', 'cancelled', 'no_show'],
        'arrived' => ['completed'],
        'completed' => [],
        'cancelled' => [],
        'no_show' => [],
    ];

    public const SOURCES = [
        'phone' => 'تەلەفۆن',
        'walk_in' => 'بەبێ نۆرە (Walk-in)',
        'whatsapp' => 'واتسئاپ',
        'online' => 'ئۆنلاین',
    ];

    protected $fillable = [
        'reference', 'public_token', 'customer_id', 'vehicle_id', 'branch_id', 'bay_id', 'technician_id',
        'scheduled_at', 'duration_minutes', 'ends_at', 'status', 'source',
        'requested_services', 'customer_notes', 'internal_notes', 'cancel_reason',
        'created_by', 'confirmed_at',
    ];

    protected $hidden = ['public_token'];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'ends_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE);
    }

    /** نۆرەکان کە لەگەڵ ماوەی [start, end) تێکەڵ دەبن */
    public function scopeOverlapping(Builder $query, $start, $end): Builder
    {
        return $query->where('scheduled_at', '<', $end)->where('ends_at', '>', $start);
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE, true);
    }

    public function canMoveTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? $this->source;
    }

    public function totalPrice(): float
    {
        return (float) $this->items->sum('price');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class)->withTrashed();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function bay(): BelongsTo
    {
        return $this->belongsTo(ServiceBay::class, 'bay_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AppointmentItem::class);
    }

    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class);
    }
}
