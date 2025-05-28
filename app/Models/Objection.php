<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Objection extends Model
{
      protected $guarded = [];

     //objections have many student suubmissions
    public function studentSubmissions()
    {
        return $this->hasMany(StudentObjection::class);
    }
}
