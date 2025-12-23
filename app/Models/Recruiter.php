<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recruiter extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }

    public function timelines()
    {
        return $this->hasMany(Timeline::class);
    }
}
