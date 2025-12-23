<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimelineStoreRequest;
use App\Http\Resources\TimelineResource;
use App\Models\Candidate;
use App\Models\Timeline;

class TimelineController extends Controller
{
    public function show(Timeline $timeline)
    {
        return new TimelineResource($timeline);
    }

    public function store(TimelineStoreRequest $request)
    {
        $data = $request->validated();

        $candidate = Candidate::create([
            'recruiter_id' => $data['recruiter_id'],
            'name' => $data['candidate_name'],
            'surname' => $data['candidate_surname'],
        ]);

        $timeline = Timeline::create([
            'recruiter_id' => $data['recruiter_id'],
            'candidate_id' => $candidate->id,
        ]);

        return new TimelineResource($timeline);
    }
}
