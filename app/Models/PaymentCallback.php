<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentCallback extends Model
{
    protected $table = 'payment_callbacks';

    protected $fillable = [
        'payment_id',
        'event',
        'razorpay_order_id',
        'razorpay_payment_id',
        'payload',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}