<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',         // 🔹 role column add kiya
        'location_id',  // 🔹 location relation ke liye
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
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // 🔹 Required by JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // 🔹 Required by JWT
    public function getJWTCustomClaims()
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    // 🔹 User belongs to one location (clinic)
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // 🔹 User can be staff (1-to-1 relation with staff table)
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    // 🔹 If user is client (1-to-1 relation with clients table)
    public function client()
    {
        return $this->hasOne(Client::class);
    } 
    
}
