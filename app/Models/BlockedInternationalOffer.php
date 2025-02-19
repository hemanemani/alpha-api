<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedInternationalOffer extends Model
{
    protected $table = 'blocked_international_offers';

    protected $fillable = [
        'mobile_number',
    ];
}
