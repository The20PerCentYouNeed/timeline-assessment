<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StepStatus extends Model
{
    protected $guarded = [];

    public function step()
    {
        return $this->belongsTo(Step::class);
    }

    public function recruiter()
    {
        return $this->belongsTo(Recruiter::class);
    }

    public function statusCategory()
    {
        return $this->belongsTo(StatusCategory::class);
    }
}
