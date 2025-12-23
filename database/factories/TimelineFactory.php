<?php

namespace Database\Factories;

use App\Models\Candidate;
use App\Models\Recruiter;
use App\Models\Timeline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Timeline>
 */
class TimelineFactory extends Factory
{
    protected $model = Timeline::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recruiter_id' => Recruiter::factory(),
            'candidate_id' => Candidate::factory(),
        ];
    }
}
