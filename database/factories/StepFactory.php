<?php

namespace Database\Factories;

use App\Models\Recruiter;
use App\Models\Step;
use App\Models\StepCategory;
use App\Models\Timeline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Step>
 */
class StepFactory extends Factory
{
    protected $model = Step::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recruiter_id' => Recruiter::factory(),
            'timeline_id' => Timeline::factory(),
            'step_category_id' => StepCategory::factory(),
        ];
    }
}
