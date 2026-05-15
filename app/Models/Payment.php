<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'payment_method',
        'payment_gateway',
        'gateway_order_id',
        'gateway_transaction_id',
        'gateway_payment_url',
        'gateway_payload',
        'payment_proof',
        'payment_status',
        'paid_at',
    ];

    protected $casts = [
        'gateway_payload' => 'array',
        'paid_at' => 'datetime',
    ];
    public function order()
{
    return $this->belongsTo(Order::class);
}
}
