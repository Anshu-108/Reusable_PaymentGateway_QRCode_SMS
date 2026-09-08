<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'gateway',
        'amount',
        'currency',
        'status',
        'payment_id',
        'receipt',
        'customer_name',
        'customer_email',
        'customer_phone',
        'description',
        'gateway_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_response' => 'array',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentRefund::class);
    }

    public function callbacks(): HasMany
    {
        return $this->hasMany(PaymentCallback::class);
    }
}