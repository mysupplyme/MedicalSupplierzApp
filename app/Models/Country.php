<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ['title_en', 'title_ar', 'iso', 'phone_prefix', 'is_default', 'currency_id'];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function residencyUsers()
    {
        return $this->hasMany(User::class, 'residency_country_id');
    }

    public function nationalityUsers()
    {
        return $this->hasMany(User::class, 'nationality_country_id');
    }
}