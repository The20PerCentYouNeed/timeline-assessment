<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timeline extends Model
{
    protected $guarded = [];

    public function recruiter()
    {
        return $this->belongsTo(Recruiter::class);
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function steps()
    {
        return $this->hasMany(Step::class);
    }
}
