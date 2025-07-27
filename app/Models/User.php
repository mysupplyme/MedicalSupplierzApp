<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'specialty_id',
        'sub_specialty_id',
        'residency_country_id',
        'nationality_country_id',
        'avatar',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function subSpecialty()
    {
        return $this->belongsTo(SubSpecialty::class);
    }

    public function residencyCountry()
    {
        return $this->belongsTo(Country::class, 'residency_country_id');
    }

    public function nationalityCountry()
    {
        return $this->belongsTo(Country::class, 'nationality_country_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isDoctor()
    {
        return $this->role === 'doctor';
    }
}