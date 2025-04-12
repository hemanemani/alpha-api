<?php

namespace App\Models;
use App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $table = 'sellers';

    protected $fillable = [
        'name',
        'company_name',
        'contact_number',
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
    
}
