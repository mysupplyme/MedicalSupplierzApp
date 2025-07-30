<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['first_name', 'last_name', 'email', 'phone', 'company', 'address', 'buyer_type', 'specialty_id', 'sub_specialty_id', 'subscription_id', 'subscription_start', 'subscription_end'];
    
    public function specialty()
    {
        return $this->belongsTo(Category::class, 'specialty_id');
    }
    
    public function subSpecialty()
    {
        return $this->belongsTo(Category::class, 'sub_specialty_id');
    }
}