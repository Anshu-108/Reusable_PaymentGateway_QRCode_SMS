<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SMSLog extends Model
{

    protected $table = 'sms_logs';
    protected $fillable = [
        'sms_template_id',
        'phone_number',
        'message',
        'template_id',
        'header',
        'entity_id',
        'provider',
        'status',
        'gateway_message_id',
        'gateway_response',
        'error_message',
        'requested_at',
        'sent_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(
            SMSTemplate::class,
            'sms_template_id'
        );
    }
}