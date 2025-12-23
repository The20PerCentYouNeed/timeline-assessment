<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimelineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'recruiter_id' => $this->recruiter_id,
            'candidate_id' => $this->candidate_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'steps' => $this->steps->map(fn ($step) => new StepResource($step))->all(),
        ];
    }
}
