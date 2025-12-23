<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StepStatusStoreRequest;
use App\Http\Resources\StepStatusResource;
use App\Models\Step;
use Illuminate\Validation\ValidationException;

class StepStatusController extends Controller
{
    public function store(StepStatusStoreRequest $request, Step $step)
    {
        $data = $request->validated();

        if ($step->timeline_id !== $data['timeline_id']) {
            throw ValidationException::withMessages([
                'timeline_id' => 'Step does not belong to this timeline',
            ]);
        }

        $timeline = $step->timeline;

        if ($timeline->candidate_id !== $data['candidate_id']) {
            throw ValidationException::withMessages([
                'candidate_id' => 'Timeline does not belong to this candidate',
            ]);
        }

        if ($step->recruiter_id !== $data['recruiter_id']) {
            throw ValidationException::withMessages([
                'recruiter_id' => 'Step does not belong to this recruiter',
            ]);
        }

        $status = $step->statuses()->create([
            'recruiter_id' => $step->recruiter_id,
            'status_category_id' => $data['status_category_id'],
        ]);

        return new StepStatusResource($status);
    }
}
