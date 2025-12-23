<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimelineStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'recruiter_id' => 'required|exists:recruiters,id',
            'candidate_name' => 'required|string|max:255',
            'candidate_surname' => 'required|string|max:255',
        ];
    }
}
