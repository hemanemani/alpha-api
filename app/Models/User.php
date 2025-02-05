<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Inquiry;
use Laravel\Sanctum\HasApiTokens;

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
        'allowed_pages'
    ];

    protected $attributes = [
        'is_admin' => false,
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
        return $this->hasMany(Inquiry::class,'user_id');
    }
    public function hasAccess($type)
    {
        return $this->access_level === $type;
    }

}
