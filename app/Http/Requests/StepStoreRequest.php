<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StepStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'candidate_id' => 'required|exists:candidates,id',
            'recruiter_id' => 'required|exists:recruiters,id',
            'step_category_id' => 'required|exists:step_categories,id',
            'status_category_id' => 'required|exists:status_categories,id',
        ];
    }
}
