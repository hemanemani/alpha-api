<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InternationalOrder;

class InternationalOrderSeller extends Model
{
    protected $table = 'international_orders_sellers';

    protected $fillable = [
        //seller
        'international_order_id',
        'seller_name',
        'seller_address',
        'seller_contact',
        'shipping_name',
        'address_line_1',
        'address_line_2',
        'seller_pincode',
        'seller_contact_person_name',
        'seller_contact_person_number',
        'no_of_boxes',
        'weight_per_unit',
        'dimension_unit',
        'length',
        'width',
        'height',
        'invoice_generate_date',
        'invoice_value',
        'invoice_number',
        'delivery_address',
        'order_ready_date',
        'order_delivery_date',
        'order_dispatch_date',
        
        // invoice
        'invoicing_invoice_generate_date',
        'invoicing_invoice_number',
        'invoice_to',
        'invoice_address',
        'invoice_gstin',
        'packaging_expenses',
        'invoicing_total_amount',
        'total_amount_in_words',
        'product_name',
        'rate_per_kg',
        'total_kg',
        'hsn',
        'invoicing_amount',
        'expenses',
        'products'
    ];
    protected $casts = [
    'products' => 'array',
    ];
    public function international_order()
    {
        return $this->belongsTo(InternationalOrder::class);
    }

}
