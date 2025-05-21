<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrderSeller;
use App\Models\Offer;
use App\Models\User;


class Order extends Model
{
    protected $table = 'orders';
    protected $fillable = [
        'offer_id',
        'order_number',
        'name',
        'mobile_number',
        'seller_assigned',
        'buyer_gst_number',
        'buyer_pan',
        'buyer_bank_details',
        'amount_received',
        'amount_received_date',
        'amount_paid',
        'amount_paid_date',
        'logistics_through',
        'logistics_agency',
        'shipping_estimate_value',
        'buyer_final_shipping_value',
        'buyer_total_amount',
        'status',
        'user_id',
        'sellerdetails'
    ];

    protected $casts = [
    'sellerdetails' => 'array',
    ];


    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function sellers() {
        return $this->hasMany(OrderSeller::class);
    }
    public function offer()
    {
        return $this->belongsTo(\App\Models\Offer::class, 'offer_id');
    }

}
