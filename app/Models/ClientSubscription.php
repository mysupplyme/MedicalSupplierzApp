<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientSubscription extends Model
{
    protected $fillable = [
        'client_id', 'bussiness_subscription_id', 'status', 'start_date', 
        'end_date', 'price', 'platform', 'transaction_id', 'receipt_data'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'receipt_data' => 'json'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function subscription()
    {
        return $this->belongsTo(BusinessSubscription::class, 'bussiness_subscription_id');
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->end_date > now();
    }
}