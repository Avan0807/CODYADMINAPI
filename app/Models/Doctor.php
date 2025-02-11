<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Doctor extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $table = 'Doctors';

    protected $primaryKey = 'doctorID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'specialization',
        'experience',
        'working_hours',
        'location',
        'phone',
        'email',
        'photo',
        'status',
        'rating',
        'bio',
        'services',
        'workplace',
        'education',
        'consultation_fee',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctorID', 'doctorID');
    }
}
