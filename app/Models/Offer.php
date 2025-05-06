<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Inquiry;
use App\Models\Order;


class Offer extends Model
{
    protected $table = 'offers';

    protected $fillable = [
        'offer_number',
        'offer_date',
        'communication_date',
        'received_sample_amount',
        'sent_sample_amount',
        'sample_dispatched_date',
        'sample_sent_through',
        'sample_received_date',
        'offer_notes',
        'sample_send_address',
        'inquiry_id',
    ];

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class, 'inquiry_id');
    }
    public function order()
    {
        return $this->hasOne(\App\Models\Order::class, 'offer_id');
    }

}
