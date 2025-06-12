<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollOption extends Model
{
    //
    protected $guarded = [];
    public function poll() {
    return $this->belongsTo(Poll::class);
}

public function votes() {
    return $this->hasMany(Vote::class);
}

}
