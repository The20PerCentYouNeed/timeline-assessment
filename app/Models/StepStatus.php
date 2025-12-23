<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StepStatus extends Model
{
    use HasFactory;

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
