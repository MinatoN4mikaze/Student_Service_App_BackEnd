<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    //
    protected $guarded = [];
    
    public function student() {
    return $this->belongsTo(Student::class);
}

public function option() {
    return $this->belongsTo(PollOption::class, 'poll_option_id');
}

}
