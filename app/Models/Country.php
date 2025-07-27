<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'phone_code', 'is_active'];

    public function residencyUsers()
    {
        return $this->hasMany(User::class, 'residency_country_id');
    }

    public function nationalityUsers()
    {
        return $this->hasMany(User::class, 'nationality_country_id');
    }
}