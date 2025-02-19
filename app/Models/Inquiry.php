<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Offer;


class Inquiry extends Model
{
    protected $table = 'inquiries';

    protected $fillable = [
        'inquiry_number',
        'mobile_number',
        'inquiry_date',
        'product_categories',
        'specific_product',
        'name',
        'location',
        'inquiry_through',
        'inquiry_reference',
        'first_contact_date',
        'first_response',
        'second_contact_date',
        'second_response',
        'third_contact_date',
        'third_response',
        'notes',
        'user_id',
        'status',
        'offers_status'
    ];
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function offers()
    {
        return $this->hasMany(Offer::class, 'inquiry_id');
    }

}
