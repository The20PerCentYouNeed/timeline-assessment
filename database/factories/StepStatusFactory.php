<?php

namespace Database\Factories;

use App\Models\Recruiter;
use App\Models\StatusCategory;
use App\Models\Step;
use App\Models\StepStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StepStatus>
 */
class StepStatusFactory extends Factory
{
    protected $model = StepStatus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'step_id' => Step::factory(),
            'recruiter_id' => Recruiter::factory(),
            'status_category_id' => StatusCategory::factory(),
        ];
    }
}
