<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'location_id', 'position'];

    // Staff belongs to ek user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Staff belongs to ek location
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
