<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StepStoreRequest;
use App\Http\Resources\StepResource;
use App\Models\Candidate;
use App\Models\Step;
use App\Models\Timeline;
use Illuminate\Validation\ValidationException;

class StepController extends Controller
{
    public function store(StepStoreRequest $request, Timeline $timeline)
    {
        $data = $request->validated();

        if ($timeline->candidate_id !== $data['candidate_id']) {
            throw ValidationException::withMessages([
                'timeline' => 'Timeline does not belong to the candidate',
            ]);
        }

        if ($timeline->recruiter_id !== $data['recruiter_id']) {
            throw ValidationException::withMessages([
                'timeline' => 'Timeline does not belong to the recruiter',
            ]);
        }

        $candidate = Candidate::findOrFail($data['candidate_id']);

        if ($candidate->recruiter_id !== $data['recruiter_id']) {
            throw ValidationException::withMessages([
                'candidate' => 'Candidate does not belong to the recruiter',
            ]);
        }

        if ($timeline->steps()->where('step_category_id', $data['step_category_id'])->exists()) {
            throw ValidationException::withMessages([
                'step_category' => 'A step category needs to exist only once per timeline',
            ]);
        }

        $step = Step::create([
            'recruiter_id' => $data['recruiter_id'],
            'timeline_id' => $timeline->id,
            'step_category_id' => $data['step_category_id'],
        ]);

        $step->statuses()->create([
            'recruiter_id' => $data['recruiter_id'],
            'status_category_id' => $data['status_category_id'],
        ]);

        return new StepResource($step);
    }
}
