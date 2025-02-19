<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedInternationalInquiry extends Model
{
    protected $table = 'blocked_international_inquiries';

    protected $fillable = [
        'mobile_number',
    ];
}
