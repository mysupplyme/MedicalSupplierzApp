<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientBusinessInfo extends Model
{
    protected $table = 'client_business_infos';
    
    protected $fillable = [
        'client_id', 'reg_number', 'business_type', 'uuid'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}