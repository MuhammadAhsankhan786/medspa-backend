<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address'];

    // Ek location ke multiple staff
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    // Ek location ke multiple clients
    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    // Ek location ke multiple users
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
