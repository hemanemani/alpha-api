<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedInquiry extends Model
{
    protected $table = 'blocked_inquiries';

    protected $fillable = [
        'mobile_number',
    ];
}
