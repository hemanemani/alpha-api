<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InternationInquiry;
use App\Models\InternationalOrder;


class InternationalOffer extends Model
{
    protected $table = 'international_offers';

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
        'international_inquiry_id',
                'status'

    ];

    public function international_inquiry()
    {
        return $this->belongsTo(InternationInquiry::class, 'international_inquiry_id');
    }
    public function international_order()
    {
        return $this->hasOne(\App\Models\InternationalOrder::class, 'international_offer_id');
    }


}
