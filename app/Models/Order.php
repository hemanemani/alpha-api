<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrderSeller;

class Order extends Model
{
    protected $table = 'orders';
    protected $fillable = [
        'offer_id',
        'order_number',
        'name',
        'contact_number',
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
    ];

    public function sellers() {
        return $this->hasMany(OrderSeller::class);
    }
    

}
