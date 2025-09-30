<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    use HasFactory;
// app/Models/Treatment.php
protected $fillable = [
    'patient_id',
    'appointment_id',
    'provider_id',
    'treatment_type',
    'cost',
    'status',
    'description',
    'treatment_date',
    'notes',        // SOAP notes
    'before_photo', // optional
    'after_photo',  // optional
];



    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
