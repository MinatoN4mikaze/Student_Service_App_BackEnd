<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentObjection extends Model
{
        protected $guarded = [];

    public function objection()
    {
        return $this->belongsTo(Objection::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
