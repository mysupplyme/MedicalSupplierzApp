<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSubscription extends Model
{
    protected $table = 'bussiness_subscriptions';
    protected $fillable = ['title', 'description', 'price', 'duration', 'features'];
    
    public function subscriptionBuyerTypes()
    {
        return $this->hasMany(SubscriptionBuyerType::class, 'bussiness_subscription_id');
    }
}