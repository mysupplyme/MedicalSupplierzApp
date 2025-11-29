<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientSubscription extends Model
{
    protected $fillable = [
        'client_id', 'subscription_id', 'status', 'payment_status', 'start_at', 
        'end_at', 'platform', 'transaction_id', 'receipt', 'product_id', 'response'
    ];

    protected $casts = [
        'start_at' => 'date',
        'end_at' => 'date',
        'response' => 'json'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function subscription()
    {
        return $this->belongsTo(BusinessSubscription::class, 'subscription_id');
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->end_at > now()->toDateString();
    }
}