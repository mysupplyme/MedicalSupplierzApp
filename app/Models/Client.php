<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'status'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    public function productSuppliers()
    {
        return $this->hasMany(ProductSupplier::class);
    }
}