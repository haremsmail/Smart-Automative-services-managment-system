<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNotification extends Model
{
    protected $fillable = [
        'customer_id', 'user_id', 'channel', 'title', 'body', 'status', 'sent_at'
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}