<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['title_en', 'title_ar', 'code_en', 'code_ar', 'decimal_digits', 'is_default', 'rate', 'status'];

    public function countries()
    {
        return $this->hasMany(Country::class);
    }
}