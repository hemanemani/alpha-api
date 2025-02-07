<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class UploadInquiry extends Model
{
    protected $table = 'bulk_uploads';

    protected $fillable = [
        'file_name',
        'file_path',
        'uploaded_by',
        'uploaded_at',
        'file_size',
        'status'
    ];
    public function user()
    {
        return $this->belongsTo(User::class,'uploaded_by');
    }
}
