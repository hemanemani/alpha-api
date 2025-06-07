<?php

namespace App\Models;
use App\Models\Seller;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = ['name','variety','price','seller_price','moq','remarks','rate','seller_id'];

    public function seller() {
        return $this->belongsTo(Seller::class);
    }
    
}
