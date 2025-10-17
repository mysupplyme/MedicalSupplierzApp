<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Client extends Model
{
    protected $fillable = [
        'uuid', 'type', 'first_name', 'last_name', 'email', 'password', 
        'mobile_number', 'country_code', 'job_title', 'workplace', 
        'specialty_id', 'sub_specialty_id', 'nationality', 'residency', 
        'buyer_type', 'is_buyer', 'status', 'reset_token', 'reset_expired_at'
    ];

    protected $hidden = ['password', 'reset_token'];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    public function productSuppliers()
    {
        return $this->hasMany(ProductSupplier::class);
    }
    
    public function countryCode()
    {
        return $this->belongsTo(Country::class, 'country_code');
    }
    
    public function clientSetting()
    {
        return $this->hasOne(ClientSetting::class);
    }
    
    public function businessInfo()
    {
        return $this->hasOne(ClientBusinessInfo::class, 'client_id');
    }
}