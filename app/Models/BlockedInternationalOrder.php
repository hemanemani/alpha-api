<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedInternationalOrder extends Model
{
    protected $table = 'blocked_international_orders';

    protected $fillable = [
        'mobile_number',
    ];
}
