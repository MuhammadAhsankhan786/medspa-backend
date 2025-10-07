<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'staff_id',
        'location_id',
        'start_time',
        'end_time',
        'service_id',
        'provider_id',
        'package_id',
        'status',
        'notes',
    ];

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }

    public function consentForms()
    {
        return $this->hasMany(ConsentForm::class);
    }
}
