<?php

namespace Database\Factories;

use App\Models\Candidate;
use App\Models\Recruiter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Candidate>
 */
class CandidateFactory extends Factory
{
    protected $model = Candidate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recruiter_id' => Recruiter::factory(),
            'name' => fake()->firstName(),
            'surname' => fake()->lastName(),
        ];
    }
}
