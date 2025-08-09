<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSubscription extends Model
{
    protected $table = 'bussiness_subscriptions';
    protected $fillable = ['title_en', 'title_ar', 'description_en', 'description_ar', 'price', 'duration_days', 'features_en', 'features_ar'];
    
    public function subscriptionBuyerTypes()
    {
        return $this->hasMany(SubscriptionBuyerType::class, 'bussiness_subscription_id');
    }
}