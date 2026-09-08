<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SMSTemplate extends Model
{
    protected $table = 'sms_templates';
    protected $fillable = [
        'template_id',
        'template_name',
        'header',
        'entity_id',
        'template_message',
        'provider',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(SMSLog::class, 'sms_template_id');
    }
}