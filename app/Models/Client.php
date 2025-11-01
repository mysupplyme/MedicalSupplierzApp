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
        'buyer_type', 'is_buyer', 'status', 'reset_token', 'reset_expired_at',
        'email_verified_at', 'email_activation_code'
    ];

    protected $hidden = ['password', 'reset_token', 'email_activation_code'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

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

    public function isEmailVerified()
    {
        return !is_null($this->email_verified_at);
    }

    public function markEmailAsVerified()
    {
        $this->email_verified_at = now();
        $this->email_activation_code = null;
        $this->save();
    }
}