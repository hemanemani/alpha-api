<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audience extends Model
{
    protected $table = 'audiences';
    protected $fillable = [
        'label',
        'value',
    ];

}
