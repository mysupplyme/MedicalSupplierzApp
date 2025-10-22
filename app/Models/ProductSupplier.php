<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSupplier extends Model
{
    protected $table = 'product_suppliers';
    protected $fillable = [
        'client_id', 'product_id', 'country_id', 'brand_id', 'unit_id',
        'image', 'short_description_ar', 'short_description_en', 
        'description_ar', 'description_en', 'condition', 'view_status',
        'warranty_id', 'min_order_quantity_id', 'in_stock_quantity',
        'alert_quantity', 'return_time_id', 'delivery_time_id', 'status'
    ];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    public function productDetails()
    {
        return $this->hasMany('App\Models\ProductSupplierB2b', 'product_supplier_id');
    }
    
    public function productDetailsByType()
    {
        return $this->hasOne('App\Models\ProductSupplierB2b', 'product_supplier_id')->where('type', 'b2b');
    }
    
    public function warranty()
    {
        return $this->belongsTo('App\Models\Warranty', 'warranty_id');
    }
    
    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_id');
    }
    
    public function deliveryTime()
    {
        return $this->belongsTo('App\Models\DeliveryTime', 'delivery_time_id');
    }
    
    public function returnTime()
    {
        return $this->belongsTo('App\Models\ReturnTime', 'return_time_id');
    }
}