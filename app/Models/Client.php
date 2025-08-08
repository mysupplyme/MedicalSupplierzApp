<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['uuid', 'type', 'first_name', 'last_name', 'company_name_en', 'company_name_ar', 'email', 'password', 'mobile_number', 'workplace', 'buyer_type', 'specialty_id', 'sub_specialty_id', 'nationality', 'residency', 'is_buyer', 'status'];
    
    public function specialty()
    {
        return $this->belongsTo(Category::class, 'specialty_id');
    }
    
    public function subSpecialty()
    {
        return $this->belongsTo(Category::class, 'sub_specialty_id');
    }
}