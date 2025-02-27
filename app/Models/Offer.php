<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Inquiry;

class Offer extends Model
{
    protected $table = 'offers';

    protected $fillable = [
        'offer_number',
        'communication_date',
        'received_sample_amount',
        'sample_dispatched_date',
        'sample_sent_through',
        'sample_received_date',
        'offer_notes',
        'inquiry_id',
    ];

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class, 'inquiry_id');
    }

}
