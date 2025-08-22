<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    protected $guarded = [];
    public function options()
    {
        return $this->hasMany(PollOption::class);
    }

    public function user()
    {
        return $this->belongsTo(Admin::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
