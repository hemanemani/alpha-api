<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $table = "ads";
    protected $fillable = [
        'ad_title',
        'type',
        'date_published',
        'platform',
        'status',
        'goal',
        'audience',
        'budget_set',
        'views',
        'reach',
        'messages_received',
        'cost_per_message',
        'top_location',
        'post_reactions',
        'post_shares',
        'post_save',
        'total_amount_spend',
        'duration',
    ];
}
