<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSupplier extends Model
{
    protected $table = 'product_suppliers';
    protected $fillable = ['product_id', 'supplier_id', 'price', 'availability'];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}