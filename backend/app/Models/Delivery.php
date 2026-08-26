<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'courier_id',
        'status',
        'fee',
        'pickup_address',
        'delivery_address',
        'pickup_latitude',
        'pickup_longitude',
        'delivery_latitude',
        'delivery_longitude',
        'picked_up_at',
        'delivered_at',
        'notes',
        'proof_image',
    ];

    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'pickup_latitude' => 'decimal:8',
            'pickup_longitude' => 'decimal:8',
            'delivery_latitude' => 'decimal:8',
            'delivery_longitude' => 'decimal:8',
            'picked_up_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }
}
