<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InternationalOrderSeller;
use App\Models\InternationalOffer;
use App\Models\User;

class InternationalOrder extends Model
{
    protected $table = 'international_orders';
    protected $fillable = [
        'international_offer_id',
        'order_number',
        'name',
        'mobile_number',
        'seller_assigned',
        'quantity',
        'seller_offer_rate',
        'gst',
        'buyer_offer_rate',
        'final_shipping_value',
        'total_amount', 
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

    public function international_sellers() {
        return $this->hasMany(InternationalOrderSeller::class);
    }
    public function international_offer()
    {
        return $this->belongsTo(\App\Models\InternationalOffer::class, 'international_offer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

}
