<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Inquiry;
use Laravel\Sanctum\HasApiTokens;
use App\Models\UploadInquiry;
use App\Models\Order;
use App\Models\InternationInquiry;
use App\Models\InternationalOrder;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_name',
        'mobile_number',
        'access_level',
        'allowed_pages',
        'status',
        'is_admin'
    ];

    protected $attributes = [
        'is_admin' => 'integer',
    ];
    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'allowed_pages' => 'array',
        ];
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class,'user_id');
    }
    public function international_inquiries()
    {
        return $this->hasMany(InternationInquiry::class,'user_id');
    }
    public function hasAccess($type)
    {
        return $this->access_level === $type;
    }
    public function bulk_uploads()
    {
        return $this->hasMany(UploadInquiry::class,'uploaded_by');
    }
     public function orders()
    {
        return $this->hasMany(Order::class,'user_id');
    }
    public function international_orders()
    {
        return $this->hasMany(InternationalOrder::class,'user_id');
    }

}
