<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSupplierB2b extends Model
{
    protected $table = 'product_supplier_b2b';
    protected $fillable = ['product_id', 'supplier_id', 'title', 'description', 'price', 'date', 'location'];
    
    public function category()
    {
        return $this->belongsTo(Category::class, 'product_id');
    }
}