<?php

namespace App\Models;
use App\Models\Seller;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = ['name', 'price','seller_price','seller_id'];

    public function seller() {
        return $this->belongsTo(Seller::class);
    }
    
}
