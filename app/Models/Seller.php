<?php

namespace App\Models;
use App\Models\Product;
use App\Models\Order;


use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $table = 'sellers';

    protected $fillable = [
        'name',
        'company_name',
        'mobile_number',
        'email',
        'gst',
        'pan',
        'bank_details',
        'pickup_address',
        'status',
    ];
    public function products() {
        return $this->hasMany(Product::class);
    }
    public function order() {
        return $this->belongsTo(Order::class);
    } 
}
