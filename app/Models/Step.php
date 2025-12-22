<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    protected $guarded = [];

    public function timeline()
    {
        return $this->belongsTo(Timeline::class);
    }

    public function recruiter()
    {
        return $this->belongsTo(Recruiter::class);
    }

    public function stepCategory()
    {
        return $this->belongsTo(StepCategory::class);
    }

    public function statuses()
    {
        return $this->hasMany(StepStatus::class);
    }

    public function currentStatus()
    {
        return $this->statuses()->latest()->first();
    }
}
