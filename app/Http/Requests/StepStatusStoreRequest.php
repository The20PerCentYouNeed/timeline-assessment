<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StepStatusStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'candidate_id' => 'required|exists:candidates,id',
            'recruiter_id' => 'required|exists:recruiters,id',
            'timeline_id' => 'required|exists:timelines,id',
            'status_category_id' => 'required|exists:status_categories,id',
        ];
    }
}
