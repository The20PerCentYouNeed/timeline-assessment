<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StepResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'recruiter_id' => $this->recruiter_id,
            'timeline_id' => $this->timeline_id,
            'step_category_id' => $this->step_category_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'current_status' => $this->currentStatus(),
        ];
    }
}
