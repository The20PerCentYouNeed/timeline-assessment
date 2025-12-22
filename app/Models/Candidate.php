<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $guarded = [];

    public function recruiter()
    {
        return $this->belongsTo(Recruiter::class);
    }
}
