<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [];//فينك تدخل اي شي

    public function student()
    {
         return $this->hasOne(Student::class);
    }
    public function admin()
    {
         return $this->hasOne(Admin::class);
    }
          public function announcment()
    {
        return $this->hasMany(Announcement::class);
    }

    public function studentObjection()
    {
        return $this->hasMany(StudentObjection::class);
    }

    public function complaints()
    {
    return $this->hasMany(Complaint::class);
    }
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
