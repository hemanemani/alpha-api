<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedOrder extends Model
{
    protected $table = 'blocked_orders';

    protected $fillable = [
        'mobile_number',
    ];
}
