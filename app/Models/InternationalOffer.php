<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InternationInquiry;

class InternationalOffer extends Model
{
    protected $table = 'international_offers';

    protected $fillable = [
        'offer_number',
        'communication_date',
        'received_sample_amount',
        'sent_sample_amount',
        'sample_dispatched_date',
        'sample_sent_through',
        'sample_received_date',
        'offer_notes',
        'international_inquiry_id',
    ];

    public function inquiry()
    {
        return $this->belongsTo(InternationInquiry::class, 'international_inquiry_id');
    }

}
