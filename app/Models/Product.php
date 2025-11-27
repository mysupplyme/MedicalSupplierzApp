<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'client_id', 'title_ar', 'title_en', 'country_id', 'unit_id',
        'image', 'short_description_ar', 'short_description_en',
        'description_ar', 'description_en', 'status', 'slug',
        'conference_register_link', 'conference_speakers_trainers',
        'conference_datetime', 'conference_duration', 'conference_venue'
    ];

    public function suppliers()
    {
        return $this->hasMany(ProductSupplier::class);
    }
    
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }
    
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->whereHas('categories', function($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });
    }
}