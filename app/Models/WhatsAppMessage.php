<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppMessage extends Model
{
    protected $fillable = [
        'from_number', 'message_text', 'message_type', 'response_sent', 'webhook_data'
    ];

    protected $casts = [
        'webhook_data' => 'array'
    ];
}