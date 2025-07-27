<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubSpecialty extends Model
{
    use HasFactory;

    protected $fillable = ['specialty_id', 'name', 'is_active'];

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}