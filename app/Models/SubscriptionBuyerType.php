<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionBuyerType extends Model
{
    protected $table = 'subscription_buyer_types';
    protected $fillable = ['bussiness_subscription_id', 'buyer_type_id'];
    
    public function businessSubscription()
    {
        return $this->belongsTo(BusinessSubscription::class, 'bussiness_subscription_id');
    }
}