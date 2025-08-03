<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    public function events()
    {
        return $this->hasMany(Event::class);
    }
    
    public function children()
    {
        return $this->hasMany(Category::class, 'category_id');
    }
}