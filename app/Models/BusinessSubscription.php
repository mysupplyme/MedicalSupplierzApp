<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSubscription extends Model
{
    protected $table = 'bussiness_subscriptions';
    protected $fillable = ['name_en', 'name_ar', 'description_en', 'description_ar', 'period', 'type', 'cost', 'status', 'ios_plan_id', 'android_plan_id'];
    
    public function subscriptionBuyerTypes()
    {
        return $this->hasMany(SubscriptionBuyerType::class, 'bussiness_subscription_id');
    }
}