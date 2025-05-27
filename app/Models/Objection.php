<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Objection extends Model
{
     protected $fillable = ['subject_name'];

    public function studentSubmissions()
    {
        return $this->hasMany(StudentObjection::class);
    }
}
