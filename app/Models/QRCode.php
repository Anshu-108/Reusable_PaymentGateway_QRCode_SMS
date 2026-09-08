<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QRCode extends Model
{
    protected $table = 'qr_codes';

    protected $fillable = [
        'qr_id',
        'name',
        'phone',
        'email',
        'website',
        'address',
        'qr_data',
        'qr_image',
    ];
}