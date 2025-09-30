<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'location_id', 'phone'];

    // Client belongs to ek User
    public function clientUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Client belongs to ek Location
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // Client has many Appointments
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // Client has many ConsentForms
    public function consentForms()
    {
        return $this->hasMany(ConsentForm::class);
    }
}
