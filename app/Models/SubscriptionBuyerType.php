<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionBuyerType extends Model
{
    protected $table = 'subscription_buyer_types';
    protected $fillable = ['subscription_id', 'buyer_type'];
    
    public function businessSubscription()
    {
        return $this->belongsTo(BusinessSubscription::class, 'subscription_id');
    }
}